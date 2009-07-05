--TEST--
test current returns singleton registry object
--FILE--
<?php
require dirname(__FILE__) . '/../setup.php.inc';
$test->assertEquals('pear2\Pyrus\Config',
                    get_class(\pear2\Pyrus\Config::current()),
                    'test config');

$test->assertEquals('pear2\Pyrus\Registry',
                    get_class(\pear2\Pyrus\Config::current()->registry),
                    'test registry');
?>
===DONE===
--EXPECT--
===DONE===