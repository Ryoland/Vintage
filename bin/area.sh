#!/bin/bash

##########
## 初期 ##
##########

ARGUMENTS=n;

HELP() {
    echo "概要";
    echo "    ｴﾘｱ名を表示します。";
    echo "";
    echo "使い方";
    echo "    $0 [ｵﾌﾟｼｮﾝ]";
    echo "";
    echo "任意ｵﾌﾟｼｮﾝ";
    echo "    -n 改行する。";
}

source ~/.vtgrc;

##########
## 変数 ##
##########

readonly FP_AREA=$VTG_ROOT_DEFAULT/etc/area;

##########
## 関数 ##
##########

process() {
    check;
    mission;
}

check() {
    if [ ! -f $FP_AREA ]; then
        echo "($PROGRAM) [警告] ﾌｧｲﾙ $FP_AREA がありません。";
        exit 1;
    elif [ ! -s $FP_AREA ]; then
        echo "($PROGRAM) [警告] ﾌｧｲﾙ $FP_AREA が空です。";
        exit 1;
    fi;
}

mission() {
    if [ $ARG_n ]; then
        $CP_CAT $FP_AREA;
    else
        echo -n $($CP_CAT $FP_AREA);
    fi;
}

##########
## 処理 ##
##########

process;

exit 0;
