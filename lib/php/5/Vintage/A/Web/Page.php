<?php

    namespace Vintage\A\Web {

        use \Vintage\C\Data\Config  as Config;
        use \Vintage\C\PHP\VArray   as VArray;
        use \Vintage\C\PHP\VInteger as VInteger;
        use \Vintage\C\PHP\VString  as VString;
        use \Vintage\C\Web\Env      as Env;

        use \Vintage                 as Vintage;
        use \Vintage\C\Util\Check    as Check;
        use \Vintage\C\Util\Template as Template;

        abstract class Page extends \Vintage\A\Lib {

            private static $TMPL_EXTENSION_DEFAULT = 'html';

            protected static $PROJECT            = 'Vintage';
            protected static $PAGE               = 'default';
            protected static $EXTENSION          = 'html';
            protected static $DISPLAY_ERRORS     = true;
            protected static $DISPLAY_LOGIN      = true;
            protected static $USE_VTG_DATA       = false;
            protected static $USE_VTG_TPL_ERRORS = true;
            protected static $USE_VTG_TPL_LOGIN  = true;
            protected static $QUERY_METHOD       = 'all';

            private static $HEADERS = array(
                'css'        => 'Content-Type: text/css',
                'html'       => 'Content-Type: text/html',
                'javascript' => 'Content-Type: application/javascript',
                'json'       => 'Content-Type: application/json'
            );

            private $Config = null;

            final protected function queries2() {

                $queries = array();
                $errors  = array();

                if (!empty(static::$QUERIES)) {

if (!isset($QUERIES['no_limit'])) {
    static::$QUERIES['no_limit'] = ['type' => 'boolean'];
}
if (!isset($QUERIES['item_p'])) {
    static::$QUERIES['item_p'] = ['type' => 'integer'];
}
if (!isset($QUERIES['page_c'])) {
    static::$QUERIES['page_c'] = ['type' => 'integer'];
}
if (!isset($QUERIES['pageC'])) {
    static::$QUERIES['pageC'] = ['type' => 'integer', 'default' => 1];
}
if (!isset($QUERIES['itemP'])) {
    static::$QUERIES['itemP'] = ['type' => 'integer', 'default' => 20];
}

                    foreach (static::$QUERIES as $name => $QUERY) {

                        $value  = null;
                        $method = isset($QUERY['method']) ?
                            $QUERY['method'] : static::$QUERY_METHOD;

                        if ($method == 'get') {
                            if (isset($_GET[$name])) {
                                $value = $_GET[$name];
                            }
                        } elseif ($method == 'post') {
                            if (isset($_POST[$name])) {
                                $value = $_POST[$name];
                            }
                        } elseif ($method == 'all') {
                            if (isset($_REQUEST[$name])) {
                                $value = $_REQUEST[$name];
                            }
                        }

                        if (VString::is($value)) {
                            if (!VString::len($value)) {
                                $value = null;
                            }
                        }

                        $queries[$name] = $value;
                        unset($QUERY);
                    }

if (!empty($queries)) {
    if (!isset($queries['page_c'])) {
        if (isset($queries['pageC'])) {
            $queries['page_c'] = $queries['pageC'];
        }
        else {
            $queries['page_c'] = 1;
        }
    }

    if (!isset($queries['item_p'])) {
        if (isset($queries['itemP'])) {
            $queries['item_p'] = $queries['itemP'];
        }
        else {
            $queries['item_p'] = 20;
        }
    }
}

                    list($queries, $errors) =
                        Check::run($queries, static::$QUERIES);
                }

                return array($queries, $errors);
            }

            protected function display(array $data = array()) {
$F = null;
if (isset(static::$DATA_FORMAT)) $F = static::$DATA_FORMAT;
if (!$F) $F = $this->FORMAT;

                header(self::$HEADERS[$F]);
                echo $this->fetch(array('data'=>$data));
            }

            final private function fetch(array $a) {

                $data    = isset($a['data'])    ? $a['data']    : array();
                $page    = isset($a['page'])    ? $a['page']    : null;
                $project = isset($a['project']) ? $a['project'] : static::$PROJECT;

if (!isset($data['data'])) $data['data'] = [];

        $A =& $this->A;

        if (!isset($page)) {
          if (isset($A['page'])) {
            $page = $A['page'];
          }
          else {
            $class = get_class($this);
            $page  = end(explode('\\', $class));
            $page  = VString::snake_case($page);
          }
        }

        $config =  null;
        $Config =& $this->Config();

        if (isset($Config)) {
          $VINTAGE = $Config->VINTAGE;
          if (isset($VINTAGE)) {
            if (isset($VINTAGE['WEB'])) {
              if (isset($VINTAGE['WEB']['PAGE'])) {
                if (isset($VINTAGE['WEB']['PAGE'][$page])) {
                  $config =& $VINTAGE['WEB']['PAGE'][$page];
                }
              }
            }
          }
        }

        $DATA_FORMAT = null;

        if (isset($config, $config['DATA_FORMAT'])) {
          $DATA_FORMAT = $config['DATA_FORMAT'];
        }
        elseif (isset(static::$DATA_FORMAT)) {
          $DATA_FORMAT = static::$DATA_FORMAT;
        }
        elseif (isset(self::$DATA_FORMAT)) {
          $DATA_FORMAT = self::$DATA_FORMAT;
        }

        $TMPL_EXTENSION = null;

        if (isset($config, $config['TMPL_EXTENSION'])) {
          $TMPL_EXTENSION = $config['TMPL_EXTENSION'];
        }
        elseif (isset(static::$VTG_TMPL_EXTENSION)) {
          $TMPL_EXTENSION = static::$VTG_TMPL_EXTENSION;
        }
        else {
          $TMPL_EXTENSION = self::$TMPL_EXTENSION_DEFAULT;
        }

                if (static::$USE_VTG_DATA) {
                    $vtg_data     = self::vtg_data();
                    $data         = array_merge($data, $vtg_data);
                    $data['data'] = array_merge($data['data'], $vtg_data);
                }

$F = $DATA_FORMAT;
if (isset(static::$DATA_FORMAT)) $F = static::$DATA_FORMAT;
if (!$F) $F = $this->FORMAT;

                if ($F == 'json') {

                    if (!empty($this->H)) {
                        $data = array(
                            'head' => $this->H,
                            'data' => $data
                        );
                    }

                    array_walk_recursive(
                        $data,
                        create_function('&$v', 'if (is_string($v)) $v = rawurlencode($v);')
                    );

                    return json_encode($data);

                } else {

                    $path = sprintf(
                        '%s/%s.%s',
                        $TMPL_EXTENSION,
                        $page,
                        $TMPL_EXTENSION
                    );

                    if (self::$ENGINE == 'smarty') {
                        return Template::fetch_by_smarty(array(
                            'proj' => $project,
                            'path' => $path,
                            'data' => $data,
                            'type' => $F
                        ));
                    } else { throw new \Exception(); }
                }
            }

            final protected function Config(array $a = array()) {

                $projects = isset($a['projects']) ? $a['projects'] : array();

                if (!isset($this->Config)) {

                    if (empty($projects)) {
                        $projects[] = static::$PROJECT;
                    }

                    $this->Config = new Config(array(
                        'projects' => $projects
                    ));
                }

                return $this->Config;
            }

            final protected function proc() {

                $session = $this->session();

                if ($session) {
                    $this->S = $session;
                } elseif (static::$DISPLAY_LOGIN) {
                    $this->display_login();
                    return 'session';
                }

                list($queries, $errors) = $this->queries();

                if ($queries) {
                    $this->Q = $queries;
                }

                if (!empty($errors)) {
                    if (static::$DISPLAY_ERRORS) {
                        $this->H = array('status' => false);
                        $this->display_errors(array_values($errors));
                        return 'queries';
                    }
                }

                $data = [];

                if (!empty(static::$RECORD)) {
                    $data =& $this->data();
                }
                else {
                    $data =& $this->build();
                }

                if (isset($data['data'])) {
                  if (isset($data['data']['total'])) {
                    if (!isset($data['data']['item_t'])) {
                      $data['data']['item_t'] = $data['data']['total'];
                    }
                  }

                  if (!empty($queries)) {
                    if (!isset($data['data']['item_p'])) {
                      $data['data']['item_p'] = isset($queries['item_p']) ? $queries['item_p'] : null;
                    }
                    if (!isset($data['data']['page_c'])) {
                      $data['data']['page_c'] = isset($queries['page_c']) ? $queries['page_c'] : null;
                    }
                  }
                }

                $this->display($data);

                return true;
            }

            public static $QUERIES2 = array();

            protected $FORMAT = 'html';

            private static $PROJECT_DF         = 'Vintage';
            private static $PAGE_DF            = 'default';
            private static $ENGINE             = 'smarty';
            private static $ERROR_EMPTY = '%s の値がありません。';
            private static $ERROR_NULL  = '%s が指定されていません。';
            private static $ERROR_WRONG = '%s の値が正しくありません。';

            protected $H = array();
            protected $Q = array();
            protected $S = array();

            protected function queries() {

                $queries = array();
                $errors  = array();

                if (count(static::$QUERIES2) > 0) {
                    foreach (static::$QUERIES2 as $key => $conf) {

                        $value    = null;
                        $name     = isset($conf[0]) ? $conf[0] : $key;
                        $required = isset($conf[1]) ? $conf[1] : null;
                        $sources  = isset($conf[2]) ? $conf[2] : null;
                        $type1    = isset($conf[3]) ? $conf[3] : null;
                        $type2    = isset($conf[4]) ? $conf[4] : null;
                        $type3    = isset($conf[5]) ? $conf[5] : null;
                        $default  = isset($conf[6]) ? $conf[6] : null;
                        $sources  = str_split($sources);

                        foreach ($sources as $source) {
                            if ($source == 'G') {
                                if (isset($_GET[$key])) {
                                    $value = $_GET[$key];
                                    break;
                                }
                            } elseif ($source == 'P') {
                                if (isset($_POST[$key])) {
                                    $value = $_POST[$key];
                                    break;
                                }
                            } else { throw new \Exception(); }
                        }

                        $error = null;

                        if (!isset($value)) {
                            $error = self::$ERROR_NULL;
                        } else {

                            if ($type1 == 'array') {

                                if (VArray::is($value) && empty($value)) {
                                    $error = self::$ERROR_EMPTY;
                                }
                            }
                            else {
                                if (!strlen($value)) {
                                    $error = self::$ERROR_EMPTY;
                                }
                            }
                        }

                        if ($error) {

                            switch ($required) {
                                case 0 :                                         break;
                                case 1 : $errors[$key] = sprintf($error, $name); break;
                                case 2 : $value        = $default;               break;
                                case 3 : $value        = $default;               break;
                            }

                            goto RESQUE;
                        }

                        $status = true;

                        if ($type1 == 'array') {

                            if (!VArray::is($value)) {
                                $errors[$key] = sprintf(self::$ERROR_WRONG, $name);
                                goto RESQUE;
                            }

                        } else {

                            if (!VString::like($type1, $value)) {
                                $errors[$key] = sprintf(self::$ERROR_WRONG, $name);
                                goto RESQUE;
                            }
                        }

                        if ($type2) {
                            if ($type1 == 'string') {
                                switch ($type2) {
                                    case 'email' : $status = VString::is_email($value); break;
                                    default      : throw new \Exception();              break;
                                }
                            }
                        }

                        if (!$status) {
                            $errors[$key] = sprintf(self::$ERROR_WRONG, $name);
                        }

                        RESQUE :
                        if (isset($errors[$key])) {
                            if ($required == 3) {
                                $value = $default;
                                unset($errors[$key]);
                            }
                        }

                        if (!isset($errors[$key])) {

                            if ($type1 == 'array') {
                            }
                            else {

                                $value = VString::cast($type1, $value);

                                if (strlen($type2)) {
                                    switch ($type1) {
                                        case 'integer' :
                                            $value = VInteger::cast($type2, $value);
                                            break;
                                    }
                                }
                            }
                        }

                        $queries[$key] = $value;
                    }
                } elseif (!empty(static::$QUERIES)) {
                    list($queries, $errors) = $this->queries2();
                }

                return array($queries, $errors);
            }

            protected function build() {
                return array();
            }

            final protected function display_errors(
                array $errors = array(), $title = '') {
                echo $this->fetch_errors($errors, $title);
            }

            protected function display_login() {
                echo $this->fetch_login();
            }

            final private function vtg_data() {

                $data = array(
                    'VTG_DATETIME_1' => \Vintage::init_datetime(1),
                    'VTG_DATETIME_2' => \Vintage::init_datetime(2),
                    'VTG_STAGE'      => Env::stage()
                );

                if ($this->S) {
                    $data['VTG_IS_AUTHORIZED'] = 1;
                }

                if (isset($this->S['User'])) {
                    $data['VTG_USER_ID']      = $this->S['User']->id;
                    $data['VTG_USER_NAME']    = $this->S['User']->name();
                    $data['VTG_USER_MISSION'] = $this->S['User']->mission();
                }

                return $data;
            }

            final protected function fetch_errors(
                array $errors = array(), $title = '') {

                $data = array(
                    'title'  => $title,
                    'errors' => $errors
                );

                return $this->fetch_private(array(
                    'file'        => 'errors',
                    'data'        => $data,
                    'USE_VTG_TPL' => static::$USE_VTG_TPL_ERRORS
                ));
            }

            final protected function fetch_login() {
                return $this->fetch_private(array(
                    'file'        => 'login',
                    'data'        => array(),
                    'USE_VTG_TPL' => static::$USE_VTG_TPL_LOGIN
                ));
            }

            final private function fetch_private(array $a) {

                $file        = $a['file'];
                $data        = $a['data'];
                $USE_VTG_TPL = $a['USE_VTG_TPL'];

                if ($USE_VTG_TPL) {
                    $project = self::$PROJECT_DF;
                } else {
                    $project = static::$PROJECT;
                }

                return $this->fetch(array(
                    'data' => $data,
                    'page' => $file,
                    'project' => $project
                ));
            }
        }
    }

?>
