--TEST--
test current returns singleton registry object
--FILE--
<?php
require dirname(__FILE__) . '/../setup.php.inc';
$test->assertEquals('PEAR2_Pyrus_Config',
                    get_class(PEAR2_Pyrus_Config::current()),
                    'test config');

$test->assertEquals('PEAR2_Pyrus_Registry',
                    get_class(PEAR2_Pyrus_Config::current()->registry),
                    'test registry');
?>
===DONE===
--EXPECT--
===DONE===