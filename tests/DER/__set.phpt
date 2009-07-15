--TEST--
Pyrus DER: __set edge cases
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $der->oops = 1;
    throw new Exception('worked and should not');
} catch (pear2\Pyrus\DER\Exception $e) {
    $test->assertEquals('To access objects, use ArrayAccess when schema is not set',
                        $e->getMessage(), 'error');
}
$schema = new \pear2\Pyrus\DER\Schema;
$der->setSchema($schema);
try {
    $der->oops = 1;
    throw new Exception('worked and should not');
} catch (pear2\Pyrus\DER\Exception $e) {
    $test->assertEquals('schema has no element matching oops at ',
                        $e->getMessage(), 'error');
}
?>
===DONE===
--EXPECT--
===DONE===