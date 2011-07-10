--TEST--
Pyrus DER: PrintableString
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('130d74657374312b7273612e636f6d', bin2hex($der->printableString('test1+rsa.com')->serialize()),
                    'test1@rsa.com');
$der = new \Pyrus\DER;
try {
    $test->assertEquals('130d7465737431407273612e636f6d', bin2hex($der->printableString('test1@rsa.com')->serialize()),
                        'test1@rsa.com');
    throw new Exception('worked and should not');
} catch (\Pyrus\DER\Exception $e) {
    $test->assertEquals('Invalid Printable string value test1@rsa.com' .
                                                ', can only contain letters, digits, space and' .
                                                ' these punctuations: \' ( ) + , - . / : = ?', $e->getMessage(),
                                                'message');
}
?>
===DONE===
--EXPECT--
===DONE===