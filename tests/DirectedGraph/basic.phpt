--TEST--
Pyrus DirectedGraph: basic topological sort
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$a1 = new stdClass;
$a2 = new stdClass;
$a3 = new stdClass;
$a4 = new stdClass;
$a5 = new stdClass;
$digraph->add($a1);
$digraph->add($a2);
$digraph->add($a3);
$digraph->add($a4);
$digraph->add($a5);

$digraph->connect($a1, $a2);
$digraph->connect($a1, $a3);
$digraph->connect($a2, $a3);
$digraph->connect($a2, $a5);
$digraph->connect($a4, $a2);
$digraph->connect($a4, $a1);
$digraph->connect($a4, $a5);
$digraph->connect($a5, $a3);

$res = array();
foreach ($digraph as $val) {
    $res[] = $val;
}
$test->assertEquals(array($a3, $a5, $a2, $a1, $a4), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===