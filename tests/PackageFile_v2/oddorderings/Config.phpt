--TEST--
PackageFile v2: test package.xml odd orderings (Config package)
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$apc = new \PEAR2\Pyrus\Package(__DIR__ . '/packages/Config/package.xml');
$test->assertEquals('Config', $apc->name, 'if we get here, all is well unless this part fails');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===