--TEST--
PEAR2_Pyrus_Config::setCascading Registries() basic test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
PEAR2_Pyrus_Config::setCascadingRegistries('something');
$test->assertEquals(array(), r::$parents, 'registry');
$test->assertEquals(array(), c::$parents, 'channel registry');
?>
===DONE===
--CLEAN--
<?php
@rmdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
?>
--EXPECT--
===DONE===
