--TEST--
\PEAR2\Pyrus\Config::constructDefaults() bin_dir from PATH on Windows
--SKIPIF--
<?php
if (PATH_SEPARATOR !== ';') echo 'skip requires MS Windows';
?>
--ENV--
PATH=.;{PWD}
PHP_PEAR_BIN_DIR=
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
tc::constructDefaults();
$defaults = tc::getTestDefaults();
$test->assertEquals(__DIR__, $defaults['bin_dir'], 'after');
?>
===DONE===
--EXPECT--
===DONE===
