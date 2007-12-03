--TEST--
PEAR2_Pyrus_Config::constructDefaults() extension_dir from php.ini
--INI--
extension_dir=something
--ENV--
PATH=.
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
tc::constructDefaults();
$defaults = tc::getTestDefaults();
$test->assertEquals('something', $defaults['ext_dir'], 'after');
?>
===DONE===
--EXPECT--
===DONE===
