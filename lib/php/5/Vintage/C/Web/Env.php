<?php

    namespace Vintage\C\Web {

        final class Env extends \Vintage\A\Util\Env {

            private static $REGEX_DEVICE_NAME = array(
                'ipad'   => '/^Mozilla\/5\.0 \(iPad;/',
                'iphone' => '/^Mozilla\/5\.0 \(iPhone;/',
                'ipod'   => '/^Mozilla\/5\.0 \(iPod;/'
            );

            private static $MAP_DEVICE_TYPE = array(
                'smart'  => array('iphone', 'ipod'),
                'tablet' => array('ipad'),
                'other'  => array('other')
            );

            final public static function device_name() {
                $user_agent = self::user_agent();
                foreach (self::$REGEX_DEVICE_NAME as $name => $regex) {
                    if (preg_match($regex, $user_agent)) {
                        return $name;
                    }
                }
                return 'other';
            }

            final public static function device_type() {
                $device_name = self::device_name();
                foreach (self::$MAP_DEVICE_TYPE as
                    $DEVICE_TYPE => $DEVICE_NAMES) {
                    foreach ($DEVICE_NAMES as $DEVICE_NAME) {
                        if ($device_name == $DEVICE_NAME) {
                            return $DEVICE_TYPE;
                        }
                    }
                }
                throw new \Exception();
            }

            final public static function user_agent($strict = false) {
                return isset($_SERVER['HTTP_USER_AGENT']) ?
                    $_SERVER['HTTP_USER_AGENT'] : ($strict ? null : '');
            }

            final public static function ua($strict = false) {
                return isset($_SERVER['HTTP_USER_AGENT']) ?
                    $_SERVER['HTTP_USER_AGENT'] : ($strict ? null : '');
            }

            final public static function ip($strict = false) {
                return isset($_SERVER['REMOTE_ADDR']) ?
                    $_SERVER['REMOTE_ADDR'] : ($strict ? null : '');
            }
        }
    }

?>
