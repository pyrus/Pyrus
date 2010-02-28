--TEST--
Pyrus DER: Object Identifier
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('06062a864886f70d', bin2hex($der->objectIdentifier('1.2.840.113549')->serialize()),
                    'RSA Object Identifier test');

try {
    // RSA Object Identifier test with invalid data input
    $der->objectIdentifier(12840113549)->serialize();
} catch (pear2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Object Identifier must be a string', $e->getMessage(), 'error');
}

// Test by running without serialize (or else we do not catch the exception in the objectIdentifier call)
try {
    // RSA Object Identifier test with data input with invalid data between delimiters
    $der->objectIdentifier('128.a40113.549');
} catch (pear2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Object Identifier must be a period-delimited string of numbers', $e->getMessage(), 'error');
}

try {
    // RSA Object Identifier test with data input missing delimiters
    $der->objectIdentifier('12840113549')->serialize();
} catch (pear2\Pyrus\DER\Exception $e) {
    $test->assertEquals('The Object Identifier value can be no less than 4 numbers in a period-delimited string', $e->getMessage(), 'error');
}

?>
===DONE===
--EXPECT--
===DONE===