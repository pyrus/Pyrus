--TEST--
PackageFile v2: test package.xml toArray(), multiple <group> dependencies
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$c = getTestConfig();
require __DIR__ . '/../setupFiles/setupPackageFile.php.inc';
$reg = $package; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/toarray.multiplegroupdep.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===