--TEST--
PackageFile v2: test basic package.xml properties
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Registry/AllRegistries/package/basic/license.template';

// don't try this at home!
$newguy->fromArray(array('package' => array()));
$test->assertEquals(null, $newguy->license['name'], 'blank name');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===