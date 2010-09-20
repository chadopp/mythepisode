#!/usr/bin/perl
############################################################################
#
# File     : grabid.pl
# Usage    : ./grabid.pl "show name" <path to showfile> <path to imageDir>
# Url      : $URL$
# Date     : $Date$
# Revision : $Revision$
# Author   : $Author$
# License  : GPL
#
## The API key used in this script was explicitly requested and assigned by 
## tvrage.com for use with mythepisode.  If you create an application that is 
## not associated with mythepisode that requires access to the tvrage API 
## key you need to request a key through tvrage.com
############################################################################
use LWP::Simple;
use LWP::Simple qw(get $ua);
use strict;

$ua->agent('My agent/1.0');
$ua->timeout(60); # time out after 60 seconds

if ($#ARGV != 2 ) {
    print "usage: ./grabid.pl \"show in quotes\" <shows.txt path> <imageDir path>\n";
    print "Ex: ./grabid.pl \"24\" /tmp/24 /var/www/mythweb/data/episodes/images\n"; 
    exit;
}

## variables
my $debug       = 0;
my $show        = $ARGV[0];
my $showfile    = $ARGV[1];
my $imagePath   = $ARGV[2];
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
my $seasonnum   = "";
my $line        = "";
my $epnum       = "";
my $title       = "";
my $airdate     = "";
my $link        = "";
my $junk        = "";
my $summary     = "";

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

## Get jpg image from tvrage.com.
if (! -f "$imagePath/$showId.jpg") {
    ## This API key was explicitly requested and assigned by tvrage.com for use with
    ## mythepisode.  If you create an application that is not associated with mythepisode
    ## that requires access to the tvrage API key you need to request a key through tvrage.com
    my $images = get "http://services.tvrage.com/myfeeds/showinfo.php?key=b8rxoRXCByj0g0V3fWgu&sid=$showId";
    foreach $line (split("\n",$images) ) {
        ## Parse the results from tvrage.com to get showid
        if ( $line =~ m#<(image)>(.*)</\1># ) {
            my $showImage = $2; chomp $showImage;
            print "Image is $showImage\n" if $debug;
            getstore("$showImage", "$imagePath/$showId.jpg");
        }
    }
} else {
    print "Thumbnail exists for $showName\n" if $debug;
}


open FILE, ">$showfile" or die $!;
binmode FILE, ":utf8";

print FILE "INFO:$showId:$showStart:$showEnd:$showCtry:$showStatus:$showClass:$showGenre:$showNetwork\n";

## Get a list of episodes based on the showid
my $episodes = get "http://services.tvrage.com/myfeeds/episode_list.php?key=b8rxoRXCByj0g0V3fWgu&sid=$showId";
foreach my $episode (split("\n",$episodes)) {
    if ($episode =~ /^\<Season no/) {
        ($junk,$seasonnum) = split("\"", $episode); 
        print "Season is $seasonnum\n" if $debug;
    }
    if ($episode =~ /^\<Special/) {
        $seasonnum = "Season";
        print "Season is $seasonnum\n" if $debug;
    }
    if ($episode =~ /^\<episode/) {
        if ($episode =~ m#<(seasonnum)>(.*)</\1>#) {
            $epnum = "$seasonnum-$2";
            print "Episode Number: $2\n"if $debug;
        }
        if ($episode =~ m#<(season)>(.*)</\1>#) {
            $epnum = "$seasonnum-$2";
            print "Episode Number: $2\n" if $debug;
        }
        if ($episode =~ m#<(title)>(.*)</\1>#) {
            $title = $2;
            $title =~ s/\&\#39\;/\'/g;
            $title =~ s/\&amp\;quot\;/\"/g;
            $title =~ s/\&amp\;/\&/g;
            print "Title         : $2\n" if $debug;
        }
        if ($episode =~ m#<(airdate)>(.*)</\1>#) {
            $airdate = $2;
            print "Airdate       : $2\n" if $debug;
        }
        if ($episode =~ m#<(link)>(.*)</\1>#) {
            $link = $2;
            print "Link          : $2\n" if $debug;
        }
        if ($episode =~ m#<(summary)>(.*)</\1>#) {
            $summary = $2;
        }
        if ($summary eq "") {
            $summary = "No summary data available";
        }
        print "Summary       : $summary\n" if $debug;
        ##print "$epnum\t$airdate\t$title\t$link\t$summary\n";
        print FILE "$epnum\t$airdate\t$title\t$link\t$summary\n";
        $summary = "";
    }
}

close(FILE);
