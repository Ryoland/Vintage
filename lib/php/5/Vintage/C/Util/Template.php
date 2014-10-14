<?php

    namespace Vintage\C\Util {

        final class Template extends \Vintage\A\Lib {

            private static $DP_TEMPLATE = '%s/pro/%s/dat/tpl';

            private static $SMARTY_FP_CLASS    = '%s/pro/Smarty/distribution/libs/Smarty.class.php';
            private static $SMARTY_CACHE_DIR   = '%s/tmp/%s/smarty/cache';
            private static $SMARTY_COMPILE_DIR = '%s/tmp/%s/smarty/compile';
            private static $SMARTY_CONFIG_DIR  = '%s/tmp/%s/smarty/config';

            private static $SMARTY_DEFAULT_MODIFIERS = array(
                'html' => array('escape')
            );

            final public static function fetch_by_smarty(array $a) {

                $proj = isset($a['proj']) ? $a['proj'] : 'Vintage';
                $path = isset($a['path']) ? $a['path'] : null;
                $data = isset($a['data']) ? $a['data'] : array();
                $type = isset($a['type']) ? $a['type'] : 'text';

                $proj = preg_match('/^\//', $path) ? 'Temporary' : $proj;
                $path = self::path($path, $proj);

                $root         = $_SERVER['VTG_ROOT'];
                $fp_class     = sprintf(self::$SMARTY_FP_CLASS,    $root);
                $template_dir = sprintf(self::$DP_TEMPLATE,        $root, $proj);
                $cache_dir    = sprintf(self::$SMARTY_CACHE_DIR,   $root, $proj);
                $compile_dir  = sprintf(self::$SMARTY_COMPILE_DIR, $root, $proj);
                $config_dir   = sprintf(self::$SMARTY_CONFIG_DIR,  $root, $proj);

                require_once($fp_class);

                $Smarty = new \Smarty();
                $Smarty->template_dir = $template_dir;
                $Smarty->cache_dir    = $cache_dir;
                $Smarty->compile_dir  = $compile_dir;
                $Smarty->config_dir   = $config_dir;

                if (isset(self::$SMARTY_DEFAULT_MODIFIERS[$type])) {
                    $Smarty->default_modifiers =
                        self::$SMARTY_DEFAULT_MODIFIERS[$type];
                }

                $Smarty->assign($data);

                return $Smarty->fetch($path);
            }

            final private function path($path, $proj) {
                if (preg_match('/^\//', $path)) {
                    return $path;
                } else {
                    return sprintf(
                        self::$DP_TEMPLATE . '/',
                        $_SERVER['VTG_ROOT'],
                        $proj
                    ) . $path;
                }
            }
        }
    }

?>
