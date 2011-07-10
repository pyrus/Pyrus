--TEST--
Test PEAR2\Autoload initalization w/2nd path AND loading class.
--FILE--
<?php
require __DIR__ . '/../src/PEAR2/Autoload.php';
PEAR2\Autoload::initialize(__DIR__.'/_files');
echo testDir1\Foo::sayHello();

?>
--EXPECT--
class testDir1\Foo says hi
