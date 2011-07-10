--TEST--
\Pyrus\AtomicFileTransaction::mkdir() failure, directory exists and is a file
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$instance->createOrOpenPath('oops', 'hi');

try {
    $instance->mkdir('oops');
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Cannot create directory oops, it is a file', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===