--TEST--
Test PEAR2\Autoload initalization
--FILE--
<?php
require __DIR__ . '/../src/PEAR2/Autoload.php';
$paths = PEAR2\Autoload::getPaths();
echo sizeof($paths);
?>
--EXPECT--
1