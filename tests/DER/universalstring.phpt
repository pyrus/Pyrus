--TEST--
Pyrus DER: UniversalString
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('1c0d7465737431407273612e636f6d', bin2hex($der->UniversalString('test1@rsa.com')->serialize()),
                    'test1@rsa.com');
?>
===DONE===
--EXPECT--
===DONE===