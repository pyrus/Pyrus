--TEST--
\pear2\Pyrus\Task\Postinstallscript::validateXml() failures 14
--FILE--
<?php
define('MYDIR', __DIR__);
include dirname(__DIR__) . '/setup.php.inc';
$xmltest = function($xml, $filexml, $message, $exception) use ($package, $test)
{
    try {
        \pear2\Pyrus\Task\Postinstallscript::validateXml($package, $xml, $filexml, 'filename');
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

file_put_contents(__DIR__ . '/testit/glooby', '<?php
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
                        'tasks:oops' => '1',
                            )))), array('role' => 'php', 'name' => 'glooby'),
         'task <postinstallscript> in file filename is invalid because of "Post-install script ' .
         '"glooby" parameter for <paramgroup> id "another" must have a <name> tag"',
         '\pear2\Pyrus\Task\Exception\Invalidtask');


?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===