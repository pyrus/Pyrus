--TEST--
Pyrus DER: Numericstring
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('120732343520323334', bin2hex($der->Numericstring('245 234')->serialize()),
                    '245 234');
try {
    $der = new \PEAR2\Pyrus\DER;
    $test->assertEquals('120d7465737431407273612e636f6d', bin2hex($der->Numericstring('test1@rsa.com' . "\277")->serialize()),
                        'test1@rsa.com');
    throw new Exception('should fail, and didn\'t');
} catch (\PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Invalid Numeric String value, can only contain digits and space', $e->getMessage(), 'message');
}
?>
===DONE===
--EXPECT--
===DONE===