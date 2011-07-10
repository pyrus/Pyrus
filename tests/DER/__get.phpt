--TEST--
Pyrus DER: __get edge cases
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $der->oops;
    throw new Exception('worked and should not');
} catch (Pyrus\DER\Exception $e) {
    $test->assertEquals('To access objects, use ArrayAccess when schema is not set',
                        $e->getMessage(), 'error');
}
$schema = new \Pyrus\DER\Schema;
$der->setSchema($schema);
try {
    $der->oops;
    throw new Exception('worked and should not');
} catch (Pyrus\DER\Exception $e) {
    $test->assertEquals('schema has no element matching oops at ',
                        $e->getMessage(), 'error');
}
?>
===DONE===
--EXPECT--
===DONE===