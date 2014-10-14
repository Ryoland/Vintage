<?php

    namespace Vintage\C\PHP {

        final class VInteger {

            const ID_BOOLEAN  = 'boolean';
            const ID_NATURAL  = 'natural';
            const ID_NEGATIVE = 'negative';
            const ID_POSITIVE = 'positive';
            const ID_ZERO_NEG = 'zero-';
            const ID_ZERO_POS = 'zero+';

            final public static function is($mixed) {
                return is_int($mixed) ? true : false;
            }

            final public static function is_boolean($mixed) {
                if (!self::is($mixed)) { return null; }
                return ($mixed == 0 || $mixed == 1) ? true : false;
            }

            final public static function is_natural($mixed) {
                if (!self::is($mixed)) { return null; }
                return ($mixed > 0) ? true : false;
            }

            final public static function is_negative($mixed) {
                if (!self::is($mixed)) { return null; }
                return ($mixed < 0) ? true : false;
            }

            final public static function is_positive($mixed) {
                if (!self::is($mixed)) { return null; }
                return ($mixed > 0) ? true : false;
            }

            final public static function is_zero_neg($mixed) {
                if (!self::is($mixed)) { return null; }
                return ($mixed <= 0) ? true : false;
            }

            final public static function is_zero_pos($mixed) {
                if (!self::is($mixed)) { return null; }
                return ($mixed >= 0) ? true : false;
            }

            final public static function is_switch($id, $mixed) {
                switch ($id) {
                    case self::ID_BOOLEAN  : return self::is_boolean( $mixed); break;
                    case self::ID_NATURAL  : return self::is_natural( $mixed); break;
                    case self::ID_NEGATIVE : return self::is_negative($mixed); break;
                    case self::ID_POSITIVE : return self::is_positive($mixed); break;
                    case self::ID_ZERO_NEG : return self::is_zero_neg($mixed); break;
                    case self::ID_ZERO_POS : return self::is_zero_pos($mixed); break;
                    default                : throw new \Exception();           break;
                }
            }

            final public static function cast($id, $mixed) {

                if (self::is_switch($id, $mixed)) {

                    switch ($id) {
                        case self::ID_BOOLEAN : return (boolean) $mixed; break;
                        default               : return           $mixed; break;
                    }

                } else { return $mixed; }
            }
        }
    }

?>
