--TEST--
\pear2\Pyrus\Config::constructDefaults() bin_dir from PATH on Windows
--SKIPIF--
<?php
if (PATH_SEPARATOR !== ';') echo 'skip requires MS Windows';
?>
--ENV--
PATH=.;{PWD}
PHP_PEAR_BIN_DIR=
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
tc::constructDefaults();
$defaults = tc::getTestDefaults();
$test->assertEquals(dirname(__FILE__), $defaults['bin_dir'], 'after');
?>
===DONE===
--EXPECT--
===DONE===
