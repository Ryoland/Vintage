<?php

    namespace Vintage\C\Util {

        use \Vintage\C\PHP\VString as VString;

        final class Session extends \Vintage\A\Lib {

            private static $CIPHER = 'sha512';
            private static $GLUE   = 'Rkf_59%9b^QywY3jb|#U683O!8QkZ_!H';
            private static $SALT   = 'y3ke)4{@\!7GLG9vM%0zl3K2&#7TOdyF';

            final public static function make(array $a = array()) {

                $id           = isset($a['id'])           ? $a['id']           : '';
                $datetime     = isset($a['datetime'])     ? $a['datetime']     : date('YmdHis');
                $use_datetime = isset($a['use_datetime']) ? $a['use_datetime'] : true;
                $cipher       = isset($a['cipher'])       ? $a['cipher']       : self::$CIPHER;
                $glue         = isset($a['glue'])         ? $a['glue']         : self::$GLUE;
                $salt         = isset($a['salt'])         ? $a['salt']         : self::$SALT;

                $data  = array(       $id);
                $salts = array($salt, $id);

                if ($use_datetime) {
                    $data[]  = $datetime;
                    $salts[] = $datetime;
                }

                $data[]  = hash($cipher, implode($glue, $salts));
                $session = implode('_', $data);

                return $session;
            }

            final public static function check(array $a = array()) {

                $session      = isset($a['session'])      ? $a['session']      : null;
                $expires      = isset($a['expires'])      ? $a['expires']      : null;
                $id           = isset($a['id'])           ? $a['id']           : null;
                $use_datetime = isset($a['use_datetime']) ? $a['use_datetime'] : true;
                $cipher       = isset($a['cipher'])       ? $a['cipher']       : null;
                $glue         = isset($a['glue'])         ? $a['glue']         : null;
                $salt         = isset($a['salt'])         ? $a['salt']         : null;

                $status       = false;
                $parts        = array();
                $matches      = array();
                $tmp_id       = null;
                $tmp_datetime = null;
                $tmp_code     = null;

                if (VString::len($session)) {

                    $regex = $use_datetime ?
                        '/^([0-9a-zA-Z]+)_(\d{14})_([0-9a-zA-Z]+)$/' :
                        '/^([0-9a-zA-Z]+)_([0-9a-zA-Z]+)$/';

                    if (preg_match($regex, $session, $matches)) {

                        if ($use_datetime) {
                            $tmp_id       = $matches[1];
                            $tmp_datetime = $matches[2];
                            $tmp_code     = $matches[3];
                        } else {
                            $tmp_id       = $matches[1];
                            $tmp_datetime = null;
                            $tmp_code     = $matches[2];
                        }

                        $restored = self::make(array(
                            'id'           => $tmp_id,
                            'datetime'     => $tmp_datetime,
                            'use_datetime' => $use_datetime,
                            'cipher'       => $cipher,
                            'glue'         => $glue,
                            'salt'         => $salt
                        ));

                        if (isset($restored) && ($restored == $session)) {
                            if ($use_datetime && isset($expires)) {
                                if ($tmp_time = strtotime($tmp_datetime)) {
                                    if (time() - $tmp_time < $expires) {
                                        $status = true;
                                    }
                                }
                            } else { $status = true; }
                        }

                        if ($status) {
                            if (isset($id) && ($id != $tmp_id)) {
                                $status = false;
                            }
                        }

                        if ($status) {
                            $parts = array(
                                'id'       => $tmp_id,
                                'datetime' => $tmp_datetime,
                                'code'     => $tmp_code
                            );
                        }
                    }
                }

                return array($status, $parts);
            }
        }
    }

?>
