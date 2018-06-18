<?php

  namespace Vintage\A {

    use \Vintage\C\PHP\VString as VString;

    abstract class More extends \Vintage\A\Record {

      protected static $DB_SQL_GET = ' SELECT SQL_CALC_FOUND_ROWS *         FROM %s ';
      protected static $DB_SQL_CNT = ' SELECT               COUNT(*) AS cnt FROM %s ';
      protected static $DB_SQL_SUM = ' SELECT                SUM(%s) AS sum FROM %s ';

      final public function get(array $a = array()) {




// ##

        $a['dbh'] = @$a['dbh'] ?: $this->dbh_slave();




        list($rows, $r) = self::divide('db_get', $a);

        if (isset($a['return_res']) && $a['return_res']) {
          return array($rows, $r);
        }
        else {
          return $r['status'] ? $rows : null;
        }
      }




// ##

      /****/
      final public function cnt(array $a = []) {
        $a['dbh'] = @$a['dbh'] ?: $this->dbh_slave();
        return self::divide('db_cnt', $a);
      }

      /****/
      final public function sum($column, array $a = []) {
        $a['column'] = $column;
        $a['dbh']    = @$a['dbh'] ?: $this->dbh_slave();
        return self::divide('db_sum', $a);
      }




      final private static function divide($method, array $a = array()) {
        if (!VString::len(static::$SOURCE)) {
          return null;
        }
        elseif (static::$SOURCE == 'database') {
          return self::$method($a);
        }
        else {
          return null;
        }
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




// ##

        $db_sql_name = @$a['db_sql_name'] ?: null;
        $limit       = @$a['limit']       ?: [];
        $page        = @$a['page']        ?: [];

        // Limit
        $limit = (array) $limit;
        $from  = null;
        $span  = null;

        if (!empty($limit)) {
          switch (count($limit)) {
            case 1  : $from = (integer) 0;
                      $span = (integer) $limit[0];
                      break;
            default : $from = (integer) $limit[0];
                      $span = (integer) $limit[1];
                      break;
          }
        }
        ///

        // Page
        if (!empty($page)) {

          $item_t = @$page['item_t'] ?: 0;
          $page_m = @$page['page_m'] ?: 'next';
          $page_l = @$page['page_l'] ?: 1;
          $page_r = @$page['page_r'] ?: 10;

          $rx = '/\sSQL_CALC_FOUND_ROWS\s/i';

          if ($page_m == 'hybrid') {
            if ($page_l < $page_r) {
              $page_m = 'next';
            }
            elseif ($page_l > $page_r) {
              $sql = preg_replace($rx, ' ', $sql);
            }
          }

          if ($page_m == 'next') {

            $sql = preg_replace($rx, ' ', $sql);

            $a['limit'] = [$from, $span + 1];
            $a['span']  = $span;

            list($records, $r) = static::db_select($sql, $a);

            if ($r['status']) {

              $count   = $r['count'];
              $hs_next = $r['hs_next'];

              $total  = $from + $count;
              $total += $hs_next ? 1 : 0;

              if ($count >= $span) {
                $r['total'] = max($total, $item_t);
              }
              else {
                $r['total'] = $total;
              }
            }

            return [$records, $r];
          }
          else {
            return static::db_select($sql, $a);
          }
        }
        ///

        return static::db_select($sql, $a);
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

        $d = static::db_select($sql, $a);

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
