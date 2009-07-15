--TEST--
Pyrus DirectedGraph: vertex coverage
--FILE--
<?php
use pear2\Pyrus\DirectedGraph\Vertex;
require dirname(__FILE__) . '/setup.php.inc';

try {
    new Vertex('oops');
    throw new Exception('worked and should not');
} catch (pear2\Pyrus\DirectedGraph\Exception $e) {
    $test->assertEquals('data must be an object, was string',
                        $e->getMessage(), 'error');
}
$a = new stdClass;
$a->foo = 2;
$b = new stdClass;
$b->foo = 3;
$c = new stdClass;
$c->foo = 4;
$v = new Vertex($a);

$v2 = new Vertex($b);
$v->connect($v2);

$v3 = new Vertex($c);

$test->assertEquals(true, isset($v[spl_object_hash($v2)]), 'isset v2');
$test->assertSame($v2, $v[spl_object_hash($v2)], 'offsetGet');

$v[] = $v3;
$test->assertEquals(true, isset($v[spl_object_hash($v3)]), 'isset v3');
unset($v[spl_object_hash($v3)]);
$test->assertEquals(false, isset($v[spl_object_hash($v3)]), 'after unset');

foreach ($v as $hash => $obj) {
    $test->assertEquals(spl_object_hash($v2), $hash, 'hash');
    $test->assertSame($v2, $obj, 'obj');
}
?>
===DONE===
--EXPECT--
===DONE===