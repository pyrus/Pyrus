--TEST--
\PEAR2\Pyrus\Task\Postinstallscript::validateXml() failures 1
--FILE--
<?php
include dirname(__DIR__) . '/setup.php.inc';

class testClass {
}
\PEAR2\Pyrus\Task\Common::registerCustomTask(array('name' => 'test', 'class' => 'testClass'));

$test->assertEquals('testClass', \PEAR2\Pyrus\Task\Common::getTask('test'), 'Missing task test');
$test->assertEquals('PEAR2\Pyrus\Task\Replace', \PEAR2\Pyrus\Task\Common::getTask('replace'), 'Missing task replace');
?>
===DONE===
--EXPECT--
===DONE===