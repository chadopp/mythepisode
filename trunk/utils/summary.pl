#!/usr/bin/perl
############################################################################
#
# File     : summary.pl
# Usage    : called by grabid.pl
# Revision : 1.0
# Author   : C. Oppliger
# License  : GPL
#
############################################################################
use LWP::Simple;
use strict;

my $link    = $ARGV[0];
my $epnum   = $ARGV[1];
my $title   = $ARGV[2];
my $airdate = $ARGV[3];
my $id      = $ARGV[4];

my ($epnum1,$junk) = split(/\./, $epnum);
my $show     = "$epnum1\t$airdate\t$title\t$link";
my $base     = "/tmp/$id";
my $filename = "$base/$epnum";
my $sum      = "";

my $getlink = get "$link";

open FILE, ">$filename" or die $!;

foreach my $data (split("\n",$getlink) ) {
    if ( $data =~ /^\<\/script\>\<br\>/ ) {
        $data =~ s/\<\/script\>\<br\>//;
        ($data,$junk) = split("\<", $data);
        $sum = $data;
        if (($sum =~ "\&nbsp") || ($sum eq "")) {
             $sum = "No summary data available";
        }
        last;
        #print "Summary       : $data\n";
        #print FILE $data;

    }
}

print FILE "$show\t$sum\n";
close FILE;
