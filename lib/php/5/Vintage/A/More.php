<?php

    namespace Vintage\A {

        use \Vintage\C\PHP\VString as VString;

        abstract class More extends \Vintage\A\Record {

            protected static $DB_SQL_GET = ' SELECT SQL_CALC_FOUND_ROWS *         FROM %s ';
            protected static $DB_SQL_CNT = ' SELECT               COUNT(*) AS cnt FROM %s ';
            protected static $DB_SQL_SUM = ' SELECT                SUM(%s) AS sum FROM %s ';

            final private static function divide(
                $method, array $a = array()) {
                if (!VString::len(static::$SOURCE)) {
                    return null;
                } elseif (static::$SOURCE == 'database') {
                    return self::$method($a);
                } else { return null; }
            }

            final public function get(array $a = array()) {
                list($rows, $res) = self::divide('db_get', $a);
                if (isset($a['return_res']) && $a['return_res']) {
                    return array($rows, $res);
                } else {
                    return $res['status'] ? $rows : null;
                }
            }

            final public function cnt(array $a = array()) {
                return self::divide('db_cnt', $a);
            }

            final public function sum($column, array $a = array()) {
                $a['column'] = $column;
                return self::divide('db_sum', $a);
            }

            final private static function db_get(array $a = array()) {

                $sql_name = isset($a['db_sql_name']) ? $a['db_sql_name'] : null;

                if (isset($sql_name)) {
                    if (isset(static::$DB_SQLS_GET[$sql_name])) {
                        $sql = static::$DB_SQLS_GET[$sql_name];
                    }
                }

                if (!isset($sql)) {
                    $sql = sprintf(static::$DB_SQL_GET, static::$DB_TNAME);
                }

                $d =& static::db_select($sql, $a);

                return $d;
            }

            final private static function db_cnt(array $a = array()) {

                $sql_name = isset($a['db_sql_name']) ? $a['db_sql_name'] : null;

                if (isset($sql_name)) {
                    if (isset(static::$DB_SQLS_GET[$sql_name])) {
                        $sql = static::$DB_SQLS_CNT[$sql_name];
                    }
                }

                if (!isset($sql)) {
                    $sql = sprintf(static::$DB_SQL_CNT, static::$DB_TNAME);
                }

                $d =& static::db_select($sql, $a);

                return $d[1]['status'] ? $d[0][0]['cnt'] : null;
            }

            final private static function db_sum(array $a) {

                $column   = isset($a['column'])      ? $a['column']      : null;
                $sql_name = isset($a['db_sql_name']) ? $a['db_sql_name'] : null;

                $sum = null;

                if (isset($sql_name)) {
                    if (isset(static::$DB_SQLS_SUM[$sql_name])) {
                        $sql = static::$DB_SQLS_SUM[$sql_name];
                    }
                }

                if (!isset($sql)) {
                    $sql = sprintf(static::$DB_SQL_SUM, $column, static::$DB_TNAME);
                }

                list($rows, $r) = static::db_select($sql, $a);

                if ($r['status']) {
                    $sum = isset($rows[0]['sum']) ? $rows[0]['sum'] : 0;
                }

                return $sum;
            }
        }
    }

?>
