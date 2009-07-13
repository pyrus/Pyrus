--TEST--
\pear2\Pyrus\ChannelRegistry\Base extra coverage
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$creg = new \pear2\Pyrus\ChannelRegistry\Sqlite3(__DIR__ . '/testit');

$test->assertEquals(__DIR__ . '/testit', $creg->getPath(), 'getPath');

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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===