<?php
$__e = error_reporting();
error_reporting(E_ERROR|E_NOTICE|E_WARNING);
require_once __DIR__ . '/../../autoload.php';
@include_once 'Text/Diff.php';
@include_once 'Text/Diff/Renderer.php';
@include_once 'Text/Diff/Renderer/unified.php';
error_reporting($__e);
class PEAR2_PHPT
{
    var $_diffonly;
    function __construct($diffonly = false)
    {
        $this->_diffonly = $diffonly;
        $this->_errors = array();
    }

    function assertTrue($test, $message)
    {
        if ($test === true) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-true value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertIsa($control, $test, $message)
    {
        if (is_a($test, $control)) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-$control object: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNull($test, $message)
    {
        if ($test === null) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-null value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotNull($test, $message)
    {
        if ($test !== null) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected null: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertSame($test, $test1, $message)
    {
        if ($test === $test1) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpectedly two vars are not the same thing: \n";
        echo "\n'$message'\n";
        return false;
    }

    function assertNotSame($test, $test1, $message)
    {
        if ($test !== $test1) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpectedly two vars are the same thing: \n";
        echo "\n'$message'\n";
        return false;
    }

    function assertFalse($test, $message)
    {
        if ($test === false) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-false value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotTrue($test, $message)
    {
        if (!$test) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected loose true value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotFalse($test, $message)
    {
        if ($test) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected loose false value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertRegex($regex, $test, $message)
    {
        if (!preg_match($regex, $test)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "Expecting:\nText Matching Regular Expression $regex\n";
            echo "\nReceived:\n";
            var_export($test);
            echo "\n";
            return false;
        }
        return true;
    }

    function assertException($exception, $class, $emessage, $message)
    {
        if (!($exception instanceof $class)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "Expecting class $class, got ", get_class($exception);
        }
        $this->assertEquals($emessage, $exception->getMessage(), $message, debug_backtrace());
    }

    function assertEquals($control, $test, $message, $trace = null)
    {
        if (!$trace) {
            $trace = debug_backtrace();
        }
        if (str_replace(array("\r", "\n"), array('', ''),
            var_export($control, true)) != str_replace(array("\r", "\n"), array('', ''),
            var_export($test, true))) {
            $this->_failTest($trace, $message);
            if (class_exists('Text_Diff')) {
                echo "Diff of expecting/received:\n";
                $diff = new Text_Diff(
                    explode("\n", var_export($control, true)),
                    explode("\n", var_export($test, true)));

                // Output the diff in unified format.
                $renderer = new Text_Diff_Renderer_unified();
                echo $renderer->render($diff);
                if ($this->_diffonly) {
                    return false;
                }
            }
            echo "Expecting:\n";
            var_export($control);
            echo "\nReceived:\n";
            var_export($test);
            echo "\n";
            return false;
        }
        return true;
    }

    function assertFileExists($fname, $message)
    {
        if (!@file_exists($fname)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "File '$fname' does not exist, and should\n";
            return false;
        }
        return true;
    }

    function assertFileNotExists($fname, $message)
    {
        if (@file_exists($fname)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "File '$fname' exists, and should not\n";
            return false;
        }
        return true;
    }

    function _failTest($trace, $message)
    {
        echo 'Test Failure: "' . $message  . "\"\n in " . $trace[0]['file'] . ' line ' .
            $trace[0]['line'] . "\n";
    }

    function showAll()
    {
        $this->_diffonly = false;
    }
}
$test = new PEAR2_PHPT();
?>
