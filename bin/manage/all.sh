#!/bin/bash

##########
## Desc ##
##########

##
 # All
 ##

##########
## Init ##
##########

ARGUMENTS=p:;

HELP() {
    echo "Usage: $0 [Argument(s)]";
    echo "";
    echo "Optional Argument(s):";
    echo "    -p Project name.";
}

source $VTG_ROOT/pro/Vintage/lib/sh/initialize;

##########
## Conf ##
##########

##########
## Func ##
##########

proc() {
    $VTG_ROOT/pro/Vintage/bin/manage/configure -p $ARG_p;
    $VTG_ROOT/pro/Vintage/bin/manage/package   -p $ARG_p;
    $VTG_ROOT/pro/Vintage/bin/manage/arrange   -p $ARG_p;
}

##########
## Proc ##
##########

proc;

##########
## Exit ##
##########

exit 0;
