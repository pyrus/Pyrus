--TEST--
Test PEAR2\Autoload initalization w/2nd path
--FILE--
<?php
require __DIR__ . '/../src/PEAR2/Autoload.php';
PEAR2\Autoload::initialize(__DIR__.'/_files');
$paths = PEAR2\Autoload::getPaths();
echo sizeof($paths);
?>
--EXPECT--
2