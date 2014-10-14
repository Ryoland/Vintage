<?php

    namespace Vintage\A {

        abstract class Lib {

            protected $A = array();
            protected $P = array();

            public function __construct(array $a = array()) {
                $this->A = $a;
                $this->init();
                $this->proc();
            }

            protected function init() {}

            protected function proc() {}
        }
    }

?>
