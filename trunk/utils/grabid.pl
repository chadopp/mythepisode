#!/usr/bin/perl
############################################################################
#
# File     : grabid.pl
# Usage    : ./grabid.pl "show name" <path to showfile> <path to utils dir> <path to imageDir>
# Url      : $URL$
# Date     : $Date$
# Revision : $Revision$
# Author   : $Author$
# License  : GPL
#
############################################################################
use LWP::Simple;
use strict;

if ($#ARGV != 3 ) {
    print "usage: ./grabid.pl \"show in quotes\" <shows.txt path> <utilsdir path> <imageDir path>\n";
    print "Ex: ./grabid.pl \"24\" /tmp/24 /var/www/mythweb/modules/episode/utils /var/www/mythweb/data/episodes/images\n"; 
	exit;
}

## Unix commands
my $TOUCH = "/usr/bin/touch";
my $RM    = "/bin/rm";
my $MKDIR = "/bin/mkdir";
my $CAT   = "/bin/cat";
my $LS    = "/bin/ls";
my $WC    = "/usr/bin/wc";
my $PS    = "/bin/ps";
my $GREP  = "/bin/grep";

## variables
my $show        = $ARGV[0];
my $showfile    = $ARGV[1];
my $utilPath    = $ARGV[2];
my $imagePath   = $ARGV[3];
my $count       = 0;
my $countunk    = 50001;
my $maxproc     = "15";
my $dir         = "";
my $showId      = "";
my $showName    = "";
my $showUrl     = "";
my $showPrem    = "";
my $showStart   = "";
my $showEnd     = "";
my $showCtry    = "";
my $showStatus  = "";
my $showClass   = "";
my $showGenre   = "";
my $showNetwork = "";
my $showAirtime = "";
my $showLatest  = "";
my $showNext    = "";
my $episodeInfo = "";
my $episodeUrl  = "";
my $junk        = ""; 
my $line        = "";
my $epnum       = "";
my $title       = "";
my $airdate     = "";
my $file        = "";
my $link        = "";
my $debug       = 0;
my @array       = ();
my @sorted      = ();
my $sumtool     = "$utilPath/summary.pl";

## Get information from tvrage.com using their quickinfo script
## The quickinfo script has some issues that I have reported, but
## for now strip a few things.
$show =~ s/\&//g; 
$show =~ s/\#//g; 
$show =~ s/ with//g; 
$show =~ s/ With//g; 
my $site = get "http://services.tvrage.com/tools/quickinfo.php?show=$show";

if (!$site) {
    print "Show id for $show not found. Could be temporary issues accessing tvrage.com\n";
    exit 1;
}

foreach $line (split("\n",$site) ) {
    ## Parse the results from tvrage.com to get showid 
    my ($sec,$val) = split('\@',$line);
    if ($sec =~ "Show ID" ) {
        $showId = $val;
    } elsif ($sec eq "Show Name" ) {
        $showName = $val;
    } elsif ( $sec eq "Show URL" ) {
        $showUrl = $val;
    } elsif ( $sec eq "Premiered" ) {
        $showPrem = $val;
    } elsif ( $sec eq "Started" ) {
        $showStart = $val;
    } elsif ( $sec eq "Ended" ) {
        $showEnd = $val;
    } elsif ($sec eq "Country" ) {
        $showCtry = $val;
    } elsif ( $sec eq "Status" ) {
        $showStatus = $val;
    } elsif ( $sec eq "Classification" ) {
        $showClass = $val;
    } elsif ( $sec eq "Genres" ) {
        $showGenre = $val;
    } elsif ( $sec eq "Network" ) {
        $showNetwork = $val;
    } elsif ( $sec eq "Airtime" ) {
        $showAirtime = $val;
    } elsif ( $sec eq "Latest Episode" ) {
        my($ep,$title,$airdate) = split('\^',$val);
        $showLatest = $ep.", \"".$title."\" aired on ".$airdate;
    } elsif ( $sec eq "Next Episode" ) {
        my($ep,$title,$airdate) = split('\^',$val);
        $showNext = $ep.", \"".$title."\" airs on ".$airdate;
    } elsif ( $sec eq "Episode Info" ) {
        my($ep,$title,$airdate) = split('\^',$val);
        $episodeInfo = $ep.", \"".$title."\" aired on ".$airdate;
    } elsif ( $sec eq "Episode URL" ) {
        $episodeUrl = $val;
    }
}

