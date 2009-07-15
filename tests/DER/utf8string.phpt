--TEST--
Pyrus DER: UTF8String
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('0c0d7465737431407273612e636f6d', bin2hex($der->UTF8String('test1@rsa.com')->serialize()),
                    'test1@rsa.com');
?>
===DONE===
--EXPECT--
===DONE===