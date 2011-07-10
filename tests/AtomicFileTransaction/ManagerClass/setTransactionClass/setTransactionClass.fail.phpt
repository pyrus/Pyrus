--TEST--
\Pyrus\AtomicFileTransaction\Manager::setTransactionClass()
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

try {
    $instance->setTransactionClass('UnknownClass');
    throw new \Exception('Expected exception');
} catch (\InvalidArgumentException $e) {
    $test->assertEquals('className must be a valid class - class cannot be loaded.', $e->getMessage(), 'must be equal');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===