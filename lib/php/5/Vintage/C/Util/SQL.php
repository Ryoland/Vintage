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

                $sql    = ' ';
                $params = ['and'=>[],'or'=>[]];
                $where  = ['and'=>[],'or'=>[]];

                if (!empty($p)) {

                    foreach ($p as $logic => $sets) {
                        foreach ($sets as $set) {
                            foreach ($set as $name => $values) {
                                foreach ($values as $operator => $value) {
                                    switch ($operator) {
                                        case 'partial' :
                                            $where[$logic][]  = " $name LIKE ? ";
                                            $params[$logic][] = "%$value%"; break;
                                        case 'left' :
                                            $where[$logic][]  = " $name LIKE ? ";
                                            $params[$logic][] = "$value%"; break;
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
                                            $where[$logic][]  = " $name $operator ? ";
                                            $params[$logic][] = $value; break;
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

                    if (!empty($where['and'])) {
                        $sql = ' WHERE ' . implode(' AND ', $where['and']);
                    }
                }

                return [$sql, $params['and']];
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
