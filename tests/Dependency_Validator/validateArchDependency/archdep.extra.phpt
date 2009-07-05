--TEST--
Dependency_Validator: arch dependency extra tidbits
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$arch = $fake->dependencies['required']->arch;
$os->pattern = 'goo';
$validator = new test_Validator($package, \pear2\Pyrus\Validate::UNINSTALLING, $errs);
// verify that we pass even with a conflict if we aren't installing or downloading
$test->assertEquals(true, $validator->validateArchDependency($os), 'UNINSTALLING');
$test->assertEquals(0, count($errs), 'UNINSTALLING count');

?>
===DONE===
--EXPECT--
===DONE===