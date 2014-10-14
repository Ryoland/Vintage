#!/bin/bash

##########
## Desc ##
##########

##
 # Configure
 ##

##########
## Init ##
##########

ARGUMENTS=;

HELP() {
    echo "Usage: $0";
}

source $VTG_ROOT/pro/Vintage/lib/sh/initialize;

##########
## Conf ##
##########

##########
## Func ##
##########

proc() {
    $0.i.pl;
}

##########
## Proc ##
##########

proc;

exit 0;
