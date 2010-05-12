--TEST--
\PEAR2\Pyrus\Task\Postinstallscript::validateXml() failures 1
--FILE--
<?php
define('MYDIR', __DIR__);
include dirname(__DIR__) . '/setup.php.inc';
$xmltest = function($xml, $filexml, $message, $exception) use ($package, $test)
{
    try {
        \PEAR2\Pyrus\Task\Postinstallscript::validateXml($package, $xml, $filexml, 'filename');
        throw new Exception('should have failed');
    } catch (Exception $e) {
        $test->assertIsa($exception, $e, 'wrong exception class ' . $message);
        $test->assertEquals($message, $e->getMessage(), 'wrong message');
        return $e;
    }
};
$causetest = function($message, $severity, $exception, $index, $errs) use ($test)
{
    $errs = $errs->getPrevious();
    $test->assertIsa($exception, $errs->{$severity}[$index], 'right class');
    $test->assertEquals($message, $errs->{$severity}[$index]->getMessage(), 'right message');
};

$xmltest(array(), array('role' => 'src', 'name' => 'foo'), 'task <postinstallscript> in file filename is invalid because of "' .
         'Post-install script "foo" must be role="php""', '\PEAR2\Pyrus\Task\Exception\Invalidtask');

?>
===DONE===
--EXPECT--
===DONE===