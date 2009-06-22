--TEST--
Pyrus DER: IA5String
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('160d7465737431407273612e636f6d', bin2hex($der->IA5String('test1@rsa.com')->serialize()),
                    'test1@rsa.com');
try {
    $der = new PEAR2_Pyrus_DER;
    $test->assertEquals('160d7465737431407273612e636f6d', bin2hex($der->IA5String('test1@rsa.com' . "\277")->serialize()),
                        'test1@rsa.com');
    throw new Exception('should fail, and didn\'t');
} catch (PEAR2_Pyrus_DER_Exception $e) {
    $test->assertEquals('Invalid IA5 String value, can only contain ASCII', $e->getMessage(), 'message');
}
?>
===DONE===
--EXPECT--
===DONE===