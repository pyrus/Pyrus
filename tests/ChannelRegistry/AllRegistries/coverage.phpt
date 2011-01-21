--TEST--
\PEAR2\Pyrus\ChannelRegistry\Base extra coverage
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
$creg = new \PEAR2\Pyrus\ChannelRegistry\Sqlite3(TESTDIR);

$test->assertEquals(TESTDIR, $creg->getPath(), 'getPath');

$inf = array();
foreach ($creg as $chan => $obj) {
    $inf[$chan] = $obj->name;
}
asort($inf);
$test->assertEquals(array (
  '__uri' => '__uri',
  'doc.php.net' => 'doc.php.net',
  'pear.php.net' => 'pear.php.net',
  'pear2.php.net' => 'pear2.php.net',
  'pecl.php.net' => 'pecl.php.net',
), $inf, 'iteration');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===