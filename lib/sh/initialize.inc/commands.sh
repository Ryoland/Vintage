##########
## 定数 ##
##########

DP_CACHE=$VTG_ROOT/cch;       ## ｷｬｯｼｭﾃﾞｨﾚｸﾄﾘのﾊﾟｽ
FN_CACHE=commands;            ## ｷｬｯｼｭﾌｧｲﾙ のﾈｰﾑ
FP_CACHE=$DP_CACHE/$FN_CACHE; ## ｷｬｯｼｭﾌｧｲﾙ のﾊﾟｽ

DPS_COMMANDS=(/bin /sbin /usr/bin /usr/local/bin /usr/sbin);

CP_GREP=${CP_GREP:=grep};
CP_LS=${CP_LS:=ls};
CP_MKDIR=${CP_MKDIR:=mkdir};
CP_RM=${CP_RM:=rm};
CP_SED=${CP_SED:=sed};
CP_TR=${CP_TR:=tr};

##########
## 変数 ##
##########

DP_COMMANDS=;
FNS_COMMAND=();

##########
## 処理 ##
##########

$CP_MKDIR -p $DP_CACHE;
[ $ARG_C ] && $CP_RM -rf $FP_CACHE;

if [ ! -f $FP_CACHE ]; then
    > $FP_CACHE;
    for DP_COMMANDS in ${DPS_COMMANDS[@]}; do
        [ ! -d $DP_COMMANDS ] && continue;
        for FN_COMMAND in $($CP_LS $DP_COMMANDS); do
            [ $(echo $FN_COMMAND | $CP_GREP -e [^0-9a-zA-Z_-]) ] && continue;
            FP_COMMAND=$DP_COMMANDS/$FN_COMMAND;
            FN_COMMAND=$(echo $FN_COMMAND | $CP_TR a-z A-Z);
            FN_COMMAND=$(echo $FN_COMMAND | $CP_TR -   _  );
            if [ ! ${FNS_COMMAND[$FN_COMMAND]} ]; then
                ${FNS_COMMAND[$FN_COMMAND]} = 1;
                echo "readonly CP_$FN_COMMAND=$FP_COMMAND" >> $FP_CACHE;
            fi;
        done;
    done;
fi;

source $FP_CACHE;

##########
## 掃除 ##
##########

unset DP_CACHE;
unset FN_CACHE;
unset FP_CACHE;

unset DP_COMMANDS;
unset DPS_COMMANDS;
unset FN_COMMAND;
unset FNS_COMMAND;
unset FP_COMMAND;
