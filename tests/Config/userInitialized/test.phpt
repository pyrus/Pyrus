--TEST--
\Pyrus\Config::userInitialized() basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertFalse(r::userInitialized(), 'first, nothing found');
r::$foo = 2;
mkdir(TESTDIR . '/testing');
file_put_contents(TESTDIR . '/testing/myfile.xml', '<hi/>');
$test->assertTrue(r::userInitialized(), 'second, myfile.xml found');
r::$foo = 1;
copy(TESTDIR . '/testing/myfile.xml', TESTDIR . '/testing/pearconfig.xml');
$dir = getcwd();
chdir(TESTDIR . '/testing');
$test->assertFileExists(TESTDIR . '/testing/pearconfig.xml', 'pearconfig.xml should exist');
$test->assertTrue(r::userInitialized(), 'third, pearconfig.xml found');
file_put_contents(TESTDIR . '/testing/pearconfig.xml', '<?xml version="1.0"?>
<pearconfig version="1.0">
 <default_channel>pear2.php.net</default_channel>
 <preferred_mirror>
  <pear2DOTphpDOTnet>pear2.php.net</pear2DOTphpDOTnet>
 </preferred_mirror>
 <auto_discover>0</auto_discover>
 <download_dir>
  <pear2DOTphpDOTnet>/hoo/boy</pear2DOTphpDOTnet>
 </download_dir>
</pearconfig>
');
$t = r::singleton();
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'pearconfig.xml', $t->userfile, 'pearconfig.xml file');
$test->assertEquals('/hoo/boy', $t->download_dir, 'check whether it loaded pearconfig.xml');
chdir($dir);
?>
===DONE===
--CLEAN--
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
