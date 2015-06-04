<?php

  namespace Vintage\C\Data {

    //==============================================================================================
    // Use
    //==============================================================================================

    //==============================================================================================
    // Class
    //==============================================================================================

    /****/
    final class Input {

      //============================================================================================
      // Trait(s)
      //============================================================================================

      //============================================================================================
      // Property(ies)
      //============================================================================================

      //============================================================================================
      // Method(s)
      //============================================================================================

      /****/
      final public static function receive($conditionz = [], $a = []) {

        // $d
        $d = [
          'status'  => null,
          'message' => null,
          'file'    => __FILE__,
          'class'   => __CLASS__,
          'method'  => __METHOD__,
          'line'    => null,
          'errorz'  => null,
          'inputz'  => null,
          'sourcez' => null,
          'valuez'  => null
        ];

        if (!is_array($conditionz)) {
          $d['message'] = 'Wrong, $conditionz.';
          $d['line']    = __LINE__;
          goto NG;
        }
        elseif (!is_array($a)) {
          $d['message'] = 'Wrong, $a.';
          $d['line']    = __LINE__;
          goto NG;
        }

        $sourcez = [
          'cookie'  => $_COOKIE,
          'get'     => $_GET,
          'post'    => $_POST,
          'request' => $_REQUEST,
          'server'  => $_SERVER,
          'session' => null,
          'option'  => null
        ];

        $errorz = [];
        $inputz = [];
        $valuez = [];
        ///

        // $
        $map = [
          'alias'  => 'name',
          'source' => 'source'
        ];

        $session_commit   = false;
        $session_required = null;

        $valuez = array_fill_keys(array_values($map), []);
        ///

        foreach ($conditionz as $autonym => $condition) {

          if (strlen($autonym)) {

            $valuez['name'][$autonym] = [$autonym];

            foreach ($map as $from => $to) {

              if (!isset($valuez[$to][$autonym])) {
                $valuez[$to][$autonym] = [];
              }

              if (isset($condition[$from])) {

                $value    =  $condition[$from];
                $values_0 =& $valuez[$to][$autonym];
                $values_1 =  [];

                if (is_string($value) && strlen($value)) {
                  $values_1 = array_unique(explode(',', $value));
                }
                elseif (is_array($value) && !empty($value)) {
                  $values_1 = array_unique(array_values($value));
                }
                elseif (is_int($value)) {
                  $values_1 = [$value];
                }

                $values_1 = array_filter($values_1, function ($v) {
                  return (is_string($v) && strlen($v)) || is_int($v);
                });

                $values_0 = array_merge($values_0, $values_1);

                // Source
                if (!empty($values_0)) {
                  if ($from == 'source') {

                    // Option
                    if (in_array('option', $values_0)) {
                      foreach ($valuez['name'][$autonym] as $name) {

                        if (strlen($name) == 1) {
                          $options_s[] = "$name::";
                        }
                        else {
                          $options_l[] = "$name::";
                        }

                        unset($name);
                      }
                    }
                    ///

                    // Session
                    if (!isset($session_required)) {
                      if (in_array('session', $values_0)) {

                        $session_required = true;

                        if (session_status() == \PHP_SESSION_DISABLED) {
                          if (session_start()) {
                            $session_commit = true;
                          }
                        }

                        if (session_status() == \PHP_SESSION_ACTIVE) {
                          $sourcez['session'] = $_SESSION;
                        }
                      }
                    }
                    ///
                  }
                }
                ///

                unset($value, $values_0, $values_1);
              }

              unset($from, $to);
            }
          }

          unset($autonym, $condition);
        }

        // Option
        if (!empty($options_s) || !empty($options_l)) {
          $options_s         = array_unique($options_s);
          $options_l         = array_unique($options_l);
          $sourcez['option'] = getopt(implode('', $options_s), $options_l);
        }
        ///

        foreach ($conditionz as $autonym => $condition) {
          foreach ($valuez['name'][$autonym] as $name) {
            foreach ($valuez['source'][$autonym] as $source) {

              if (isset($sourcez[$source][$name])) {
                $inputz[$autonym] = $sourcez[$source][$name];
                unset($name, $source);
                break 2;
              }

              unset($source);
            }

            unset($name);
          }

          unset($autonym, $condition);
        }

        if ($session_commit) {
          session_commit();
        }

        OK:
        $d['status']     = true;
        $d['message']    = 'Succeeded.';
        $d['conditionz'] = $conditionz;
        $d['errorz']     = $errorz;
        $d['inputz']     = $inputz;
        $d['sourcez']    = $sourcez;
        $d['valuez']     = $valuez;
        goto FIN;

        NG:
        $d['status'] = false;
        goto FIN;

        FIN:
        return $d;
      }

      //============================================================================================
    }

    //==============================================================================================
  }

?>
