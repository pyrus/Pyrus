<?php
namespace PEAR2;
if (!class_exists('\PEAR2\Autoload', false)) {
    class Autoload
    {
        /**
         * Whether the autoload class has been spl_autoload_register-ed
         * 
         * @var bool
         */
        protected static $registered = false;

        /**
         * Array of PEAR2 autoload paths registered
         * 
         * @var array
         */
        protected static $paths = array();

        /**
         * Initialize the PEAR2 autoloader
         * 
         * @param string $path Directory path to register
         * 
         * @return void
         */
        static function initialize($path)
        {
            self::register();
            self::addPath($path);
        }

        /**
         * Register the PEAR2 autoload class with spl_autoload_register
         * 
         * @return void
         */
        protected static function register()
        {
            if (!self::$registered) {
                // set up __autoload
                $autoload = spl_autoload_functions();
                spl_autoload_register('PEAR2\Autoload::load');
                if (function_exists('__autoload') && ($autoload === false)) {
                    // __autoload() was being used, but now would be ignored, add
                    // it to the autoload stack
                    spl_autoload_register('__autoload');
                }
            }
            self::$registered = true;
        }

        /**
         * Add a path
         * 
         * @param string $path The directory to add to the set of PEAR2 paths
         * 
         * @return void
         */
        protected static function addPath($path)
        {
            if (!in_array($path, self::$paths)) {
                self::$paths[] = $path;
            }
        }

        /**
         * Load a PEAR2 class
         * 
         * @param string $class The class to load
         * 
         * @return bool
         */
        static function load($class)
        {
            if (strtolower(substr($class, 0, 6)) !== 'pear2\\') {
                return false;
            }
            $file = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class) . '.php';
            foreach (self::$paths as $path) {
                if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
                    require $path . DIRECTORY_SEPARATOR . $file;
                    if (!class_exists($class, false) && !interface_exists($class, false)) {
                        die(new \Exception('Class ' . $class . ' was not present in ' .
                            $path . DIRECTORY_SEPARATOR . $file .
                            '") [PEAR2_Autoload-0.2.3]'));
                    }
                    return true;
                }
            }
            $e = new \Exception('Class ' . $class . ' could not be loaded from ' .
                $file . ', file does not exist (registered paths="' .
                implode(PATH_SEPARATOR, self::$paths) .
                '") [PEAR2_Autoload-0.2.3]');
            $trace = $e->getTrace();
            if (isset($trace[2]) && isset($trace[2]['function']) &&
                  in_array($trace[2]['function'], array('class_exists', 'interface_exists'))) {
                return false;
            }
            if (isset($trace[1]) && isset($trace[1]['function']) &&
                  in_array($trace[1]['function'], array('class_exists', 'interface_exists'))) {
                return false;
            }
            die ((string) $e);
        }

        /**
         * return the array of paths PEAR2 autoload has registered
         * 
         * @return array
         */
        static function getPaths()
        {
            return self::$paths;
        }
    }
}
Autoload::initialize(dirname(__DIR__));