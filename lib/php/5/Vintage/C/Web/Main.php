<?php

    namespace Vintage\C\Web {

        use \Vintage\C\PHP\VString as VString;

        final class Main extends \Vintage\A\Lib {

            final public function __construct(array $a) {
                parent::__construct($a);
            }

            final protected function proc() { $this->page(); }

            final private function page() {

                $A         = $this->A;
                $project   = isset($A['project'])   ? $A['project']   : null;
                $page      = isset($A['page'])      ? $A['page']      : null;
                $namespace = isset($A['namespace']) ? $A['namespace'] : null;
                $class     = isset($A['class'])     ? $A['class']     : null;
                $options   = isset($A['options'])   ? $A['options']   : array();

                if (!isset($namespace)) {
                    if (isset($project)) {
                        $namespace = sprintf('/%s/C/Web/Page', $project);
                        $namespace = preg_replace('/\//', '\\', $namespace);
                    }
                }

                if (!isset($class)) {
                    if (isset($page)) {
                        $page  = preg_replace('/\//', '_', $page);
                        $page  = preg_replace('/^_/', '',  $page);
                        $page  = preg_replace('/_$/', '',  $page);
                        $class = VString::pascal_case($page);
                    }
                }

        $options['project'] = $project;
        $options['page']    = $page;

        if (class_exists("$namespace\\$class")) {
          $package = "$namespace\\$class";
          new $package($options);
        }
        elseif (class_exists("$namespace\\Skeleton")) {
          $package = "$namespace\\Skeleton";
          new $package($options);
        }
        else {
          header('HTTP/1.1 404 Not Found');
        }
      }
    }
  }

?>
