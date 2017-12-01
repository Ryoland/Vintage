<?php

  namespace Vintage\C\Data {

    final class Database extends \Vintage\A\Lib {

      protected static $DSNS = array();
      protected static $LOGS = array();









//===

      private static $dsn_fmt = '%s:host=%s;dbname=%s;port=%d;';

      private static $type = 'mysql';
      private static $host = 'localhost';
      private static $user = 'root';
      private static $pass = null;

      private static $port = [
        'mysql' => 3306
      ];









      public static function connect(array $a) {

        $dbh = null;

        try {
          $dbh = new \PDO(self::dsn($a), $a['user'], $a['pass']);
          $dbh->query('SET NAMES utf8');
        } catch (\PDOException $e) {}

        return $dbh;
      }

      public static function dbh(array $a1 = array(), array $a2 = array() ) {

        $dbh = null;

        if (isset($a2['dbh'])) {
          if (self::ping($a2['dbh'])) {
            $dbh = $a2['dbh'];
          } else { throw new \Exception(); }
        }

        if (!isset($dbh)) {
          if (!empty($a1)) {

            $dsn = self::dsn($a1);

            if (isset(self::$DSNS[$dsn])) {
              if (self::ping(self::$DSNS[$dsn])) {
                $dbh = self::$DSNS[$dsn];
              } else { unset(self::$DSNS[$dsn]); }
            }

            if (!isset($dbh)) {
              $dbh = self::$DSNS[$dsn] = self::connect($a1);
            }

          } else { throw new \Exception(); }
        }

        return $dbh;
      }

      public static function ping(\PDO $dbh) {

        try {
          $dbh->query('SELECT 1');
        } catch (\PDOException $e) {
          return false;
        }

        return true;
      }

      public static function select(array $a1 = array(), array $a2) {

        $res    = self::execute($a1, $a2);
        $dbh    = $res['dbh'];
        $sth    = $res['sth'];
        $status = $res['status'];

        $sql  = $a2['sql'];
        $rows = array();

        $r = array(
          'status' => $status,
          'message' => $res['message'],
          'total'  => null,
          'trace'  => array()
        );

        if ($status) {

          $regex = '/\ssql_calc_found_rows\s/i';

          if (preg_match($regex, $sql)) {

            try {
              $sth2 = $dbh->query('SELECT FOUND_ROWS()');
            } catch (\PDOException $e) {}

            if ($sth2) {
              $row2 = $sth2->fetch(\PDO::FETCH_NUM);
              $r['total'] = intval($row2[0]);
            }
          }

          while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = $row;
          }

          $r['count'] = $sth->rowCount();
        }

        if (!empty($res)) unset($res['trace']);
        if (!empty($res)) $r['trace'][] = &$res;

        $data = array($rows, $r);
        return $data;
      }

      public static function insert(array $a1 = array(), array $a2) {

        $res    = self::execute($a1, $a2);
        $dbh    = $res['dbh'];
        $sth    = $res['sth'];
        $status = $res['status'];

        $r = array(
          'status'         => $status,
          'message'        => $res['message'],
          'last_insert_id' => null,
          'trace'          => array()
        );

        if ($status) {

          try {
            $sth2 = $dbh->query('SELECT LAST_INSERT_ID()');
          } catch (\PDOException $e) {}

          if ($sth2) {
            $row2 = $sth2->fetch(\PDO::FETCH_NUM);
            $r['last_insert_id'] = $row2[0];
          }
        }

        if (!empty($res)) unset($res['trace']);
        if (!empty($res)) $r['trace'][] = &$res;

        return $r;
      }

      public static function update(array $a1 = array(), array $a2) {
        $res = self::execute($a1, $a2);
        return $res;
      }

      public static function remove(array $a1 = array(), array $a2) {
        $res = self::execute($a1, $a2);
        return $res;
      }

      public static function replace(array $a1 = array(), array $a2) {

        $res    = self::execute($a1, $a2);
        $dbh    = $res['dbh'];
        $sth    = $res['sth'];
        $status = $res['status'];

        $r = array(
          'status'         => $status,
          'message'        => $res['message'],
          'last_insert_id' => null,
          'trace'          => array()
        );

        if ($status) {

          try {
            $sth2 = $dbh->query('SELECT LAST_INSERT_ID()');
          } catch (\PDOException $e) {}

          if ($sth2) {
            $row2 = $sth2->fetch(\PDO::FETCH_NUM);
            $r['last_insert_id'] = $row2[0];
          }
        }

        if (!empty($res)) unset($res['trace']);
        if (!empty($res)) $r['trace'][] = &$res;

        return $r;
      }

      /****/
      private static function execute(array $a1, array $a2) {

        $sql    = isset($a2['sql'])    ? $a2['sql']    : null;
        $params = isset($a2['params']) ? $a2['params'] : array();

        $x = array(
          'dbh'     => null,
          'sth'     => null,
          'sql'     => $sql,
          'params'  => $params
        );

        try {

          $dbh = self::dbh($a1, $a2);
          $sth = $dbh->prepare($sql);

          if ($sth->execute($params)) {
            $x['dbh'] = $dbh;
            $x['sth'] = $sth;
          }
          else {
            $errorInfo    = $sth->errorInfo();
            $x['message'] = implode(', ', $errorInfo);
            $x['line']    = __LINE__;
            goto NG;
          }

        }
        catch (\Exception $e) {
          $x['message'] = $e->getMessage();
          $x['line']    = __LINE__;
          goto NG;
        }




#
        OK :
        $x['message'] = 'ok';
        $x['status']  = true;
        goto FIN;

        NG :
        $x['status']  = false;
        $x['class']   = __CLASS__;
        $x['method']  = __METHOD__;
        $x['trace']   = [$x];
        goto FIN;

        FIN : return $x;
      }




      /**
       * DSN
       *
       * @param  array   $a         [!]
       * @param  integer $a['port'] [=]
       * @param  string  $a['host'] [=]
       * @param  string  $a['name'] [!]
       * @param  string  $a['type'] [=]
       * @return string  $dsn
       */
      private static function dsn(array $a) {

        $host = @$a['host'] ?: self::$host;
        $type = @$a['type'] ?: self::$type;
        $port = @$a['port'] ?: self::$port[$type];

        return sprintf(self::$dsn_fmt, $type, $host, $a['name'], $port);
      }
    }
  }

?>
