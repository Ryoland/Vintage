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
      // Config(s)
      //============================================================================================

      //============================================================================================
      // Member(s)
      //============================================================================================

      //============================================================================================
      // Method(s)
      //============================================================================================

      /****/
      final public static function &retrieve(array &$conditions = [], array &$a = []) {

        // Argument(s) -----------------------------------------------------------------------------

        // Parameter(s) ----------------------------------------------------------------------------

        $d              = null;
        $t              = [];
        $session_commit = false;

        $INPUT = [
          'cookie'  =>& $_COOKIE,
          'get'     =>& $_GET,
          'post'    =>& $_POST,
          'request' =>& $_REQUEST,
          'server'  =>& $_SERVER,
          'session' =>& $_SESSION,
          'option'  =>  []
        ];

        // Process(es) -----------------------------------------------------------------------------






        foreach ($conditions as $autonym => &$condition) {
          unset($condition);
        }

        // Option
        if ($option_required) {

          $soptions = [];
          $loptions = [];

          foreach ($conditions as $autonym => &$condition) {
            if (in_array('session', $condition['sources'])) {
              foreach ($condition['names'] as $name) {
                if (strlen($name) == 1) {
                  $soptions[] = "$name::";
                } else {
                  $loptions[] = "$name::";
                }
              }
            }
            unset($condition);
          }

          $INPUT['option'] = getopt(implode('', $soptions), $loptions);
        }
        ///

        // Session
        if ($session_required) {
          if (session_status() == \PHP_SESSION_DISABLED) {
            if (session_start()) {
              $session_commit = true;
            } else {
              goto FIN;
            }
          }

          if (session_status() == \PHP_SESSION_ACTIVE) {
            $INPUT['session'] =& $_SESSION;
          }
        }
        ///









        foreach ($conditions as $autonym =>& $condition) {

          $alias   = isset($condition['alias'])   ? $condition['alias']   : null;
          $default = isset($condition['default']) ? $condition['default'] : null;
          $source  = isset($condition['source'])  ? $condition['source']  : null;

          $aliases = isset($alias)  ? explode(',', $alias)  : [];
          $sources = isset($source) ? explode(',', $source) : [];
          $names   = array_merge((array) $autonym, $aliases);

          foreach (array_filter($names) as $name) {
            foreach (array_filter($sources) as $source) {
              if (isset($INPUT[$source])) {
                if (isset($INPUT[$source][$name])) {
                  $t[$autonym] = $INPUT[$source][$name];
                  break 2;
                }
              }
            }
          }

          unset($condition);
        }

        if ($session_commit) session_commit();

        // Result(s) -------------------------------------------------------------------------------

        $d =& $t;

        FIN :

        return $d;

      //============================================================================================
    }

    //==============================================================================================
  }

?>
