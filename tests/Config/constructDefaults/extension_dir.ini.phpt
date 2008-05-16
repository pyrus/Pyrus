--TEST--
PEAR2_Pyrus_Config::constructDefaults() extension_dir from php.ini
--ENV--
PATH=.
--FILE--
<?php
if (!ini_get('extension_dir')) {
    ini_set('extension_dir', 'something');
}
require dirname(__FILE__) . '/setup.php.inc';
tc::constructDefaults();
$defaults = tc::getTestDefaults();
$test->assertEquals(ini_get('extension_dir'), $defaults['ext_dir'], 'after');
?>
===DONE===
--EXPECT--
===DONE===
