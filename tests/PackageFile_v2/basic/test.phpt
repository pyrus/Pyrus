--TEST--
PackageFile v2: test basic, simple methods
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$a = new \pear2\Pyrus\PackageFile\v2;
$a->setPackageFile('foo/bar', 'bar');
$test->assertEquals('foo/bar', $a->getPackageFile(), 'packagefile');
$test->assertEquals('foo/bar', $a->packagefile, 'packagefile 2');
$test->assertEquals('bar', $a->getArchiveFile(), 'archivefile');
$test->assertEquals('foo', $a->filepath, 'archivefile 2');

$a->setFilelist(array('foo.php' => array('role' => 'test'), 'bar.php' => array('role' => 'php')));

$test->assertTrue($a->hasFile('foo.php'), 'hasfile1');
$test->assertTrue($a->hasFile('bar.php'), 'hasfile2');
$test->assertFalse($a->hasFile('no'), 'hasfile3');

$test->assertEquals(array('role' => 'test'), $a->getFile('foo.php'), 'foo');
$test->assertEquals(array('role' => 'php'), $a->getFile('bar.php'), 'bar');

try {
    $a->setFileAttribute('foo.php', 'noway', 'no');
    throw new \Exception('noway did not fail');
} catch (\pear2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Cannot set invalid attribute noway for file foo.php', $e->getMessage(), 'noway message');
}

try {
    $a->setFileAttribute('foo.php', 'name', 'no');
    throw new \Exception('name did not fail');
} catch (\pear2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Cannot change name of file foo.php', $e->getMessage(), 'name message');
}

try {
    $a->setFileAttribute('oops', 'role', 'test');
    throw new \Exception('oops did not fail');
} catch (\pear2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Cannot set attribute role for non-existent file oops', $e->getMessage(), 'oops message');
}
$i = 1;
foreach (array('role', 'baseinstalldir', 'install-as') as $attr) {
    $a->setFileAttribute('foo.php', $attr, 'hi'.$i++);
}
$test->assertEquals(array (
  'role' => 'test',
  'attribs' => 
  array (
    'role' => 'hi1',
    'baseinstalldir' => 'hi2',
    'install-as' => 'hi3',
  ),
), $a->getFile('foo.php'), 'final');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===