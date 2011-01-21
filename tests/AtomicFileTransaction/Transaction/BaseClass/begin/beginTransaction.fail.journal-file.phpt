--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

touch($journalPath);

try {
    $instance->begin();
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals(
        'unrecoverable transaction error: journal path ' . $journalPath . ' exists and is not a directory',
        $e->getMessage(),
        'error'
    );
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===