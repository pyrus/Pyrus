--TEST--
PackageFile v2: test package.xml odd orderings (Auth_Prefmanager package)
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$apc = new \PEAR2\Pyrus\Package(__DIR__ . '/packages/Auth_PrefManager/package.xml');
$test->assertEquals('Auth_PrefManager', $apc->name, 'if we get here, all is well unless this part fails');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===