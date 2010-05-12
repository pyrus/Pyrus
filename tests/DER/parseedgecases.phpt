--TEST--
Pyrus DER: parse edge cases
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $der->parseFromString(chr(100));
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Unknown tag: 0x64',
                        $e->getMessage(), 'error');
}
try {
    $der->setSchema(new PEAR2\Pyrus\DER\Schema);
    $der->parseFromString(chr(1));
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Invalid DER document, no matching elements for tag "Boolean" (0x1) at ',
                        $e->getMessage(), 'error');
}
$der = new PEAR2\Pyrus\DER;
$der->parseFromString(chr(0xA0) . chr(3) . chr(1) . chr(1) . chr(0));

$test->assertEquals('(multiple): 
 sequence: 
  boolean(FALSE)
 end sequence
end (multiple)
', (string) $der, 'parse result');

$der = new PEAR2\Pyrus\DER;
$der->parseFromString(chr(0x80) . chr(3) . chr(1) . chr(1) . chr(0));

$test->assertEquals('(multiple): 
 octetString(010100)
end (multiple)
', (string) $der, 'parse result');
$test->assertEquals('[]', (string)new PEAR2\Pyrus\DER, 'blank');
?>
===DONE===
--EXPECT--
===DONE===