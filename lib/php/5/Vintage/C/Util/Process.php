<?php

    namespace Vintage\C\Util {

        use \Vintage\C\PHP\VString as VString;

        final class Process extends \Vintage\A\Lib {

            final public static function start(array $a = []) {

                $name = isset($a['name']) ? $a['name'] : null;

                $d = [];

                if (!isset($name)) {
                    $d['message'] = 'No name.';
                    goto NG;
                }

                $file = self::path($a);

                if (file_exists($file)) {

                    $contents = file_get_contents($file);
                    $datetime = substr($contents, 0, 14);

                    if (time() - strtotime($datetime) > 60 * 60) {

                        if (!unlink($file)) {
                            $d['message'] = 'Failed to unlink.';
                            goto NG;
                        }
                    }
                    else {
                        $d['message'] = 'Process already exists.';
                        goto NG;
                    }
                }

                $id = date('YmdHis') . '' . mt_rand();

                if (!file_put_contents($file, $id)){
                    $d['message'] = 'Failed to write file.';
                    goto NG;
                }

                $d['id'] = $id;

                $d['message'] = 'Succeeded to write file.';
                goto OK;

                NG :
                $d['status'] = false;
                goto FIN;

                OK :
                $d['status'] = true;
                goto FIN;

                FIN :

                return $d;
            }

            final public static function finish(array $a = []) {

                $id = isset($a['id']) ? $a['id'] : null;

                $d = [];

                if (!isset($id)) {
                    $d['message'] = 'No id.';
                    goto NG;
                }

                $path = self::path($a);

                if (!file_exists($path)) {
                    $d['message'] = 'Process is not running.';
                    goto OK;
                }

                $contents = file_get_contents($path);

                if ($contents == $id) {
                    if (!unlink($path)) {
                        $d['message'] = 'Failed to finish.';
                        goto NG;
                    }
                }
                else {
                    $d['message'] = 'ID is wrong.';
                    goto NG;
                }

                $d['message'] = 'Finished.';
                goto OK;

                NG :
                $d['status'] = false;
                goto FIN;

                OK :
                $d['status'] = true;
                goto FIN;

                FIN :

                return $d;
            }

            final public static function running(array $a = []) {
                $path = self::path($a);
                if (file_exists($path)) {
                    return true;
                }
                return false;
            }

            final private static function path(array $a) {

                $project = isset($a['project']) ? $a['project'] : null;
                $name    = isset($a['name'])    ? $a['name']    : null;

                $dir  = $_SERVER['VTG_ROOT'] . '/tmp';

                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $path = $dir . '/' . $project . '.' . $name . '.process';

                return $path;
            }
        }
    }

?>
