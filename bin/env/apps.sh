#!/bin/bash

##########
## Desc ##
##########

##
 # Apps
 ##

##########
## Init ##
##########

ARGUMENTS=n;

HELP() {
    echo "Usage: $0 [Argument(s)]";
    echo "";
    echo "Optional Argument(s):";
    echo "    -n Include a newline character.";
}

VTG_NO_CONSTANTS=1;
source $VTG_ROOT/pro/Vintage/lib/sh/initialize;

##########
## Conf ##
##########

readonly FP=$VTG_ROOT/etc/apps;

##########
## Func ##
##########

proc() {
    if [ -f $FP ]; then
        if [ $ARG_n ]; then
            env cat $FP;
        else
            env echo -n $(env cat $FP);
        fi;
    fi;
}

##########
## Proc ##
##########

proc;

exit 0;
