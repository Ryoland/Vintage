<?php

  namespace Vintage\C\Util {

    use \Vintage\C\PHP\VArray as VArray;

    final class SQL extends \Vintage\A\Lib\S {


/**/

      private static $combinationz = [
        'and_and' => ['sort' => 0],
        'and_or'  => ['sort' => 1],
        'or_and'  => ['sort' => 2],
        'or_or'   => ['sort' => 3]
      ];

      private static $combinations = [];

      private static function combinations():array {

        if (empty(self::$combinations)) {

          // $sorts
          $sorts = [];

          foreach (self::$combinationz as $k => $combination) {
            $sorts[] = $combination['sort'];
          }

          $sorts = array_unique($sorts);
          $sorts = array_values($sorts);

          sort($sorts);
          ///

          // self::$combinations
          $combinations = [];

          foreach ($sorts as $sort) {
            foreach (self::$combinationz as $combination_name => $combination) {
              if ($combination['sort'] == $sort) {
                $combination['name'] = $combination_name;
                $combinations[]      = $combination;
              }
            }
          }

          self::$combinations = $combinations;
          ///
        }

        return self::$combinations;
      }

/**/


      public static function group_by($a = []) {

        $a   = VArray::is($a) ? $a : [$a];
        $sql = ' ';

        if (!empty($a)) {
          $sql = ' GROUP BY ' . implode(', ', $a);
        }

        return $sql;
      }

      public static function order_by(array $a = []) {

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

      public static function where(array $a = []) {

        $sql    = ' ';
        $params = ['and'=>[],'or'=>[],'ors'=>[]];
        $where  = ['and'=>[],'or'=>[],'ors'=>[]];

        foreach ($a as $logic => $sets) {
          if (in_array($logic, ['and', 'or', 'ors'])) {
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
                    case '^'    :
                    case 'left' :
                      $where[$logic][]  = " $name LIKE ? ";
                      $params[$logic][] = "$value%"; break;
                    case '$'     :
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

                        $where[$logic][] = sprintf(" $name in (%s) ", implode(',', $questions));
                      }
                      else {
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
        }

        if (!empty($where['or'])) {
          $where['and'][] = sprintf('(%s)', implode(' OR ', $where['or']));
          $params['and']  = array_merge($params['and'], $params['or']);
        }

        $sqls   = $where['and'];
        $params = $params['and'];

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


/**/

        if (isset($a['and_1']) && is_array($a['and_1']) && !empty($a['and_1'])) {
          foreach ($a['and_1'] as $set) {
            foreach ($set as $column => $values) {
              foreach ($values as $operator => $value) {

                $r = self::where_single([
                  'column'   => $column,
                  'operator' => $operator,
                  'value'    => $value
                ]);

                $sqls[] = '(' . $r[0] . ')';
                $params = array_merge($params, $r[1]);
              }
            }
          }
        }

        foreach (self::combinations() as $combination) {

          $combination_name = $combination['name'];
          $groups           = $a[$combination_name] ?? [];

          if (is_array($groups) && !empty($groups)) {

            list($condition_1, $condition_2) = explode('_', $combination_name);

            foreach ($groups as $group) {

              $sqls_t_1 = [];

              foreach ($group as $sets) {

                $sqls_t_2 = [];

                foreach ($sets as $set) {
                  foreach ($set as $column => $values) {
                    foreach ($values as $operator => $value) {

                      $r = self::where_single([
                        'column'   => $column,
                        'operator' => $operator,
                        'value'    => $value
                      ]);

                      $sqls_t_2[] = '(' . $r[0] . ')';
                      $params     = array_merge($params, $r[1]);
                    }
                  }
                }

                if (!empty($sqls_t_2)) {
                  $glue       = strtoupper(" $condition_1 ");
                  $sqls_t_1[] = '(' . implode($glue, $sqls_t_2) . ')';
                }
              }

              if (!empty($sqls_t_1)) {
                $glue   = strtoupper(" $condition_2 ");
                $sqls[] = '(' . implode($glue, $sqls_t_1) . ')';
              }
            }
          }
        }

        if (isset($a['and_2']) && is_array($a['and_2']) && !empty($a['and_2'])) {
          foreach ($a['and_2'] as $set) {
            foreach ($set as $column => $values) {
              foreach ($values as $operator => $value) {

                $r = self::where_single([
                  'column'   => $column,
                  'operator' => $operator,
                  'value'    => $value
                ]);

                $sqls[] = '(' . $r[0] . ')';
                $params = array_merge($params, $r[1]);
              }
            }
          }
        }

        if (!empty($sqls)) {
          $sql = ' WHERE ' . implode(' AND ', $sqls);
        }

        return [$sql, $params];
      }

/**/


      /**
       * Filter
       *
       * @param  array  $filter [=]
       * @return string $sql
       * @return array  $params
       */
      public static function filter(array $filters = []) {

        $sql    = '';
        $params = [];
        $sqls   = [];

        foreach ($filters as $logics => $sets) {
          if (!empty($sets)) {
            list($sql_, $params_) = self::filter_loop($logics, $sets);
            $sqls[] = $sql_;
            $params = array_merge($params, $params_);
          }
        }

        if (!empty($sqls)) {
          $sql = ' WHERE ' . implode(' AND ', $sqls);
        }

        return [$sql, $params];
      }

      /**
       * Filter | Loop
       *
       * @param  array  $filters [=]
       * @return string $sql
       * @return array  $params
       */
      private static function filter_loop($logics, array $sets) {

        $logics = explode('_', $logics);
        $sql    = '';
        $params = [];

        if (count($logics) == 1) {

          $logic = $logics[0];
          $tmps  = [];

          foreach ($sets as $set) {
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

          $sql = sprintf('(%s)', implode(" $logic ", $tmps));
          $sql = "($sql)";
        }
        else {

          $logic  = array_shift($logics);
          $logics = implode('_', $logics);

          $sqls = [];

          foreach ($sets as $set) {
            list($sql_, $params_) = self::filter_loop($logics, $set);
            $sqls[] = $sql_;
            $params = array_merge($params, $params_);
          }

          $sql = implode(" $logic ", $sqls);
          $sql = "($sql)";
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




////
        $NOT = preg_match('/^\!/', $operator) ? 'NOT' : '';
////




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




////
        else if (in_array($operator, ['~', '!~'], true)) {
          $sql    = "$column $NOT LIKE ?";
          $params = ["%$value%"];
        }
        else if (in_array($operator, ['^', '!^'], true)) {
          $sql    = "$column $NOT LIKE ?";
          $params = ["$value%"];
        }
        else if (in_array($operator, ['$', '!$'], true)) {
          $sql    = "$column $NOT LIKE ?";
          $params = ["%$value"];
        }
        else if (in_array($operator, ['|', '!|'], true)) {
          $sql    = "$column $NOT BETWEEN ? AND ?";
          $params = [$value[0], $value[1]];
        }
////




        else {
          $sql    = "$column $operator ?";
          $params = [$value];
        }

        return [$sql, $params];
      }

      public static function limit($a = []) {

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
