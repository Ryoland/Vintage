#!/bin/bash

##########
## Desc ##
##########

##
 # Cron
 ##

##########
## Init ##
##########

ARGUMENTS=p:t:!;

HELP() {
    echo "Usage: $0 [Argument(s)]";
    echo "";
    echo "Required Argument(s):";
    echo "    -t Tag name.";
    echo "";
    echo "Optional Argument(s):";
    echo "    -p Project name.";
}

source $VTG_ROOT/pro/Vintage/lib/sh/initialize;

##########
## Conf ##
##########

PROJECT=${ARG_p:-$VTG_MANAGER};
DP_ROOT=$VTG_ROOT/pro/$PROJECT/Vintage/bin/manage/cron;

##########
## Func ##
##########

execute() {

    DP=$1;

    if [ -d $DP ]; then

        FPS=$(env find $DP -maxdepth 1 -regex "^.+\/\w+\.$ARG_t$");

        for FP in ${FPS[@]}; do

            if [ $ARG_v ]; then env echo "Processing $FP"; fi;

            OWNER=$(env echo $FP | env sed -e s/^.*\\\///g | env awk -F'.' '{print $1}');

            if [ $(env id -n -u $OWNER) = $OWNER ]; then
                env sudo  -i -u $OWNER $FP;
            fi;

        done;
    fi;
}

proc() {

    DPS=();
    DPS=(${DPS[@]} $DP_ROOT/global);
    DPS=(${DPS[@]} $DP_ROOT/area/$VTG_AREA);
    DPS=(${DPS[@]} $DP_ROOT/host/$VTG_AREA/$VTG_HOST);

    for ROLE in ${VTG_ROLES[@]}; do
        DPS=(${DPS[@]} $DP_ROOT/roles/$ROLE);
    done;

    for APP in ${VTG_APPS[@]}; do
        DPS=(${DPS[@]} $DP_ROOT/apps/$APP);
    done;

    for TAG in ${VTG_TAGS[@]}; do
        DPS=(${DPS[@]} $DP_ROOT/tags/$TAG);
    done;

    for DP in ${DPS[@]}; do
        execute $DP;
    done;
}

##########
## Proc ##
##########

proc;

##########
## Exit ##
##########

exit 0;
