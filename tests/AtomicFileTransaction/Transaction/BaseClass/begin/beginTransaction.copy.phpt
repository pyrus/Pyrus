--TEST--
\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$dirs = array(
    $path,
    $path . '/sub/deep/deep/thing',
    $path . '/anothernew/dir',
    $path . '/anothernew/dir/file'
);
$files = array(
    'foo',
    'another',
    'anothernew/dir/file',

);
mkdir($path);
touch($path . '/foo', 1234567);
touch($path . '/another');
umask(0);
mkdir($path . '/sub/deep/deep/thing', 0777, true);
mkdir($path . '/anothernew/dir', 0777, true);
umask(022);
touch($path . '/anothernew/dir/file');

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