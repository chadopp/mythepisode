#!/usr/bin/perl
############################################################################
#
# File     : grabid.pl
# Usage    : ./grabid.pl "show name" <path to showfile> <path to imageDir> <data site>
# Url      : $URL$
# Date     : $Date$
# Revision : $Revision$
# Author   : $Author$
# License  : GPL
#
## The API keys used in this script were explicitly requested and assigned by 
## tvrage.com and TheTVDB.com for use with mythepisode.  If you create an 
## application that is not associated with mythepisode that requires access 
## to the tvrage or thetvdb API key you need to request a key through 
## tvrage.com and/or TheTVDB.com
## Since these two information providers rely on input from the community
## they appreciate contributions from the users of their information.  You
## can contribute by requesting an account on their websites and updating
## show/episode information.
##
## Data sites are in the following form:
## TVRage.com
## TheTVDB.com
############################################################################

use LWP::Simple;
use LWP::Simple qw(get $ua);
use XML::Simple;
use Encode;
use strict;

$ua->agent('My agent/1.0');
$ua->timeout(60); # time out after 60 seconds

if ($#ARGV != 3 ) {
    print "usage: ./grabid.pl \"show in quotes\" <shows path> <imageDir path> <data site>\n";
    print "Ex: ./grabid.pl \"24\" /tmp/24 /var/www/mythweb/data/episodes/images TVRage.com\n"; 
    exit;
}

## variables
my $debug      = 0;
my $show       = $ARGV[0];
my $showfile   = $ARGV[1];
my $imagePath  = $ARGV[2];
my $siteSelect = $ARGV[3];
my @epArray    = ();
my @specArray  = ();
my @normArray  = ();

my $showId      = "Unknown";
my $showStart   = "Unknown";
my $showCtry    = "Unknown";
my $showStatus  = "Unknown";
my $showClass   = "Unknown";
my $showGenre   = "Unknown";
my $showNetwork = "Unknown";

my ($siteInfo,$season,$episodeInfo,$episodeUrl,$seasonnum,$line,$epnum,
    $title,$airdate,$link,$junk,$summary);

my ($showName,$showUrl,$showPrem,$showAirtime,$showLatest,$showNext,
    $showSummary,$showPoster,$showArt,$showImage,$showEnd); 

my ($tvdbInfo,$tvdbEpnum,$tvdbSeason,$tvdbepnum,$tvdbaired,$tvdbEpisodes,
    $tvdbLink,$tvdbSubtitle,$tvdbShowID,$tvdbShow,$tvdbLoaded);

## Get information from tvrage.com using their quickinfo script
## The quickinfo script has some issues that I have reported, but
## for now strip a few things.
$show =~ s/\&//g; 
$show =~ s/\#//g; 
$show =~ s/ with//g; 
$show =~ s/ With//g; 

## Get data from TheTVDB.com if needed
sub TVDB_Info {
    ## Get show info from TheTVDB.com
    my $tvdbsite = get "http://www.thetvdb.com/api/GetSeries.php?seriesname=$show";
print "$tvdbsite\n";
    if (!$tvdbsite) {
        print "Show id for $show not found. Could be temporary issues accessing thetvdb.com\n";
        exit 1;
    }
    my $tvdbxml  = new XML::Simple;
    my $tvdbID   = $tvdbxml->XMLin($tvdbsite, SuppressEmpty => '', ForceArray => 1, KeyAttr => {},);

    if ($tvdbID) {
        foreach my $tvdbTemp (@{$tvdbID->{Series}}) {
            $tvdbShow = $tvdbTemp->{SeriesName}->[0];
            $tvdbShow =~ s/\.//g; 
            $tvdbShow =~ s/\://g; 
            $tvdbShow =~ s/ //g; 
            $show =~ s/\.//g;
            $show =~ s/\://g;
            $show =~ s/ //g;
print "tvdbshow is ...$tvdbShow... - show is ...$show...\n";
            if (lc($tvdbShow) eq lc($show)) {
                $tvdbShowID = $tvdbTemp->{seriesid}->[0];
                print "Show Id is $tvdbShowID\n" if $debug;
                last;
            }
        }
    }

    if ($tvdbShowID) {
        $tvdbEpisodes = get "http://thetvdb.com/api/8209AD0FC5FE8945/series/$tvdbShowID/all/en.xml";
    }

    if ($tvdbEpisodes) {
        $tvdbInfo = XMLin($tvdbEpisodes, SuppressEmpty => '', ForceArray => 1, KeyAttr => {},);
        $tvdbLoaded = 1;
        return $tvdbInfo;
    }
    $tvdbLoaded = 1;
}

