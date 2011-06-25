#!/usr/bin/perl
############################################################################
##
## File     : update_programid.pl
## Usage    : ./update_programid.pl
## Url      : $URL$
## Date     : $Date$
## Revision : $Revision$
## Author   : Author: agiggins $
## License  : GPL
##
## This utility is used to insert programids to episodes that a user manually
## inserted into the oldrecorded database that didn't include programid
##
## Requires DBD::mysql & DBD::AnyData
##
## Usage: update_programid.pl <dbhost> <database> <dbuser> <dbpass> <path to mythepisode data>
## With no supplied arguement it will run with the default values below, which are set on line 29
##
## dbhost = "localhost";
## database = "mythconverg";
## user = "mythtv";
## pass = "mythtv";
## path = "/storage/recordings/episode/shows";
##
##
#############################################################################
#
use DBI;

# Please set your database and mythepisode data directories below also please perform a Full Show Listing Update in mythepisode for best results.
my $dbhost = "localhost";
my $database = "mythconverg";
my $user = "mythtv";
my $pass = "mythtv";
my $path = "/storage/recordings/episode/shows";

$num_args = $#ARGV + 1;
if ($num_args == 0) {
  print "\nNo command arguements supplied using default values supplied in script\n";
  print "$dbhost $database $user $pass $path";
}
elsif ($num_args == 5) {
  print "\nRunning with the supplied command values\n";
  $dbhost = $ARGV[0];
  $database = $ARGV[1];
  $user = $ARGV[2];
  $pass = $ARGV[3];
  $path = $ARGV[4];
}
else {
  print "\nUsage: update_programid.pl <dbhost> <database> <dbuser> <dbpass> <path to mythepisode data>\n";
  exit;
}


print "\ndbhost = $dbhost";
print "\ndatabase = $database";
print "\nuser = $user";
print "\npass = $pass";
print "\npath = $path";
print "\nIs the above information correct? (Y/N): ";

chomp ( $answer = <STDIN> );        # Get the input, assign it to the variable
if    ( $answer eq "Y" ) {
  print "Yes\n";
}
elsif ( $answer eq "N" ) {
  print "No\n";
  exit;
}
else {
  print "No idea!\n";
  exit;
}


#Prepare DB
my $dbh = DBI->connect("dbi:mysql:database=$database:host=$dbhost","$user","$pass")
	or die "(EE) Cannot connect to database ($!)\n" . exit(-1);
$dbh->{PrintError} = 0;
#$dbh->{PrintWarn} = 1;
#$dbh->{RaiseError} = 0;
my $sth;

#select show titles with missing programid's
my $missingshow = "SELECT DISTINCT(title) FROM oldrecorded WHERE programid = '' order by title";
my $sth = $dbh->prepare($missingshow);
my $showtitle = $sth->execute();

while ( @showtitle = $sth->fetchrow_array ) {
my @showtitle2 = @showtitle;
  foreach $line (@showtitle){
    $line =~ s/[^a-zA-Z0-9]*//g;
    $line = lc $line;
    $line = ucfirst $line;
    if (-e "$path/$line") {
        my $missing_select = "SELECT subtitle FROM oldrecorded WHERE title = (?) AND programid = '' AND NOT subtitle = '' order by subtitle";
        my $sths = $dbh->prepare($missing_select);
        my $subtitle = $sths->execute(@showtitle2);
        while ( @subtitle = $sths->fetchrow_array ) {

          my $dbj = DBI->connect('dbi:AnyData(RaiseError=>1):');
          $dbj->func( 'shows', 'Tab', "$path/$line", { col_names => 'episode,airdate,subtitle,link,description' }, 'ad_catalog');
	  my $shows_select = "SELECT episode FROM shows WHERE subtitle = (?)";
          my $shows_sth = $dbj->prepare($shows_select);
          my $shows = $shows_sth->execute(@subtitle);
          while (@shows = $shows_sth->fetchrow_array) {
	    print "@shows - @showtitle2 - @subtitle\n";
	    my $updateprogid = "update oldrecorded SET programid = ? WHERE title = ? AND subtitle = ? AND programid = ''";
	    $update_sth = $dbh->prepare($updateprogid);
	    $update_sth->execute(@shows, @showtitle2, @subtitle);
	  }
        }
     }
  }
}
