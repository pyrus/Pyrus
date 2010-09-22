<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * PEAR2_Exception
 *
 * PHP versions 4 and 5
 *
 * @category   pear
 * @package    PEAR2_Exception
 * @author     Tomas V. V. Cox <cox@idecnet.com>
 * @author     Hans Lellelid <hans@velum.net>
 * @author     Bertrand Mansion <bmansion@mamasam.com>
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2007 The PHP Group
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear2.php.net/PEAR2_Exception
 * @since      File available since Release 0.1.0
 */


/**
 * Base PEAR2_Exception Class
 *
 * 1) Features:
 *
 * - Nestable exceptions (throw new PEAR2_Exception($msg, $prev_exception))
 * - Definable triggers, shot when exceptions occur
 * - Added more context info available (like class, method or cause)
 * - cause can be a PEAR2_Exception or an array of mixed
 *   PEAR2_Exceptions or a \PEAR2\MultiErrors
 * - callbacks for specific exception classes and their children
 *
 * 2) Usage example
 *
 * <code>
 * namespace PEAR2;
 * class PEAR2_MyPackage_Exception extends Exception {}
 *
 * class Test
 * {
 *     function foo()
 *     {
 *         throw new PEAR2_MyPackage_Exception('Error Message', 4);
 *     }
 * }
 *
 * function myLogger($exception)
 * {
 *     echo 'Logger: ' . $exception->getMessage() . "\n";
 * }
 *
 * // each time a exception is thrown the 'myLogger' will be called
 * // (its use is completely optional)
 * Exception::addObserver('\PEAR2\myLogger');
 * $test = new Test;
 * try {
 *     $test->foo();
 * } catch (\Exception $e) {
 *     print $e;
 * }
 * </code>
 *
 * @category   pear
 * @package    PEAR
 * @author     Tomas V.V.Cox <cox@idecnet.com>
 * @author     Hans Lellelid <hans@velum.net>
 * @author     Bertrand Mansion <bmansion@mamasam.com>
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2007 The PHP Group
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: @package_version@
 * @link       http://pear2.php.net/PEAR2_Exception
 * @since      Class available since Release 0.1.0
 *
 */
namespace PEAR2;
abstract class Exception extends \Exception
{
    private static $_observers = array();
    private $_trace;

    /**
     * Supported signatures:
     *  - PEAR2_Exception(string $message);
     *  - PEAR2_Exception(string $message, int $code);
     *  - PEAR2_Exception(string $message, Exception $cause);
     *  - PEAR2_Exception(string $message, Exception $cause, int $code);
     *  - PEAR2_Exception(string $message, PEAR2\MultiErrors $cause);
     *  - PEAR2_Exception(string $message, PEAR2\MultiErrors $cause, int $code);
     * @param string exception message
     * @param int|Exception|PEAR2\MultiErrors|null exception cause
     * @param int|null exception code or null
     */
    public function __construct($message, $p2 = null, $p3 = null)
    {
        $code = $cause = null;
        if (is_int($p2)) {
            $code = $p2;
        } elseif (is_object($p2)) {
            if (!($p2 instanceof \Exception)) {
                throw new \Exception('exception cause must be Exception, or PEAR2\MultiErrors');
            }

            $code  = $p3;
            $cause = $p2;
        }

        if (!is_string($message)) {
            throw new \Exception('exception message must be a string, was ' . gettype($message));
        }

        parent::__construct($message, $code, $cause);

        foreach (self::$_observers as $func) {
            if (is_callable($func)) {
                call_user_func($func, $this);
            }
        }
    }

    /**
     * @param mixed $callback  - A valid php callback, see php func is_callable()
     *                         - A PEAR2_Exception::OBSERVER_* constant
     *                         - An array(const PEAR2_Exception::OBSERVER_*,
     *                           mixed $options)
     * @param string $label    The name of the observer. Use this if you want
     *                         to remove it later with removeObserver()
     */
    public static function addObserver($callback, $label = 'default')
    {
        self::$_observers[$label] = $callback;
    }

    public static function removeObserver($label = 'default')
    {
        unset(self::$_observers[$label]);
    }

    /**
     * Function must be public to call on caused exceptions
     * @param array
     */
    public function getCauseMessage(array &$causes)
    {
        $trace = $this->getTraceSafe();
        $cause = array(
            'class'   => get_class($this),
            'message' => $this->message,
            'file'    => 'unknown',
            'line'    => 'unknown'
        );

        if (isset($trace[0]) && isset($trace[0]['file'])) {
            $cause['file'] = $trace[0]['file'];
            $cause['line'] = $trace[0]['line'];
        }

        $causes[] = $cause;
        if ($this->getPrevious() instanceof self) {
            $this->getPrevious()->getCauseMessage($causes);
        } elseif ($this->getPrevious() instanceof \PEAR2\MultiErrors) {
            foreach ($this->getPrevious() as $cause) {
                if ($cause instanceof self) {
                    $cause->getCauseMessage($causes);
                } elseif ($cause instanceof \Exception) {
                    $causes[] = array(
                        'class'   => get_class($cause),
                        'message' => $cause->getMessage(),
                        'file'    => $cause->getFile(),
                        'line'    => $cause->getLine()
                    );
                }
            }
        } elseif ($this->getPrevious() instanceof \Exception) {
            $causes[] = array(
                'class'   => get_class($this->getPrevious()),
                'message' => $this->getPrevious()->getMessage(),
                'file'    => $this->getPrevious()->getFile(),
                'line'    => $this->getPrevious()->getLine()
            );
        }
    }

    public function getTraceSafe()
    {
        if (!isset($this->_trace)) {
            $this->_trace = $this->getTrace();
            if (empty($this->_trace)) {
                $backtrace = debug_backtrace();
                $this->_trace = array($backtrace[count($backtrace)-1]);
            }
        }

        return $this->_trace;
    }
}