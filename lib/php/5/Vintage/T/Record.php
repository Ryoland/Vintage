<?php

    namespace Vintage\T {

        trait Record {

            protected static $DB_PKEY_DEFAULT = ['id'];

            protected static $Database = null;

            final protected static function &db_pkey() {
                if (isset(static::$DB_PKEY)) {
                    return static::$DB_PKEY;
                } elseif (isset(static::$DB_PKEYS)) {
                    return static::$DB_PKEYS;
                }
                return static::$DB_PKEY_DEFAULT;
            }
        }
    }

?>
