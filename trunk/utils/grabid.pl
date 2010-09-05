#!/usr/bin/perl
############################################################################
#
# File     : grabid.pl
# Usage    : ./grabid.pl "show name" <path to showfile>
# Date     : $Date$
# Revision : $Revision$
# Author   : $Author$
# License  : GPL
#
############################################################################
use LWP::Simple;
use strict;

if ($#ARGV != 1 ) {
    print "usage: ./grabid.pl \"show name in quotes\" <path to showfile>\n";
    print "Ex: ./grabid.pl \"24\" /tmp/24\n"; 
	exit;
}

my $TOUCH = "/usr/bin/touch";
my $RM    = "/bin/rm";
my $MKDIR = "/bin/mkdir";
my $CAT   = "/bin/cat";
my $LS    = "/bin/ls";
my $WC    = "/usr/bin/wc";
my $PS    = "/bin/ps";
my $GREP  = "/bin/grep";

my $sumtool  = "/var/www/mythweb/modules/episode/utils/summary.pl";
my $show     = $ARGV[0];
my $showfile = $ARGV[1];
my $count    = 0;
my $maxproc  = "15";
my $dir      = "";
my $id       = "";
my $junk     = ""; 
my $line     = "";
my $epnum    = "";
my $title    = "";
my $airdate  = "";
my $file     = "";
my $link     = "";
my @array    = ();
my @sorted   = ();

## Get information from tvrage.com using their quickinfo script
$show =~ s/\&//g; 
$show =~ s/\#//g; 
my $site = get "http://services.tvrage.com/tools/quickinfo.php?show=$show";

foreach $line (split("\n",$site) ) {
    ## Parse the results from tvrage.com to get showid 
    if ($line =~ /^\<pre/) {
        ($junk,$id) = split("\@", $line);
        chomp $id;    
        print "$id\n";

        $dir = "/tmp/$id";
        if (-d $dir) {
            print "Removing dir $dir\n";
            system("$RM -r $dir");
        }
        print "Creating dir $dir\n";
        system("$MKDIR $dir");
        
        ## Get a list of episodes based on the showid
        my $episodes = get "http://www.tvrage.com/feeds/episode_list.php?sid=$id";
        foreach my $episode (split("\n",$episodes) ) {
            if ( $episode =~ /^\<episode/ ) {
                if ( $episode =~ m#<(epnum)>(.*)</\1># ) {
                    $epnum = $2;
                    #print "EP1 is $epnum\n";
                    #print "Episode Number: $2\n";
                }else{
                    $epnum = $count + 1;
                    #print "EP2 is $epnum\n";
                }
                if ( $episode =~ m#<(title)>(.*)</\1># ) {
                    $title = $2;
                    $title =~ s/\&\#39\;/\'/g;
                    $title =~ s/\&amp\;quot\;/\"/g;
                    $title =~ s/\&amp\;/\&/g;
                    #print "Title         : $2\n";
                } 
                if ( $episode =~ m#<(airdate)>(.*)</\1># ) {
                     $airdate = $2
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
                    system("$sumtool $link $epnum \"$title\" $airdate $id &");
                    system("$TOUCH $dir/$epnum");
                    $count++;
                }
            }
        }
    } 
}

#print "Count is $count\n";
if ($count == '0') {
    system("$RM $showfile"); 
    system("$TOUCH $showfile");
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
foreach $file (@sorted) {
    chomp $file;
    until ((! -z "$dir/$file") || ($wait2 > 30)){
        sleep 1;
        $wait2++; 
    }
    system("$CAT $dir/$file >> $showfile");
}

system("$RM -r $dir");
