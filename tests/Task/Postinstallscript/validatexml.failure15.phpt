--TEST--
\PEAR2\Pyrus\Task\Postinstallscript::validateXml() failures 15
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
class glooby_postinstall {
    function init2(){}
    function run2(){}
}
');

$xmltest(array('tasks:paramgroup' => array(
               array('tasks:id' => 'foo',
                     'tasks:param' => array(
                            'tasks:name' => 'bar',
                            'tasks:prompt' => 'h',
                            'tasks:type' => 'string',
                        )
                     ),
               array('tasks:id' => 'hi'),
               array('tasks:id' => 'another',
                     'tasks:param' => array(
                        'tasks:name' => '*&',
                            )))), array('role' => 'php', 'name' => 'glooby'),
         'task <postinstallscript> in file filename is invalid because of "Post-install ' .
         'script "glooby" parameter "*&" for <paramgroup> id "another" is not a valid name.' .
         '  Must contain only alphanumeric characters"', '\PEAR2\Pyrus\Task\Exception\Invalidtask');


?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===