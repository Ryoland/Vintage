<?php

    namespace Vintage\C\Data {

        final class Database extends \Vintage\A\Lib {

            protected static $DSNS = array();
            protected static $LOGS = array();

            final public static function connect(array $a) {

                $dsn = '%s:host=%s;dbname=%s';
                $dsn = sprintf($dsn, $a['type'], $a['host'], $a['name']);
                $dbh = null;

                try {
                    $dbh = new \PDO($dsn, $a['user'], $a['pass']);
                    $dbh->query('SET NAMES utf8');
                } catch (\PDOException $e) {}

                return $dbh;
            }

            final public static function dbh(
                array $a1 = array(), array $a2 = array() ) {

                $dbh = null;

                if (isset($a2['dbh'])) {
                    if (self::ping($a2['dbh'])) {
                        $dbh = $a2['dbh'];
                    } else { throw new \Exception(); }
                }

                if (!isset($dbh)) {
                    if (!empty($a1)) {

                        $dsn = '%s:host=%s;dbname=%s';
                        $dsn = sprintf($dsn, $a1['type'], $a1['host'], $a1['name']);

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

            final public static function ping(\PDO $dbh) {

                try {
                    $dbh->query('SELECT 1');
                } catch (\PDOException $e) {
                    return false;
                }

                return true;
            }

            final public static function &select(array $a1 = array(), array $a2) {

                $res    = &self::execute($a1, $a2);
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

            final public static function insert(array $a1 = array(), array $a2) {

                $res    = &self::execute($a1, $a2);
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

            final public static function &update(array $a1 = array(), array $a2) {
                $res = &self::execute($a1, $a2);
                return $res;
            }

            final public static function &remove(array $a1 = array(), array $a2) {
                $res = &self::execute($a1, $a2);
                return $res;
            }

            final public static function replace(array $a1 = array(), array $a2) {

                $res    = &self::execute($a1, $a2);
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

            //================================================================

            /****/
            final private static function &execute(array $a1, array $a2) {

                // Argument(s) -----------------------------------------------

                $sql    = isset($a2['sql'])    ? $a2['sql']    : null;
                $params = isset($a2['params']) ? $a2['params'] : array();

                // Parameter(s) ----------------------------------------------

                $r = array(
                    'status'  => null,
                    'message' => null,
                    'dbh'     => null,
                    'sth'     => null,
                    'sql'     => $sql,
                    'params'  => $params,
                    'trace'   => array()
                );

                // Process(es) -----------------------------------------------

                try {

                    $dbh = &self::dbh($a1, $a2);
                    $sth = &$dbh->prepare($sql);

                    if ($sth->execute($params)) {
                        $r['message'] = 'Executed';
                        $r['line']    = __LINE__;
                        $r['dbh']     = &$dbh;
                        $r['sth']     = &$sth;
                        goto SUCCESS;
                    } else {
                        $errorInfo    = $sth->errorInfo();
                        $r['message'] = implode(', ', $errorInfo);
                        $r['line']    = __LINE__;
                        goto FAILURE;
                    }

                } catch (\Exception $e) {
                    $r['message'] = 'Exception, ' . $e->getMessage();
                    $r['line']    = __LINE__;
                    goto FAILURE;
                }

                // Result(s) -------------------------------------------------

                FAILURE :
                $r['status'] = false;
                goto LAST;

                SUCCESS :
                $r['status'] = true;
                goto LAST;

                LAST :
                return $r;
            }

            //================================================================
        }
    }

?>
