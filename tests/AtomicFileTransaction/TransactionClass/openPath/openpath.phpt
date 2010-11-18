--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::openPath()
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$instance->createOrOpenPath('foo', 'abc');

$fp = $instance->openPath('foo');

$test->assertEquals('abc', stream_get_contents($fp), 'validate content');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===