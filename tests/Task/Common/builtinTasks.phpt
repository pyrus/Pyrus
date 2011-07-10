--TEST--
\Pyrus\Task\Postinstallscript::validateXml() failures 1
--FILE--
<?php
include dirname(__DIR__) . '/setup.php.inc';

$test->assertEquals('Pyrus\Task\Replace', \Pyrus\Task\Common::getTask('replace'), 'Missing task replace');
$test->assertEquals('Pyrus\Task\Windowseol', \Pyrus\Task\Common::getTask('windowseol'), 'Missing task windowseol');
$test->assertEquals('Pyrus\Task\Unixeol', \Pyrus\Task\Common::getTask('unixeol'), 'Missing task unixeol');
$test->assertEquals('Pyrus\Task\Postinstallscript', \Pyrus\Task\Common::getTask('postinstallscript'), 'Missing task postinstallscript');
?>
===DONE===
--EXPECT--
===DONE===