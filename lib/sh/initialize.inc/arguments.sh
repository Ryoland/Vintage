##########
## 設定 ##
##########

ARGUMENTS_RSVD=(C D H v V);

##########
## 関数 ##
##########

if [ ! "$(declare | grep -e '^HELP ()')" ]; then

    HELP() {
        echo "(arguments) [情報] >";
        echo "ｵﾌﾟｼｮﾝ[H] (ﾍﾙﾌﾟ表示)が指定されましたが、";
        echo "このﾌﾟﾛｸﾞﾗﾑ ではﾍﾙﾌﾟ関数が未定義のため、";
        echo "代わりにこのﾒｯｾｰｼﾞを表示して終了します。";
        exit $1;
    }
fi;

##########
## 処理 ##
##########

ARGUMENTS=${ARGUMENTS:-''};

for ARGUMENT in ${ARGUMENTS_RSVD[@]}; do
    if [ ${ARGUMENTS#*$ARGUMENT*} = $ARGUMENTS ]; then
        ARGUMENTS=$ARGUMENTS$ARGUMENT;
    else
        echo "(arguments) [警告] ｵﾌﾟｼｮﾝ[$ARGUMENT]は予約されています。";
        exit 1;
    fi;
done;

ARGUMENTS_ORIG=$ARGUMENTS;
ARGUMENTS_TEMP=:$ARGUMENTS_ORIG;
ARGUMENTS_TEMP=$(echo ${ARGUMENTS_TEMP//!});
ARGUMENTS_TEMP=$(echo ${ARGUMENTS_TEMP//::/:});

if [ "$ARGUMENTS_TEMP" ]; then
    while getopts $ARGUMENTS_TEMP ARGUMENT; do
        case $ARGUMENT in
            [a-zA-Z])
                case $ARGUMENTS_TEMP in
                    *$ARGUMENT:*)
                        printf -v ARG_$ARGUMENT %s "$OPTARG";;
                    *$ARGUMENT*)
                        printf -v ARG_$ARGUMENT %s 1;;
                esac;
                ;;
        esac;
    done;
fi;

ARGUMENTS_TEMP=$ARGUMENTS_ORIG;
ARGUMENTS_TEMP=$(echo ${ARGUMENTS_TEMP//:});
ARGUMENTS_TEMP=$(echo ${ARGUMENTS_TEMP//!/ });
ARGUMENTS_TEMP=$(echo $ARGUMENTS_TEMP | sed -e "s/[a-zA-Z]*$//");

if [ "$ARGUMENTS_TEMP" ]; then
    for ARGUMENT in $ARGUMENTS_TEMP; do
        ARGUMENT=${ARGUMENT:${#ARGUMENT}-1};
        ARG_NAME=ARG_$ARGUMENT;
        if [ ! ${!ARG_NAME} ]; then
            echo "(arguments) [警告] ｵﾌﾟｼｮﾝ[$ARGUMENT](必須)が指定されていません｡";
            exit 1;
        fi;
    done;
fi;

unset ARG_NAME ARGUMENT ARGUMENTS_ORIG ARGUMENTS_RSVD ARGUMENTS_TEMP;

[ $ARG_H ] && HELP 0 && exit 1;
