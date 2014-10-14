<?php

    namespace Vintage\C\PHP {

        final class VArray {

            final public static function is($mixed) {
                return is_array($mixed) ? true : false;
            }
        }
    }

?>
