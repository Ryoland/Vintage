<?php

    namespace Vintage\A {

        use \Vintage\C\Data\Database as Database;

        abstract class Unit extends \Vintage\A\Record {

            protected static $SKELETON = false;
            protected static $DB_SQL_GET = ' SELECT * FROM %s ';

            private $EXISTS    = null;
            private $HAS_ERROR = null;

            private $XKEYS = array();

            final public function has_error() {
                if (!isset($this->HAS_ERROR)) $this->get();
                return $this->HAS_ERROR;
            }

            final public function exists() {
                if (!isset($this->EXISTS)) $this->get();
                return $this->EXISTS;
            }

            final public function is_ok() {
                return (!$this->has_error() && $this->exists()) ? true : false;
            }

            final public function &get() {

                $d = [];

                if (static::$SKELETON) {
                    $d =& $this->P;
                } elseif (!empty($this->P)) {
                    $d =& $this->P;
                } elseif (static::$SOURCE == 'database') {
                    $A =& $this->A;
                    $o =  isset($A['db_sql_name']) ?
                        ['sql_name' => $A['db_sql_name']] : [];
                    $d =& $this->db_get($o);
                } else { throw new \Exception(); }

                return $d;
            }

            final private function &db_get(array $a) {

                $sql_name = isset($a['sql_name']) ? $a['sql_name'] : null;

                $A   =& $this->A;
                $and =  [];
                $sql =  '';

                if (!isset($sql_name)) {
                    $sql = sprintf(static::$DB_SQL_GET, static::$DB_TNAME);
                } elseif (isset(static::$DB_SQLS_GET[$sql_name])) {
                    $sql = static::$DB_SQLS_GET[$sql_name];
                } else { throw new \Exception(); }

                if ($A['key_type'] == 'primary') {
                    foreach (static::db_pkey() as $key) {
                        $name  = static::$DB_TNAME . ".$key";
                        $value = $this->XKEYS[$key];
                        $and[] = [$name => ['=' => $value]];
                    }
                } elseif ($A['key_type'] == 'unique') {
                    foreach (static::$DB_UKEYS[$A['key_name']] as $key) {
                        $name  = static::$DB_TNAME . ".$key";
                        $value = $this->XKEYS[$key];
                        $and[] = [$name => ['=' => $value]];
                    }
                } else { throw new \Exception(); }

                $dbh = $this->dbh_slave();
                $o   = [
                    'where' => ['and' => &$and],
                    'dbh'   => &$dbh
                ];

                $r    = static::db_select($sql, $o);
                $rows =& $r[0];
                $res  =& $r[1];

                if (!$res['status']) {
                    $this->HAS_ERROR =  true;
                    $this->EXISTS    =  null;
                    $d               =  null;
                } elseif (!$res['count']) {
                    $this->HAS_ERROR =  false;
                    $this->EXISTS    =  false;
                    $d               =  null;
                } else {
                    $this->HAS_ERROR =  false;
                    $this->EXISTS    =  true;
                    $this->P         =& $rows[0];
                    $d               =& $rows[0];
                }

                return $d;
            }

            final public function &set($p = array(), $a = array()) {

                $r = array(
                    'status'  => false,
                    'message' => null
                );

                if (empty($p))       { goto FAILURE; }
                if (!$this->is_ok()) { goto FAILURE; }

                $method = 'set_' . static::$SOURCE;
                $res    = &self::$method($p, $a);

                if ($res['status']) {
                    foreach ($p as $key => $value) {
                        $this->P[$key] = $value;
                    }
                    goto SUCCESS;
                } else {
                    goto FAILURE;
                }

                SUCCESS :
                $r['status'] = true;
                goto LAST;

                FAILURE :
                $r['status'] = false;
                goto LAST;

                LAST :
                return $r;
            }

            final private function &set_database(array $p, array $a) {

                $dbh = isset($a['dbh']) ? $a['dbh'] : $this->dbh_master();

                $params = array();
                $set    = array();
                $where  = array();

                foreach ($p as $key => $value) {
                    $set[]    = "$key = ?";
                    $params[] = $value;
                }

                foreach ($this->XKEYS as $key => $value) {
                    $where[]  = "$key = ?";
                    $params[] = $value;
                }

                $sql = sprintf(
                    'UPDATE %s SET %s WHERE %s',
                    static::$DB_TNAME,
                    implode(',',     $set),
                    implode(' AND ', $where)
                );

                return Database::update(array(), array(
                    'sql'    => $sql,
                    'params' => $params,
                    'dbh'    => $dbh
                ));
            }

            final public function &del($a = array()) {

                $r = array(
                    'status'  => null,
                    'message' => null,
                    'class'   => __CLASS__,
                    'line'    => null
                );

                if (!$this->exists()) {
                    $r['message'] = 'Unit does not exist.';
                    $r['line']    = __LINE__;
                    goto FAILURE;
                }

                $method = 'del_' . static::$SOURCE;
                $res    = &self::$method($a);

                if ($res['status']) {
                    $this->P      = array();
                    $this->EXISTS = false;
                    $r['line']    = __LINE__;
                    goto SUCCESS;
                } else {
                    $r['line'] = __LINE__;
                    goto FAILURE;
                }

                SUCCESS :
                $r['status'] = true;
                goto LAST;

                FAILURE :
                $r['status'] = false;
                goto LAST;

                LAST :
                return $r;
            }

            final private function &del_database(array $a) {

                $dbh = isset($a['dbh']) ? $a['dbh'] : $this->dbh_master();

                $params = array();
                $where  = array();

                foreach ($this->XKEYS as $key => $value) {
                    $where[]  = "$key = ?";
                    $params[] = $value;
                }

                $sql = sprintf(
                    'DELETE FROM %s WHERE %s',
                    static::$DB_TNAME,
                    implode(' AND ', $where)
                );

                return Database::remove(array(), array(
                    'sql'    => $sql,
                    'params' => $params,
                    'dbh'    => $dbh
                ));
            }

            public function __construct(
                array $k = array(), array $a = array()) {
                $this->XKEYS = $k;
                parent::__construct($a);
            }

            protected function init() {

                $A =& $this->A;
                $P =& $this->P;
                $K =& $this->XKEYS;

                if (static::$SKELETON) {
                    $P                 = $K;
                    $this->EXISTS    = true;
                    $this->HAS_ERROR = false;
                } elseif (static::$SOURCE === 'database') {
                    if (!isset($A['key_type'])) {
                        $A['key_type'] = self::$DB_KTYPE;
                    }
                } else { throw new \Exception(); }

                $this->get();
            }

            final public function __get($name) {
                return array_key_exists($name, $this->P) ? $this->P[$name] : null;
            }

            final public static function add($p = array(), $a = array()) {
                if (static::$SOURCE === 'database') {
                    return self::add_by_db($p, $a);
                } else { throw new \Exception(); }
            }

            final public static function sub($p = array(), $a = array()) {
                if (static::$SOURCE === 'database') {
                    return self::sub_database($p, $a);
                } else { throw new \Exception(); }
            }

            final private static function sub_database(array $p, array $a) {

                $dbh = isset($a['dbh']) ? $a['dbh'] : null;

                $DB_CNAME = isset(static::$DB_CNAME) ? static::$DB_CNAME : static::$DB_CNAME_DEFAULT;

                if (!isset($dbh)) {
                    $Database = static::Database();
                    $dbh =& $Database->dbh(array(
                        'cname' => $DB_CNAME,
                        'rtype' => 'master'
                    ));
                }

                $sql_set = array();
                $params  = array();

                foreach ($p as $k => $v) {
                    $sql_set[] = "$k = ?";
                    $params[]  = $v;
                }

                $sql = sprintf(
                    'REPLACE %s SET %s',
                    static::$DB_TNAME,
                    implode(', ', $sql_set)
                );

                return Database::replace(array(), array(
                    'sql'    => $sql,
                    'params' => $params,
                    'dbh'    => $dbh
                ));
            }

            final private static function add_by_db(array $p, array $a) {

                $dbh = isset($a['dbh']) ? $a['dbh'] : null;

                $DB_CNAME = isset(static::$DB_CNAME) ? static::$DB_CNAME : static::$DB_CNAME_DEFAULT;

                if (!isset($dbh)) {
                    $Database = static::Database();
                    $dbh = $Database->dbh(array(
                        'cname' => $DB_CNAME,
                        'rtype' => 'master'
                    ));
                }

                $sql_set = array();
                $params  = array();

                foreach ($p as $k => $v) {
                    $sql_set[] = "$k = ?";
                    $params[]  = $v;
                }

                $sql = sprintf(
                    'INSERT %s SET %s',
                    static::$DB_TNAME,
                    implode(', ', $sql_set)
                );

                return Database::insert(array(), array(
                    'sql'    => $sql,
                    'params' => $params,
                    'dbh'    => $dbh
                ));
            }

            final public function error() {}
        }
    }

?>
