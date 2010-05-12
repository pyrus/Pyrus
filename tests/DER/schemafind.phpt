--TEST--
Pyrus DER: schema find
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$schema = new PEAR2\Pyrus\DER\Schema;
$schema->Integer('oops');
$der->setSchema($schema);

try {
    $der->parseFromString(chr(16).chr(03).chr(01).chr(02).chr(03));
    throw new Exception('worked and should fail');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Invalid DER document, required tag oops not found, instead requested tag value 10 at ',
                        $e->getMessage(), 'error');
}

try {
    $der->parseFromString(chr(1).chr(03).chr(01).chr(02).chr(03));
    throw new Exception('worked and should fail');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Invalid DER document, required tag oops not found, instead requested tag value "Boolean" (0x1) at ',
                        $e->getMessage(), 'error');
}
$schema = new PEAR2\Pyrus\DER\Schema;
$schema->any('first');
$der->setSchema($schema);
$der->parseFromString(chr(0x80).chr(1).chr(0x12));
$test->assertEquals('
 first [octetString] (12)
end 
', (string) $der, 'after parsing');
try {
    $der->parseFromString(chr(100).chr(03).chr(01).chr(02).chr(03));
    throw new Exception('worked and should fail');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Unknown tag: 64 at ',
                        $e->getMessage(), 'error');
}

$schema = new PEAR2\Pyrus\DER\Schema;
$choice = $schema->choice('hi', 0);
$choice->choice('inner', 1);

$test->assertEquals('inner', $choice->findTag(0x81)->name, 'findTag');
?>
===DONE===
--EXPECT--
===DONE===