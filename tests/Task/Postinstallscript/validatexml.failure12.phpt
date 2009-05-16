--TEST--
PEAR2_Pyrus_Task_Postinstallscript::validateXml() failures 12
--FILE--
<?php
define('MYDIR', __DIR__);
include dirname(__DIR__) . '/setup.php.inc';
$xmltest = function($xml, $filexml, $message, $exception) use ($package, $test)
{
    try {
        PEAR2_Pyrus_Task_Postinstallscript::validateXml($package, $xml, $filexml, 'filename');
        throw new Exception('should have failed');
    } catch (Exception $e) {
        $test->assertIsa($exception, $e, 'wrong exception class ' . $message);
        $test->assertEquals($message, $e->getMessage(), 'wrong message');
        return $e;
    }
};
$causetest = function($message, $severity, $exception, $index, $errs) use ($test)
{
    $errs = $errs->getCause();
    $test->assertIsa($exception, $errs->{$severity}[$index], 'right class');
    $test->assertEquals($message, $errs->{$severity}[$index]->getMessage(), 'right message');
};

mkdir(__DIR__ . '/testit');
file_put_contents(__DIR__ . '/testit/glooby', '<?php
class glooby_postinstall {
    function init(){}
    function run(){}
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
               array('tasks:id' => 'hi',
                     'tasks:name' => 'foo::bar',
                     'tasks:conditiontype' => 'blah'))), array('role' => 'php', 'name' => 'glooby'), 'task <postinstallscript> in file filename is invalid because of "Post-install script "glooby" <paramgroup> id "hi" must have a <conditiontype> tag containing either "=", "!=", or "preg_match""', 'PEAR2_Pyrus_Task_Exception_Invalidtask');


?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===