<?php

    namespace {

        final class Vintage {

            private static $INIT_DATETIME = null;
            private static $DPS_EXTENSION = array(
                '/opt/local/lib/php/pear',
                '/usr/share/pear'
            );

            final private static function autoload($class) {

                if (preg_match('/^Vintage\\\/', $class)) {
                    require_once(
                        $_SERVER['VTG_ROOT'] .
                        '/pro/Vintage/lib/php/5/' .
                        str_replace('\\', '/', $class) .
                        '.php'
                    );
                } else {
                    // コメントアウトするとメール送れなくなる。
                    include_once(str_replace('\\', '/', $class) . '.php');
                }
            }

            final public static function initialize() {

                date_default_timezone_set('Asia/Tokyo');
                spl_autoload_register('\Vintage::autoload');

                self::initialize_conf();

                foreach (self::$DPS_EXTENSION as $DP_EXTENSION) {
                    if (is_dir($DP_EXTENSION)) {
                        ini_set('extension_dir', $DP_EXTENSION);
                    }
                }
            }

            final private static function initialize_conf() {
                $INIT_DATETIME = explode(',', date('Y,m,d,H,i,s'));
                for ($i = 0; $i < count($INIT_DATETIME); $i++) {
                    $INIT_DATETIME[$i] = (integer) $INIT_DATETIME[$i];
                }
                self::$INIT_DATETIME = $INIT_DATETIME;
            }

            final public static function init_datetime($mode = 0) {
                if ($mode == 0) {
                    return self::$INIT_DATETIME;
                } elseif ($mode == 1) {
                    $format = '%04d%02d%02d%02d%02d%02d';
                    return vsprintf($format, self::$INIT_DATETIME);
                } elseif ($mode == 2) {
                    $format = '%04d/%02d/%02d %02d:%02d:%02d';
                    return vsprintf($format, self::$INIT_DATETIME);
                } else { throw new \Exception(); }
            }
        }

        \Vintage::initialize();
    }

?>
