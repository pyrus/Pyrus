--TEST--
\pear2\Pyrus\Config::constructDefaults() bin_dir from PHP_PEAR_BIN_DIR
--ENV--
PATH=.;{PWD}
PHP_PEAR_BIN_DIR=somethingelse
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
tc::constructDefaults();
$defaults = tc::getTestDefaults();
$test->assertEquals('somethingelse', $defaults['bin_dir'], 'after');
?>
===DONE===
--EXPECT--
===DONE===
