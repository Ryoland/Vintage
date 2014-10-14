<?php

    namespace Vintage\C\Util {

        final class Check extends \Vintage\A\Lib\S {

            final public static function run(array $params, array $rules) {

                $errors = array();

                foreach ($rules as $name => $rule) {
                    if (!isset($params[$name])) {
                        if (isset($rule['default'])) {
                            $params[$name] = $rule['default'];
                        } else {
                            $params[$name] = null;
                        }
                    }
                }

                return array($params, $errors);
            }
        }
    }

?>
