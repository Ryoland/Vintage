<?php

    namespace Vintage\C\Data {

        use \Vintage\C\Util\Env as Env;

        final class Config extends \Vintage\A\Lib {

            private $data   = array();
            private $config = array(
                'head' => array(),
                'data' => array()
            );

            final protected function proc() {

                $A        = $this->A;
                $projects = isset($A['projects']) ? $A['projects'] : array();

                $config = &$this->config;

                foreach ($projects as $project) {

                    $fp = sprintf(
                        '%s/pro/%s/etc/conf.json',
                        Env::root(),
                        $project
                    );

                    if (file_exists($fp)) {

                        $fs = filesize($fp);
                        $fh = fopen($fp, 'r');
                        $fd = fread($fh, $fs);
                        fclose($fh);

                        $json = json_decode($fd, true);
                        $head = isset($json['head']) ? $json['head'] : array();
                        $data = isset($json['data']) ? $json['data'] : array();

                        $config['head'] = array_merge($config['head'], $head);
                        $config['data'] = array_merge($config['data'], $data);
                    }
                }

                unset($config);
            }

            final public function __get($name) {

                if (!isset($this->data[$name])) {

                    if (isset(  $this->config['data'][$name])) {
                        $data = $this->config['data'][$name];

                        if (isset(  $this->config['head'][$name])) {
                            $head = $this->config['head'][$name];

                            if (isset($head['area']) && $head['area']) {
                                $area = Env::area();

                                if (isset($data[$area])) {

                                    if (isset($head['stage']) && $head['stage']) {
                                        $stage = Env::stage();

                                        if (isset($data[$area][$stage])) {
                                            $this->data[$name] = $data[$area][$stage];
                                        }
                                    }
                                    else { $this->data[$name] = $data[$area]; }
                                }
                            }
                            elseif (isset($head['stage']) && $head['stage']) {
                                $stage = Env::stage();

                                if (isset($data[$stage])) {
                                    $this->data[$name] = $data[$stage];
                                }
                            }
                            else { $this->data[$name] = $data; }
                        }
                        else { $this->data[$name] = $data; }

                        if (isset($this->data[$name])) {
                            unset($this->config['data'][$name]);
                        }
                    }
                }

                return isset($this->data[$name]) ? $this->data[$name] : null;
            }
        }
    }

?>
