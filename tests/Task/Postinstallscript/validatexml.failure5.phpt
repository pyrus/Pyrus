--TEST--
\PEAR2\Pyrus\Task\Postinstallscript::validateXml() failures 5
--FILE--
<?php
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

file_put_contents(TESTDIR . '/glooby', '<?php
class poo {}
');

$xmltest(array(), array('role' => 'php', 'name' => 'glooby'), 'task <postinstallscript> in file filename is invalid because of "Post-install script "glooby" class "poo" must be named "glooby_postinstall""', '\PEAR2\Pyrus\Task\Exception\Invalidtask');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===