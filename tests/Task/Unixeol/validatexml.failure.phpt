--TEST--
\PEAR2\Pyrus\Task\Unixeol::validateXml() failures
--FILE--
<?php
include dirname(__DIR__) . '/setup.minimal.php.inc';
$xmltest = function($xml, $filexml, $message, $exception) use ($package, $test)
{
    try {
        \PEAR2\Pyrus\Task\Unixeol::validateXml($package, $xml, $filexml, 'filename');
        throw new Exception('should have failed');
    } catch (Exception $e) {
        $test->assertIsa($exception, $e, 'wrong exception class ' . $message);
        $test->assertEquals($message, $e->getMessage(), 'wrong message');
        return $e;
    }
};

$xmltest(array('attribs' => array()), array(), 'task <unixeol> in file filename is invalid because of "no attributes allowed"', '\PEAR2\Pyrus\Task\Exception\Invalidtask');
?>
===DONE===
--EXPECT--
===DONE===