## If the user chooses then we check TVRage.com
if ($siteSelect =~ /^TVRage/) {
    $siteInfo = "INFOTVRAGE";

    ## Get show info from TVRage.com
    my $tvragesite = get "http://services.tvrage.com/tools/quickinfo.php?show=$show";

    if (!$tvragesite) {
        print "Show id for $show not found. Could be temporary issues accessing tvrage.com\n";
        exit 1;
    }

    ## Parse the results from tvrage.com to get showid
    foreach $line (split("\n",$tvragesite) ) {
        my ($sec,$val) = split('\@',$line);
        if ($sec =~ "Show ID" ) {
            $showId = $val;
        } elsif ($sec eq "Show Name" ) {
            $showName = $val;
        } elsif ( $sec eq "Show URL" ) {
            $showUrl = $val;
            ($junk, $showUrl) = split(":", $showUrl);
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

    ## Get show info from TVRage.com
    my $images = get "http://services.tvrage.com/myfeeds/showinfo.php?key=b8rxoRXCByj0g0V3fWgu&sid=$showId";
    my $xml = new XML::Simple;

    ## Get summary info from TVRage.com
    my $showData = $xml->XMLin("$images");
    if ($showData) {
        $showSummary = $showData->{summary};
        $showSummary =~ s/\n/ /g;
        $showSummary =~ s/:/ /g;
    }else{
        $showSummary = "No summary available";
    }
    print "Summary is $showSummary\n" if $debug;

    ## Get jpg image from tvrage.com.
    if (! -f "$imagePath/$showId.jpg") {
        my $showImage = $showData->{image};
        getstore("$showImage", "$imagePath/$showId.jpg");
    }

    ## Get a list of episodes based on the showid
    my $episodes = get "http://services.tvrage.com/myfeeds/episode_list.php?key=b8rxoRXCByj0g0V3fWgu&sid=$showId";
    my $tvrageInfo = XMLin($episodes, SuppressEmpty => '', ForceArray => 1, KeyAttr => {},);
    foreach my $tvrageEpisodes (@{$tvrageInfo->{Episodelist}->[0]->{Season}}) {
        $season = $tvrageEpisodes->{no};
        foreach my $ep (@{$tvrageEpisodes->{episode}}) {
            $epnum = "$season-$ep->{seasonnum}->[0]";
            print "Episode : $epnum\n" if $debug;
            $title = $ep->{title}->[0];
            print "Subtitle: $title\n" if $debug;
            $airdate = $ep->{airdate}->[0];
            print "Airdate : $airdate\n" if $debug;
            $link = $ep->{link}->[0];
            print "Link    : $link\n" if $debug;
            if (defined $ep->{summary}->[0]) {
                $summary = "$ep->{summary}->[0]";
                $summary =~ s/\n/ /g;
                chomp $summary;
                $summary = "$summary - TVRage.com";
            } else {
                # If summary not found at tvrage look at thetvdb
                if (!$tvdbLoaded) {
                    &TVDB_Info;
                }
                foreach my $tvdbEpisode (@{$tvdbInfo->{Episode}}) {
                    $tvdbSeason = $tvdbEpisode->{SeasonNumber}->[0];
                    $tvdbEpnum  = $tvdbEpisode->{EpisodeNumber}->[0];
                    $tvdbEpnum  = sprintf("%2d", $tvdbEpnum);
                    $tvdbEpnum  =~ tr/ /0/;
                    $tvdbepnum  = "$tvdbSeason-$tvdbEpnum";
                    $tvdbaired  = $tvdbEpisode->{FirstAired}->[0];
                    if (($tvdbepnum eq $epnum) &&
                        ($tvdbEpisode->{Overview}->[0] !~ /^HASH/) && ($airdate eq $tvdbaired)) {
                        $summary = "$tvdbEpisode->{Overview}->[0]";
                        $summary =~ s/\n/ /g;
                        chomp $summary;
                        $summary = "$summary - TheTVDB.com";
                        last;
                    }
                }
            }
            if (($summary =~ /^ /) || ($summary eq "")) {
                $summary = "No summary data available";
            }
            print "Summary : $summary\n\n" if $debug;
            #print FILE "$epnum\t$airdate\t$title\t$link\t$summary\n";
            push @epArray, "$epnum\t$airdate\t$title\t$link\t$summary\n";
            $summary = "";
        }
    }

    ## Get the special episodes if they exist
    foreach my $tvrageEpisodes (@{$tvrageInfo->{Episodelist}->[0]->{Special}->[0]->{episode}}) {
        $epnum = "Season-$tvrageEpisodes->{season}->[0]";
        print "Episode : $epnum\n" if $debug;
        $title = $tvrageEpisodes->{title}->[0];
        print "Subtitle: $title\n" if $debug;
        $airdate = $tvrageEpisodes->{airdate}->[0];
        print "Airdate : $airdate\n" if $debug;
        $link = $tvrageEpisodes->{link}->[0];
        print "Link    : $link\n" if $debug;
        if (defined $tvrageEpisodes->{summary}->[0]) {
            $summary = $tvrageEpisodes->{summary}->[0];
            $summary =~ s/\n/ /g;
            chomp $summary;
            $summary = "$summary - TVRage.com";
        } else {
            $summary = "No summary data available";
        }
        if (($summary =~ /^ /) || ($summary eq "")) {
            $summary = "No summary data available";
        }
        print "Summary : $summary\n\n" if $debug;
        #print FILE "$epnum\t$airdate\t$title\t$link\t$summary\n";
        push @epArray, "$epnum\t$airdate\t$title\t$link\t$summary\n";
        $summary = "";
    }
}

## If the user chooses then we check TheTVDB.com
if ($siteSelect =~ /^TheTVDB/) {
    &TVDB_Info;
    $siteInfo = "INFOTVDB";
    foreach my $tvdbData (@{$tvdbInfo->{Series}}) {
        $showId      = $tvdbData->{id}->[0];
        $showStart   = $tvdbData->{FirstAired}->[0];
        $showStatus  = $tvdbData->{Status}->[0];
        $showGenre   = $tvdbData->{Genre}->[0];
        $showGenre   =~ s/^\|//g; 
        $showGenre   =~ s/\|$//g; 
        $showNetwork = $tvdbData->{Network}->[0];
        $showSummary = $tvdbData->{Overview}->[0];
        $showPoster  = $tvdbData->{poster}->[0]; 
        $showArt     = $tvdbData->{fanart}->[0]; 
        $showSummary =~ s/\n/ /g;
        $showSummary =~ s/:/ /g;
    }
    if ($showArt) {
        $showImage   = "http://thetvdb.com/banners/$showArt";
    } elsif ($showPoster) {
        $showImage   = "http://thetvdb.com/banners/$showPoster";
    }

    if (($showImage) && (! -f "$imagePath/$showId.jpg")) {
        getstore("$showImage", "$imagePath/$showId.jpg");
    }
    foreach my $tvdbEpisode (@{$tvdbInfo->{Episode}}) {
        $tvdbSeason = $tvdbEpisode->{SeasonNumber}->[0];
        if ($tvdbSeason == '0') {
            $tvdbSeason = "Special";
        }
        $tvdbEpnum = $tvdbEpisode->{EpisodeNumber}->[0];
        $tvdbEpnum = sprintf("%2d", $tvdbEpnum);
        $tvdbEpnum =~ tr/ /0/;
        $tvdbepnum = "$tvdbSeason-$tvdbEpnum";
        $tvdbSubtitle = $tvdbEpisode->{EpisodeName}->[0];
        $tvdbaired = $tvdbEpisode->{FirstAired}->[0];
        if (($showStatus eq "Ended") && ($tvdbSeason ne "Special") && ($tvdbaired)) {
            $showEnd = $tvdbaired;
        }
        $tvdbLink = "http://thetvdb.com";
        $summary = "$tvdbEpisode->{Overview}->[0] - TheTVDB.com";
        chomp $summary;
        $summary =~ s/\n/ /g;
        if (($summary =~ /^ /) || ($summary eq "")) {
            $summary = "No summary data available";
        }
        #print "$tvdbepnum\t$tvdbaired\t$tvdbSubtitle\t$link\t$summary\n";
        #print FILE "$tvdbepnum\t$tvdbaired\t$tvdbSubtitle\t$tvdbLink\t$summary\n";
        if ($tvdbSeason == "Special") {
            push @specArray, "$tvdbepnum\t$tvdbaired\t$tvdbSubtitle\t$tvdbLink\t$summary\n"; 
        } else {
            push @normArray, "$tvdbepnum\t$tvdbaired\t$tvdbSubtitle\t$tvdbLink\t$summary\n";
        }
        @epArray = (@normArray,@specArray);
    }
    $showUrl = "//www.thetvdb.com";
}

## Write the results to a file
open FILE, ">$showfile" or die $!;
binmode FILE, ":utf8";

print FILE "$siteInfo:$showId:$showStart:$showEnd:$showCtry:$showStatus:$showClass:$showGenre:$showNetwork:$showUrl:$showSummary\n";

foreach my $line (@epArray) {
    print FILE $line;
}
close(FILE);
