<?php

    namespace Vintage\T\Web {

        trait Page {

            private static $DATA_DEFAULT     = ['head' => [], 'data' => []];
            private static $ITEM_P_DEFAULT   = 20;
            private static $PAGE_C_DEFAULT   = 1;
            private static $NO_LIMIT_DEFAULT = false;

            protected function &data() {

                $Q =& $this->Q;
                $S =& $this->S;

                $r =  [];
                $p =  self::$DATA_DEFAULT;
                $h =& $p['head'];
                $d =& $p['data'];

                try {

                    if (!empty(static::$RECORD)) {

                        $RECORD =& static::$RECORD;

                        if ($RECORD['TYPE'] == 'unit') {
                        }
                        elseif ($RECORD['TYPE'] == 'more') {

                            $More =& new $RECORD['NAME']();

                            $r =& $More->get([
                                'where'       => $this->record_where(),
                                'order_by'    => $this->record_order_by(),
                                'limit'       => $this->record_limit(),
                                'db_sql_name' => 'set',
                                'return_res'  => true
                            ]);

                            if (!$r[1]['status']) {
                                $r         =& $r[1];
                                $h['line'] =  __LINE__;
                                goto NG;
                            }

                            $d['more']  = $r[0];
                            $d['list']  = $r[0];
                            $d['total'] = $r[1]['total'];
                            unset($r);
                        }
                    }

                    $h['line'] = __LINE__;
                    goto OK;

                    NG :
                    $h['status'] = false;
                    goto FIN;

                    OK :
                    $h['message'] = 'Created.';
                    $h['status']  = true;
                    goto FIN;

                    FIN :
                }
                catch (\Exception $e) {

                    $h['message'] = 'Exception, ' . $e->getMessage();
                    $h['line']    = __LINE__;
                    $h['status']  = false;
                }
                finally {

                    if (!empty($r)) $h['message'] =  $r['message'];
                    if (!empty($r)) $h['trace']   =& $r['trace'];

                    $t            =  [];
                    $t['class']   =  __CLASS__;
                    $t['method']  =  __METHOD__;
                    $t['line']    =  $h['line'];
                    $h['trace'][] =& $t;
                }

                return $p;
            }

            final protected function &record_where($logic = null, $label = null) {

                $where = null;

                if (!empty(static::$RECORD)) {
                    if (!empty(static::$RECORD['WHERE'])) {

                        $Q      =& $this->Q;
                        $RECORD =& static::$RECORD['WHERE'];
                        $where  =  [];

                        if (isset($logic)) {

                            $logic = strtolower($logic);
                            $LOGIC = strtoupper($logic);

                            if (!empty($RECORD[$LOGIC])) {

                                $confs =& $RECORD[$LOGIC];
                                if (isset($label)) $confs =& $confs[$label];

                                foreach ($confs as &$conf) {
                                    if (isset($Q[$conf[2]])) {
                                        $where[] = [
                                            $conf[0] => [$conf[1] => $Q[$conf[2]]]
                                        ];
                                        unset($conf);
                                    }
                                }
                            }
                        }
                        else {
                            $and          =& $this->record_where('and', $label);
                            $or           =& $this->record_where('or',  $label);
                            $where['and'] =& $and;
                            $where['or']  =& $or;
                        }
                    }
                }

                return $where;
            }

            final protected function &record_order_by() {

                $order_by = null;

                if (!empty(static::$RECORD)) {
                    if (!empty(static::$RECORD['ORDER_BY'])) {

                        $Q        =& $this->Q;
                        $confs    =& static::$RECORD['ORDER_BY'];
                        $order_by =  [];

                        foreach ($confs as &$conf) {
                            $order_by[] = [$conf[0] => $conf[1]];
                            unset($conf);
                        }
                    }
                }

                return $order_by;
            }

            final protected function &record_limit($suffix = '') {

                $Q     =& $this->Q;
                $limit =  null;

                $k        = preg_replace('/_$/', '', "no_limit_$suffix");
                $no_limit = isset($Q[$k]) ? $Q[$k] : self::$NO_LIMIT_DEFAULT;

                if (!$no_limit) {
                    $k      = preg_replace('/_$/', '', "item_p_$suffix");
                    $item_p = isset($Q[$k]) ? $Q[$k] : self::$ITEM_P_DEFAULT;
                    $k      = preg_replace('/_$/', '', "page_c_$suffix");
                    $page_c = isset($Q[$k]) ? $Q[$k] : self::$PAGE_C_DEFAULT;
                    $limit  = [(($page_c - 1) * $item_p), $item_p];
                }

                return $limit;
            }
        }
    }

?>
