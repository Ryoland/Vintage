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
      // Constant(s)
      //============================================================================================

      //============================================================================================
      // Static Variable(s)
      //============================================================================================

      /****/
      private static $CNT_DATA_MAX = 100;

      /****/
      private static $CNT_DATA_MIN = 0;

      /****/
      private static $ERROR_MESSAGEZ = [
        'en' => [
          'count.zero'    => '{$method} {$label}.',
          'count.inexact' => '{$method} {$cnt_data} {$label}.',
          'count.over'    => 'You can {$method} up to {$cnt_data_max} {$label}.',
          'count.under'   => '{$method} {$cnt_data_min} or more {$label}.'
        ],
        'ja' => [
          'count.zero'    => '{$label}を{$method}してください。',
          'count.inexact' => '{$label}を{$cnt_data}{$unit}{$method}してください。',
          'count.over'    => '{$label}は{$cnt_data_max}{$unit}まで{$method}できます。',
          'count.under'   => '{$label}を{$cnt_data_min}{$unit}以上{$method}してください。'
        ]
      ];

      /****/
      private static $METHOD = 'assign';

      /****/
      private static $METHODZ = [
        'en' => ['assign' => 'assign', 'input' => 'input', 'select' => 'select'],
        'ja' => ['assign' => '指定',   'input' => '入力',  'select' => '選択'  ]
      ];

      //============================================================================================
      // Dynamic Variable(s)
      //============================================================================================

      //============================================================================================
      // Static Function(s)
      //============================================================================================

      /**
       * Receive
       */
      final public static function receive($conditionz, $a = []) {

        list($d, $r) = [[], null];

        // $conditionz, $a
        if (!isset($conditionz)) {
          $d['message'] = 'Missed, $conditionz.';
          $d['trace']   = debug_backtrace();
          goto NG;
        }
        elseif (!is_array($conditionz)) {
          $d['message'] = 'Wrong, $conditionz.';
          $d['trace']   = debug_backtrace();
          goto NG;
        }
        elseif (empty($conditionz)) {
          $d['message'] = 'Empty, $conditionz.';
          $d['trace']   = debug_backtrace();
          goto NG;
        }
        elseif (!is_array($a)) {
          $d['message'] = 'Wrong, $a.';
          $d['trace']   = debug_backtrace();
          goto NG;
        }

        $language    = isset($a['language'])    ? $a['language']    : \Vintage::$LANGUAGE;
        $no_validate = isset($a['no_validate']) ? $a['no_validate'] : false;
        ///

        // $d
        $errorz = [];
        $inputz = [];
        $valuez = [];

        $sourcez = [
          'cookie'  => $_COOKIE,
          'get'     => $_GET,
          'post'    => $_POST,
          'request' => $_REQUEST,
          'server'  => $_SERVER,
          'session' => null,
          'option'  => null
        ];
        ///

        // $
        $options_l        = [];
        $options_s        = [];
        $session_commit   = false;
        $session_required = null;

        $map = [
          'alias'  => 'name',
          'source' => 'source'
        ];

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

          $inputz[$autonym] = null;

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

          // Validate
          if (!$no_validate) {

            $input =& $inputz[$autonym];

            $r = self::validate(
              $input,
              array_merge(['language'=>$language], $condition)
            );

            if (!$r['status']) {
              $d['message'] = $r['message'];
              $d['trace']   = $r['trace'];
              goto NG;
            }
            elseif ($r['is_error']) {
              $errorz[$autonym] = $r['errorz'];
            }

            unset($input, $r);
          }
          ///

          unset($autonym, $condition);
        }

        if ($session_commit) {
          session_commit();
        }

        OK:
        $d['status']     = true;
        $d['message']    = 'OK';
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

      /**
       * Validate
       *
       * @param  mixed   $data                      [!]
       * @param  array   $a                         [!]
       * @param  integer $a['cnt_data']             [-]
       * @param  integer $a['cnt_data_max']         [=|self::$CNT_DATA_MAX]
       * @param  integer $a['cnt_data_min']         [=|self::$CNT_DATA_MIN]
       * @param  string  $a['label']                [!]
       * @param  string  $a['language']             [=|\Vintage::$LANGUAGE]
       * @param  string  $a['method']               [=|self::$METHOD]
       * @param  string  $a['unit']                 [-]
       * @param  mixed   $a['default']              [-]
       * @param  mixed   $a['substitute']           [-]
       * @param  boolean $a['is_plural']            [=|false]
       * @param  boolean $a['is_required']          [=|false]
       * @return array   $d                         [!]
       * @return boolean $d['status']               [!]
       * @return string  $d['message']              [!]
       * @return array   $d['trace']                [?|$d['status']==false]
       * @return array   $d['errorz']               [?|$d['status']==true]
       * @return string  $d['errorz'][*]['code']    [?|!empty($d['errorz'])]
       * @return string  $d['errorz'][*]['message'] [?|!empty($d['errorz'])]
       * @return integer $d['cnt_data']             [?|$d['status']==true]
       * @return integer $d['cnt_default']          [?|$d['status']==true]
       * @return integer $d['cnt_substitute']       [?|$d['status']==true]
       * @return string  $d['error_code']           [?|$d['status']==true&&$a['is_plural']==false]
       * @return string  $d['error_message']        [?|$d['status']==true&&$a['is_plural']==false]
       * @return mixed   $d['data']                 [?|$d['status']==true]
       * @return boolean $d['is_error']             [?|$d['status']==true]
       */
      final public static function validate($data, $a) {

        list($d, $r) = [[], null];

        // $a
        if (!isset($a)) {
          $d['message'] = 'Missed, $a.';
          $d['trace']   = debug_backtrace();
          goto NG;
        }
        elseif (!is_array($a)) {
          $d['message'] = 'Wrong, $a.';
          $d['trace']   = debug_backtrace();
          goto NG;
        }

        $cnt_data     = isset($a['cnt_data'])     ? $a['cnt_data']     : null;
        $cnt_data_max = isset($a['cnt_data_max']) ? $a['cnt_data_max'] : self::$CNT_DATA_MAX;
        $cnt_data_min = isset($a['cnt_data_min']) ? $a['cnt_data_min'] : self::$CNT_DATA_MIN;
        $label        = isset($a['label'])        ? $a['label']        : null;
        $language     = isset($a['language'])     ? $a['language']     : \Vintage::$LANGUAGE;
        $method       = isset($a['method'])       ? $a['method']       : self::$METHOD;
        $unit         = isset($a['unit'])         ? $a['unit']         : null;
        $default      = isset($a['default'])      ? $a['default']      : null;
        $substitute   = isset($a['substitute'])   ? $a['substitute']   : null;
        $is_plural    = isset($a['is_plural'])    ? $a['is_plural']    : false;
        $is_required  = isset($a['is_required'])  ? $a['is_required']  : false;

        $data = $is_plural ? (array) $data : [$data];
        ///

        // $
        $error_codez    = [];
        $error_paramz   = [];
        $cnt_data_real  = 0;
        $cnt_default    = 0;
        $cnt_substitute = 0;
        $error_code     = null;
        $error_message  = null;
        $is_error       = null;
        ///

        // Default
        foreach ($data as $i => &$data_i) {

          if (isset($data_i)) {
            $cnt_data_real++;
          }
          elseif (isset($default)) {
            $data_i = $default;
            $cnt_data_real++;
            $cnt_default++;
          }

          unset($i, $data_i);
        }
        ///

        // Count
        if ($cnt_data_real == 0) {
          if ($is_required) {
            $error_codez['_'] = 'count.zero';
            $error_paramz['_'] = [];
            goto OK;
          }
        }

        if ($is_plural) {
          if (isset($cnt_data)) {
            if ($cnt_data_real != $cnt_data) {
              $error_codez['_']  = 'count.inexact';
              $error_paramz['_'] = ['cnt_data' => $cnt_data];
              goto OK;
            }
          }
          elseif ($cnt_data_real > $cnt_data_max) {
            $error_codez['_']  = 'count.over';
            $error_paramz['_'] = ['cnt_data_max' => $cnt_data_max];
            goto OK;
          }
          elseif ($cnt_data_real < $cnt_data_min) {
            $error_codez['_']  = 'count.under';
            $error_paramz['_'] = ['cnt_data_min' => $cnt_data_min];
            goto OK;
          }
        }
        ///

        foreach ($data as $i => &$data_i) {

          if (!isset($data_i)) {
            unset($i, $data_i);
            continue;
          }

          $is_retried = false;

          TOP:

          BTM:

          // Substitute
          if (isset($error_codez[$i])) {
            if (isset($substitute)) {
              if (!$is_retried) {
                unset($error_codez[$i]);
                $data_i     = $substitute;
                $is_retried = true;
                $cnt_substitute++;
                goto TOP;
              }
            }
          }
          ///

          unset($i, $data_i, $is_retried);
        }

        OK:
        if (!empty($error_codez)) {

          $errorz   = [];
          $method   = self::$METHODZ[$language][$method];
          $is_error = true;

          foreach ($error_codez as $i => $error_code_i) {

            // $error_paramz_i
            $label_tmp = $label;

            if ($is_plural) {
              $label_tmp = sprintf('%s(%d)', $label, $i + 1);
            }

            $error_paramz_i = array_merge($error_paramz[$i], [
              'label'  => $label_tmp,
              'method' => $method,
              'unit'   => $unit
            ]);

            unset($label_tmp);
            ///

            // $error_message_i
            list($from, $to) = [[], []];

            foreach ($error_paramz_i as $k => $v) {
              $from[] = '{$' . $k . '}';
              $to[]   = $v;
              unset($k, $v);
            }

            $error_message_i = self::$ERROR_MESSAGEZ[$language][$error_code_i];
            $error_message_i = str_replace($from, $to, $error_message_i);
            $first           = mb_substr($error_message_i, 0, 1);

            if (strlen($first) == mb_strlen($first)) {
              $error_message_i = ucfirst($error_message_i);
            }

            unset($from, $to, $first);
            ///

            // $errorz[$i], $error_code, $error_message
            $errorz[$i] = [
              'code'    => $error_code_i,
              'message' => $error_message_i
            ];

            if (!$is_plural) {
              $error_code    = $error_code_i;
              $error_message = $error_message_i;
            }
            ///

            unset($i, $error_code_i, $error_paramz_i, $error_message_i);
          }
        }
        else {
          $is_error = false;
        }

        $d['status']         = true;
        $d['message']        = 'OK';
        $d['errorz']         = $errorz;
        $d['cnt_data']       = $cnt_data_real;
        $d['cnt_default']    = $cnt_default;
        $d['cnt_substitute'] = $cnt_substitute;
        $d['data']           = $is_plural ? $data : $data[0];
        $d['is_error']       = $is_error;

        if (!$is_plural) $d['error_code']    = $error_code;
        if (!$is_plural) $d['error_message'] = $error_message;

        goto FIN;

        NG:
        $d['status'] = false;
        goto FIN;

        FIN:
        return $d;
      }

      //============================================================================================
      // Dynamic Function(s)
      //============================================================================================

      //============================================================================================
    }

    //==============================================================================================
  }

?>
