--TEST--
\PEAR2\Pyrus\Task\Postinstallscript::validateXml() failures 1
--FILE--
<?php
include dirname(__DIR__) . '/setup.php.inc';

$test->assertEquals('PEAR2\Pyrus\Task\Replace', \PEAR2\Pyrus\Task\Common::getTask('replace'), 'Missing task replace');
$test->assertEquals('PEAR2\Pyrus\Task\Windowseol', \PEAR2\Pyrus\Task\Common::getTask('windowseol'), 'Missing task windowseol');
$test->assertEquals('PEAR2\Pyrus\Task\Unixeol', \PEAR2\Pyrus\Task\Common::getTask('unixeol'), 'Missing task unixeol');
$test->assertEquals('PEAR2\Pyrus\Task\Postinstallscript', \PEAR2\Pyrus\Task\Common::getTask('postinstallscript'), 'Missing task postinstallscript');
?>
===DONE===
--EXPECT--
===DONE===