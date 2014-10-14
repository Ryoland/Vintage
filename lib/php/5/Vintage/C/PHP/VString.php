<?php

    namespace Vintage\C\PHP {

        final class VString {

            const ID_BOOLEAN = 'boolean';
            const ID_INTEGER = 'integer';
            const ID_STRING  = 'string';

            const RX_BOOLEAN = '/^(true|false)$/i';
            const RX_INTEGER = '/^[\+\-]?[0-9]+$/';
            const RX_EMAIL   = '/^(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:"(?:\\[^\r\n]|[^\\"])*")))\@(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:\[(?:\\\S|[\x21-\x5a\x5e-\x7e])*\])))$/';

            final public static function is($mixed) {
                return is_string($mixed) ? true : false;
            }

            final public static function is_email($mixed) {
                if (!self::is($mixed)) { return null; }
                return preg_match(self::RX_EMAIL, $mixed) ? true : false;
            }

            final public static function like_boolean($mixed) {
                return self::like(self::ID_BOOLEAN, $mixed);
            }

            final public static function like_integer($mixed) {
                return self::like(self::ID_INTEGER, $mixed);
            }

            final public static function like($id, $mixed) {

                if (!self::is($mixed)) { return null; }

                switch ($id) {
                    case self::ID_STRING  : return true;            break;
                    case self::ID_BOOLEAN : $x = self::RX_BOOLEAN;  break;
                    case self::ID_INTEGER : $x = self::RX_INTEGER;  break;
                    default               : throw new \Exception(); break;
                }

                return preg_match($x, $mixed) ? true : false;
            }

            final public static function cast($id, $mixed) {

                if (self::like($id, $mixed)) {

                    switch ($id) {
                        case self::ID_BOOLEAN : return (boolean) $mixed; break;
                        case self::ID_INTEGER : return (integer) $mixed; break;
                        default               : return           $mixed; break;
                    }

                } else { return $mixed; }
            }

            final public static function len($target) {
                if (!self::is($target)) { return null; }
                return strlen($target);
            }

            final public static function is_simple_case($target) {
                if (!self::is($target)) { return null; }
                $regexp = '/^[a-z]+( [a-z]+)*$/';
                return preg_match($regexp, $target) ? true : false;
            }

            final public static function is_camel_case($target) {
                if (!self::is($target)) { return null; }
                $regexp = '/^[a-z]+([A-Z][a-z]*)*$/';
                return preg_match($regexp, $target) ? true : false;
            }

            final public static function is_pascal_case($target) {
                if (!self::is($target)) { return null; }
                $regexp = '/^([A-Z][a-z]*)+$/';
                return preg_match($regexp, $target) ? true : false;
            }

            final public static function is_snake_case($target) {
                if (!self::is($target)) { return null; }
                $regexp = '/^[a-z]+(_[a-z]+)*$/';
                return preg_match($regexp, $target) ? true : false;
            }

            final public static function simple_case($target) {
                $return = null;
                if (self::is_simple_case($target)) {
                    $return = $target;
                } elseif (self::is_camel_case($target)) {
                    $regexp = '/([A-Z])/';
                    $return = preg_replace($regexp, " $1", $target);
                    $return = preg_replace('/^ /', '', $return);
                    $return = strtolower($return);
                } elseif (self::is_pascal_case($target)) {
                    $regexp = '/([A-Z])/';
                    $return = preg_replace($regexp, " $1", $target);
                    $return = preg_replace('/^ /', '', $return);
                    $return = strtolower($return);
                } elseif (self::is_snake_case($target)) {
                    $return = str_replace('_', ' ', $target);
                } else { $return = $target; }
                return $return;
            }

            final public static function pascal_case($target) {
                $return = self::simple_case($target);
                if (!self::is_simple_case($return)) { return $target; }
                $return = ucwords($return);
                $return = str_replace(' ', '', $return);
                return $return;
            }

            final public static function snake_case($target) {
                $return = self::simple_case($target);
                if (!self::is_simple_case($return)) { return $target; }
                $return = str_replace(' ', '_', $return);
                return $return;
            }

            //================================================================
        }
    }

?>
