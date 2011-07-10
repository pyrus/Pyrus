--TEST--
\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

mkdir($path, 0777);

$test->assertFileExists($path, 'before path');
$test->assertFileNotExists($journalPath, 'before journal');

$instance->begin();

$test->assertFileExists($path, 'after path');
$test->assertFileExists($journalPath, 'after journal');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===