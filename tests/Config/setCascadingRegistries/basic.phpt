--TEST--
PEAR2_Pyrus_Config::setCascading Registries() basic test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
PEAR2_Pyrus_Config::setCascadingRegistries(dirname(__FILE__) . '/something');
$test->assertEquals(array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo' => true), r::$parents, 'registry');
$test->assertEquals(array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo' => true), c::$parents, 'channel registry');
?>
===DONE===
--CLEAN--
<?php
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo'), RecursiveIteratorIterator::LEAVES_ONLY) as $name => $file) {
    unlink($file->getPathname());
}
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something'), RecursiveIteratorIterator::LEAVES_ONLY) as $name => $file) {
    unlink($file->getPathname());
}
rmdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
rmdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something');
?>
--EXPECT--
===DONE===
