--TEST--
Sqlite3: init
--FILE--
<?php
require __DIR__ . '/../../setup.php.inc';
try {
    $a = new \Pyrus\Registry\Sqlite3(false, true);
    throw new Exception('should fail, didn\'t');
} catch (\Pyrus\Registry\Exception $e) {
    $test->assertEquals('Cannot create SQLite3 registry, registry is read-only', $e->getMessage(), 'message');
}

class r extends \Pyrus\Registry\Sqlite3
{
    static public $databases = array();
}

$a = new r(false);
$b = new r(false);
$test->assertEquals($a::$databases, $b::$databases, 'ensure they the same');
$test->assertEquals($a->getDatabase(), $b->getDatabase(), 'another test');
$test->assertEquals(':memory:', $a->getDatabase(), 'last one');
?>
===DONE===
--EXPECT--
===DONE===