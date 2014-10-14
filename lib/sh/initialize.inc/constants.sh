if [ -z $VTG_NO_CONSTANTS ]; then

    readonly VTG_AREA=$($VTG_ROOT/pro/Vintage/bin/env/area);
    readonly VTG_HOST=$($VTG_ROOT/pro/Vintage/bin/env/host);
    readonly VTG_MANAGER=$($VTG_ROOT/pro/Vintage/bin/env/manager);
    readonly VTG_ROLES=$($VTG_ROOT/pro/Vintage/bin/env/roles);
    readonly VTG_APPS=$($VTG_ROOT/pro/Vintage/bin/env/apps);
    readonly VTG_TAGS=$($VTG_ROOT/pro/Vintage/bin/env/tags);
fi;

readonly PROGRAM=$USER@:${0##*/};
