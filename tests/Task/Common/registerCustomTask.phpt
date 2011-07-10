--TEST--
\Pyrus\Task\Postinstallscript::validateXml() failures 1
--FILE--
<?php
include dirname(__DIR__) . '/setup.php.inc';

class testClass {
}
\Pyrus\Task\Common::registerCustomTask(array('name' => 'test', 'class' => 'testClass'));

$test->assertEquals('testClass', \Pyrus\Task\Common::getTask('test'), 'Missing task test');
$test->assertEquals('Pyrus\Task\Replace', \Pyrus\Task\Common::getTask('replace'), 'Missing task replace');
?>
===DONE===
--EXPECT--
===DONE===