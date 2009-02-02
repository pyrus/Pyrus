--TEST--
PEAR2_Pyrus_Config::userInitialized() basic test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertFalse(r::userInitialized(), 'first, nothing found');
r::$foo = 2;
mkdir(__DIR__ . '/testing');
file_put_contents(__DIR__ . '/testing/myfile.xml', '<hi/>');
$test->assertTrue(r::userInitialized(), 'second, myfile.xml found');
r::$foo = 1;
copy(__DIR__ . '/testing/myfile.xml', __DIR__ . '/testing/pearconfig.xml');
$dir = getcwd();
chdir(__DIR__ . '/testing');
$test->assertFileExists(__DIR__ . '/testing/pearconfig.xml', 'pearconfig.xml should exist');
$test->assertTrue(r::userInitialized(), 'third, pearconfig.xml found');
file_put_contents(__DIR__ . '/testing/pearconfig.xml', '<?xml version="1.0"?>
<pearconfig version="1.0"><default_channel>pear2.php.net</default_channel><preferred_mirror>pear2.php.net</preferred_mirror><auto_discover>0</auto_discover><download_dir>/hoo/boy</download_dir></pearconfig>
');
$t = r::singleton();
$test->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'pearconfig.xml', $t->userfile, 'pearconfig.xml file');
$test->assertEquals('/hoo/boy', $t->download_dir, 'check whether it loaded pearconfig.xml');
chdir($dir);
?>
===DONE===
--CLEAN--
--CLEAN--
<?php
$dir = __DIR__ . '/testing';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
