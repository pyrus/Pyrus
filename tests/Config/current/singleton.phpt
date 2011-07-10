--TEST--
test current returns singleton registry object
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$test->assertEquals('Pyrus\Config',
                    get_class(\Pyrus\Config::current()),
                    'test config');

$test->assertEquals('Pyrus\Registry',
                    get_class(\Pyrus\Config::current()->registry),
                    'test registry');
?>
===DONE===
--EXPECT--
===DONE===