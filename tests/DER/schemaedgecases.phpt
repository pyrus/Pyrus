--TEST--
Pyrus DER: schema edge cases
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$schema = new PEAR2\Pyrus\DER\Schema;
$schema->choice()
->end()
->Integer('foo', 1);
$test->assertEquals('schema: 
schema()
end schema
', (string) $schema, 'string it');
try {
    $schema->Integer();
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Invalid schema, element must be named',
                        $e->getMessage(), 'error');
}
try {
    $schema->blurp('hi');
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Unknown type blurp at ',
                        $e->getMessage(), 'error');
}
try {
    $schema->blurp;
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\DER\Exception $e) {
    $test->assertEquals('Unknown schema element blurp',
                        $e->getMessage(), 'error');
}

$test->assertEquals(array(), $schema->types, 'types');
$test->assertEquals('foo', $schema->foo->name, 'name');
$test->assertEquals(0x81, $schema->foo->tag, 'tag');
?>
===DONE===
--EXPECT--
===DONE===