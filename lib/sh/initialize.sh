##########
## 変数 ##
##########

DP_INC=$VTG_ROOT/pro/Vintage/lib/sh/initialize.inc;
FNS_INC=(arguments commands constants functions);

##########
## 処理 ##
##########

if [ ! -d $DP_INC ]; then
    echo "(initialize) [警告] ﾃﾞｨﾚｸﾄﾘ $DP_INC が存在しません｡";
    exit 1;
else
    for FN_INC in ${FNS_INC[@]}; do
        if [ ! -f $DP_INC/$FN_INC ]; then
            echo "(initialize) [警告] ﾌｧｲﾙ $DP_INC/$FN_INC が存在しません｡";
            exit 1;
        else
            source $DP_INC/$FN_INC;
        fi;
    done;
fi;

unset DP_INC FN_INC FNS_INC;
