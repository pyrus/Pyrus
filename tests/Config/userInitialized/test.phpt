--TEST--
PEAR2_Pyrus_Config::userInitialized() basic test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertFalse(r::userInitialized(), 'first, nothing found');
r::$foo = 2;
$test->assertTrue(r::userInitialized(), 'second, myfile.xml found');
r::$foo = 1;
copy(__DIR__ . '/myfile.xml', __DIR__ . '/pearconfig.xml');
$dir = getcwd();
chdir(__DIR__);
$test->assertFileExists(__DIR__ . '/pearconfig.xml', 'pearconfig.xml does not exist');
$test->assertTrue(r::userInitialized(), 'third, pearconfig.xml found');
file_put_contents('pearconfig.xml', '<?xml version="1.0"?>
<pearconfig version="1.0"><default_channel>pear2.php.net</default_channel><preferred_mirror>pear2.php.net</preferred_mirror><auto_discover>0</auto_discover><download_dir>/hoo/boy</download_dir></pearconfig>
');
$t = r::singleton();
$test->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'pearconfig.xml', $t->userfile, 'pearconfig.xml file');
$test->assertEquals('/hoo/boy', $t->download_dir, 'check whether it loaded pearconfig.xml');
chdir($dir);
?>
===DONE===
--CLEAN--
<?php
unlink(__DIR__ . '/pearconfig.xml');
foreach (new DirectoryIterator(__DIR__) as $file) {
    if ($file->isDot()) continue;
    if ($file->getFileName() == '.pear2registry') unlink($file->getPathName());
    if (substr($file->getFileName(), 0, 4) == 'chan') unlink($file->getPathName());
}
?>
--EXPECT--
===DONE===
