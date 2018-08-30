<?php

    namespace Vintage\C\Util {

        use \Vintage\C\PHP\VArray as VArray;

        final class SQL extends \Vintage\A\Lib\S {

            final public static function group_by($a = []) {

                $a   = VArray::is($a) ? $a : [$a];
                $sql = ' ';

                if (!empty($a)) {
                    $sql = ' GROUP BY ' . implode(', ', $a);
                }

                return $sql;
            }

            final public static function order_by(array $a = []) {

                $sql      = ' ';
                $order_by = [];

                if (!empty($a)) {
                    foreach ($a as $pair) {
                        foreach ($pair as $key => $value) {
                            $order_by[] = " $key $value ";
                        }
                    }
                    $sql = ' ORDER BY ' . implode(', ', $order_by);
                }

                return $sql;
            }

            final public static function where(array $p = []) {

$a = $p;
                $sql    = ' ';
                $params = ['and'=>[],'or'=>[],'ors'=>[]];
                $where  = ['and'=>[],'or'=>[],'ors'=>[]];

                if (!empty($p)) {

                    foreach ($p as $logic => $sets) {
                        foreach ($sets as $set) {
                            foreach ($set as $name => $values) {
                                foreach ($values as $operator => $value) {
                                    switch ($operator) {
                                        case '~' :
                                            $where[$logic][]  = " $name LIKE ? ";
                                            $params[$logic][] = "%$value%"; break;
                                        case 'partial' :
                                            $where[$logic][]  = " $name LIKE ? ";
                                            $params[$logic][] = "%$value%"; break;
                                        case '^' :
                                        case 'left' :
                                            $where[$logic][]  = " $name LIKE ? ";
                                            $params[$logic][] = "$value%"; break;
                                        case '$' :
                                        case 'right' :
                                            $where[$logic][]  = " $name LIKE ? ";
                                            $params[$logic][] = "%$value"; break;
                                        case '=' :
                                            if (VArray::is($value)) {
                                                $questions = [];
                                                foreach ($value as $buffer) {
                                                    $questions[]      = '?';
                                                    $params[$logic][] = $buffer;
                                                }
                                                $where[$logic][] = sprintf(
                                                    " $name in (%s) ",
                                                    implode(',', $questions)
                                                );
                                            } else {
                                                $where[$logic][]  = " $name $operator ? ";
                                                $params[$logic][] = $value;
                                            }
                                            break;
                                        default :

                                            $r = self::where_single([
                                              'column'   => $name,
                                              'operator' => $operator,
                                              'value'    => $value
                                            ]);

                                            $where[$logic][] = $r[0];
                                            $params[$logic]  = array_merge($params[$logic], $r[1]);
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($where['or'])) {
                        $where['and'][] = sprintf(
                            '(%s)', implode(' OR ', $where['or']));
                        $params['and'] = array_merge(
                            $params['and'], $params['or']);
                    }

$sqls = $where['and'];
$params = $params['and'];




// ##

          if (isset($a['ors'])) {
            foreach ($a['ors'] as $or) {

              $tmps = [];

              foreach ($or as $set) {
                foreach ($set as $column => $values) {
                  foreach ($values as $operator => $value) {

                    $r = self::where_single([
                      'column'   => $column,
                      'operator' => $operator,
                      'value'    => $value
                    ]);

                    $tmps[] = sprintf('(%s)', $r[0]);
                    $params = array_merge($params, $r[1]);
                  }
                }
              }

              $sqls[] = sprintf('(%s)', implode(' OR ', $tmps));
            }
          }
        }

        if (!empty($sqls)) {
          $sql = ' WHERE ' . implode(' AND ', $sqls);
        }

        return [$sql, $params];
      }

      /**
       * Where | Single
       *
       * @param  array  $a             [!]
       * @param  string $a['column']   [!]
       * @param  string $a['operator'] [!]
       * @param  mixed  $a['value']    [!]
       * @return string $sql
       * @return array  $params
       */
      private static function where_single(array $a) {

        $column   = @$a['column']   ?: null;
        $operator = @$a['operator'] ?: null;

        $value = isset($a['value']) ? $a['value'] : null;

        $sql    = null;
        $params = null;

        if (in_array($operator, ['=', '!='])) {
          if (is_array($value)) {
            $count     = count($value);
            $questions = implode(',', array_pad([], $count, '?'));
            $operator2 = ($operator == '=') ? 'IN' : 'NOT IN';
            $sql       = sprintf("$column $operator2 (%s)", $questions);
            $params    = $value;
          }
          else {
            $sql    = "$column $operator ?";
            $params = [$value];
          }
        }
        elseif (in_array($operator, ['~', 'partial'])) {
          $sql    = "$column LIKE ?";
          $params = ["%$value%"];
        }
        elseif (in_array($operator, ['^', 'left'])) {
          $sql    = "$column LIKE ?";
          $params = ["$value%"];
        }
        elseif (in_array($operator, ['$', 'right'])) {
          $sql    = "$column LIKE ?";
          $params = ["%$value"];
        }
        else {
          $sql    = "$column $operator ?";
          $params = [$value];
        }

        return [$sql, $params];
      }




      final public static function limit($a = []) {

        $a   = VArray::is($a) ? $a : [$a];
        $sql = ' ';

        if (!empty($a)) {
          $sql = ' LIMIT ' . implode(', ', $a);
        }

        return $sql;
      }
    }
  }

?>