if ($debug) {
    print "showId      is $showId\n";
    print "showName    is $showName\n";
    print "showUrl     is $showUrl\n";
    print "showPrem    is $showPrem\n";
    print "showStart   is $showStart\n";
    print "showEnd     is $showEnd\n";
    print "showCtry    is $showCtry\n";
    print "showStatus  is $showStatus\n";
    print "showClass   is $showClass\n";
    print "showGenre   is $showGenre\n";
    print "showNetwork is $showNetwork\n";
    print "showAirtime is $showAirtime\n";
    print "showLatest  is $showLatest\n";
    print "showNext    is $showNext\n";
    print "episodeInfo is $episodeInfo\n";
    print "episodeUrl  is $episodeUrl\n";
}

## Get jpg image from tvrage.com.  Search the images directory
## tree until we find the one we need
if (! -f "$imagePath/$showId.jpg") {
    my $ii = 0;
    until ((-f "$imagePath/$showId.jpg") || ($ii >= 35)) {
        getstore("http://images.tvrage.com/shows/${ii}/$showId.jpg",
                 "$imagePath/$showId.jpg");
        $ii++;
    }
}

$dir = "/tmp/$showId";
if (-d $dir) {
    print "Removing dir $dir\n";
    system("$RM -r $dir");
}
print "Creating dir $dir\n";
system("$MKDIR $dir");
        
        ## Get a list of episodes based on the showid
        my $episodes = get "http://www.tvrage.com/feeds/episode_list.php?sid=$showId";
        foreach my $episode (split("\n",$episodes) ) {
            if ( $episode =~ /^\<episode/ ) {
                if ( $episode =~ m#<(epnum)>(.*)</\1># ) {
                    $epnum = $2;
                    #print "EP1 is $epnum\n";
                    #print "Episode Number: $2\n";
                }else{
                    $epnum = "$countunk";
                    #print "EP2 is $epnum\n";
                    $countunk++;
                }
                if ( $episode =~ m#<(title)>(.*)</\1># ) {
                    $title = $2;
                    $title =~ s/\&\#39\;/\'/g;
                    $title =~ s/\&amp\;quot\;/\"/g;
                    $title =~ s/\&amp\;/\&/g;
                    #print "Title         : $2\n";
                } 
                if ( $episode =~ m#<(airdate)>(.*)</\1># ) {
                     $airdate = $2;
                     #print "Airdate       : $2\n";
                }
                if ( $episode =~ m#<(link)>(.*)</\1># ) {
                    $link = $2;
                    #print "Link          : $2\n";
                    ## Account for shows that have the same episode id.  
                    if (-f "$dir/$epnum") {
                        $epnum = "$epnum.$airdate";
                    } 
                    ## Get summary information for each episode by spawning off
                    ## summary.pl for each episode.  Can be cpu intensive. 
                    until (`$PS -ef | $GREP summary.pl | $WC -l` <= "$maxproc") {}
                    system("$sumtool $link $epnum \"$title\" $airdate $showId &");
                    system("$TOUCH $dir/$epnum");
                    $count++;
                }
            }
        }


#print "Count is $count\n";
if ($count == '0') {
    system("$RM $showfile"); 
    system("$TOUCH $showfile");
    system("/bin/echo \"INFO:$showId:$showStart:$showEnd:$showCtry:$showStatus:$showClass:$showGenre:$showNetwork\" > $showfile");
    exit;
}
 
my $wait = 0;
my $wait2 = 0;
## Before we continue we need to ensure all summary information
## has been collected.  Timeout after 60 seconds
until ((`$LS $dir/* | $WC -l` == $count) || ($wait > 60)) {
    sleep 1;
    $wait++;
}

## Sort the files and combine them into one
@array = `$LS $dir`;
@sorted = sort {$a <=> $b} @array;
system("$RM $showfile"); 
system("/bin/echo \"INFO:$showId:$showStart:$showEnd:$showCtry:$showStatus:$showClass:$showGenre:$showNetwork\" > $showfile");
foreach $file (@sorted) {
    chomp $file;
    until ((! -z "$dir/$file") || ($wait2 > 30)){
        sleep 1;
        $wait2++; 
    }
    system("$CAT $dir/$file >> $showfile");
}

system("$RM -r $dir");
