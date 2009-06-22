--TEST--
Pyrus DER: Object Identifier
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('06062a864886f70d', bin2hex($der->objectIdentifier('1.2.840.113549')->serialize()),
                    'RSA Object Identifier test');
?>
===DONE===
--EXPECT--
===DONE===