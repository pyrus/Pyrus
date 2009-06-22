--TEST--
Pyrus DER: Null
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('0500', bin2hex($der->null()->serialize()),
                    'null test');
?>
===DONE===
--EXPECT--
===DONE===