--TEST--
test current returns singleton registry object
--FILE--
<?php
require dirname(__FILE__) . '/../setup.php.inc';
$test->assertEquals('PEAR2\Pyrus\Config',
                    get_class(\PEAR2\Pyrus\Config::current()),
                    'test config');

$test->assertEquals('PEAR2\Pyrus\Registry',
                    get_class(\PEAR2\Pyrus\Config::current()->registry),
                    'test registry');
?>
===DONE===
--EXPECT--
===DONE===