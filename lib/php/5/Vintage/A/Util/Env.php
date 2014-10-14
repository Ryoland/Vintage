<?php

    namespace Vintage\A\Util {

        abstract class Env extends \Vintage\A\Lib\S {

            final public function root($strict = false) {
                return isset($_SERVER['VTG_ROOT']) ?
                    $_SERVER['VTG_ROOT'] : ($strict ? null : '');
            }

            final public function area($strict = false) {
                $fp = self::root() . '/etc/area';
                $fd = file_exists($fp) ?
                    file($fp, FILE_IGNORE_NEW_LINES) : array();
                return isset($fd[0]) ? $fd[0] : ($strict ? null : '');
            }

            final public function host($strict = false) {
                $fp = self::root() . '/etc/host';
                $fd = file_exists($fp) ?
                    file($fp, FILE_IGNORE_NEW_LINES) : array();
                return isset($fd[0]) ? $fd[0] : ($strict ? null : '');
            }

            final public function stage($strict = false) {
                return isset($_SERVER['VTG_STAGE']) ?
                    $_SERVER['VTG_STAGE'] : ($strict ? null : '');
            }
        }
    }

?>
