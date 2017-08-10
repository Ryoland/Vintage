<?php

    namespace Vintage\A {

        use \Vintage\C\Util\SQL as SQL;

        abstract class Record extends \Vintage\A\Lib\D {

            protected static $SOURCE = 'database';

            protected static $DB_KTYPE = 'primary';
            protected static $DB_PKEYS = array('id');

            private $dbh_master = null;
            private $dbh_slave  = null;
            private $use_master = null;

            final public function &dbh_master() {
                if (static::$SOURCE != 'database') return null;

$DB_CNAME = isset(static::$DB_CNAME) ? static::$DB_CNAME : static::$DB_CNAME_DEFAULT;

                if (!isset($this->dbh_master)) {
                    if (isset($this->A['dbh_master'])) {
                        $this->dbh_master = $this->A['dbh_master'];
                    } else {
                        $Database = static::Database();
                        $this->dbh_master = $Database->dbh(array(
                            'cname' => $DB_CNAME,
                            'rtype' => 'master'
                        ));
                    }
                }
                return $this->dbh_master;
            }

            final public function &dbh_master_forced() {
                $dbh_master = null;
                if ($this->use_master()) {
                    $dbh_master =& $this->dbh_master();
                }
                return $dbh_master;
            }

            final public function &dbh_slave() {
                if (static::$SOURCE != 'database') return null;
                if ($this->use_master()) return $this->dbh_master();

$DB_CNAME = isset(static::$DB_CNAME) ? static::$DB_CNAME : static::$DB_CNAME_DEFAULT;

                if (!isset($this->dbh_slave)) {
                    if (isset($this->A['dbh_slave'])) {
                        $this->dbh_slave = $this->A['dbh_slave'];
                    } else {
                        $Database = static::Database();
                        $this->dbh_slave = $Database->dbh(array(
                            'cname' => $DB_CNAME,
                            'rtype' => 'slave'
                        ));
                    }
                }
                $dbh_slave = $this->dbh_slave;
                return $dbh_slave;
            }

            final public function use_master($use_master = null) {
                if (static::$SOURCE != 'database') return null;

                if (isset($use_master)) {
                    $this->use_master = $use_master;
                }
                if (!isset($use_master)) {
                    $this->use_master = isset($this->A['use_master']) ?
                        $this->A['use_master'] : false;
                }
                return $this->use_master;
            }

            final protected static function &db_select($sql, array $a = []) {

                $dbh = isset($a['dbh']) ? $a['dbh'] : null;

                $d = [[], ['status' => false]];

                $Database =& static::Database();

                if (isset($Database)) {

$params = isset($a['params']) ? $a['params'] : [];

                    if (isset($a['where'])) {
                        $res    = SQL::where($a['where']);
                        $sql   .= $res[0];
                        $params = array_merge($params, $res[1]);
                    }

                    if (isset($a['group_by'])) {
                        $sql .= SQL::group_by($a['group_by']);
                    }

                    if (isset($a['order_by'])) {
                        $sql .= SQL::order_by($a['order_by']);
                    }

                    if (isset($a['limit'])) {
                        $sql .= SQL::limit($a['limit']);
                    }

$DB_CNAME = isset(static::$DB_CNAME) ? static::$DB_CNAME : static::$DB_CNAME_DEFAULT;

                    $d =& $Database->select([
                        'cname'  => $DB_CNAME,
                        'rtype'  => 'slave',
                        'sql'    => $sql,
                        'params' => $params,
                        'dbh'    => $dbh
                    ]);
                }

                return $d;
            }
        }
    }

?>
