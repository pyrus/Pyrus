--TEST--
PEAR2_Pyrus_Config::constructDefaults() extension_dir from php.ini
--SKIPIF--
<?php if (!ini_get('extension_dir')) die("skip extension_dir not set"); ?>
--ENV--
PHP_PEAR_EXTENSION_DIR=
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
tc::constructDefaults();
$defaults = tc::getTestDefaults();
$test->assertEquals(ini_get('extension_dir'), $defaults['ext_dir'], 'after');
?>
===DONE===
--EXPECT--
===DONE===
