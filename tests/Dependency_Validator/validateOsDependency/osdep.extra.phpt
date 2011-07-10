--TEST--
Dependency_Validator: OS dependency extra tidbits
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \Pyrus\PackageFile\v2;
$os = $fake->dependencies['required']->os;
$os->name = '*';
$validator = new test_Validator($package, $state, $errs);
$validator->os = $validator->sysname = 'Linux';
$test->assertEquals(true, $validator->validateOSDependency($os), '* pass');

$os->name = 'windows';
$validator = new test_Validator($package, \Pyrus\Validate::UNINSTALLING, $errs);
$validator->os = $validator->sysname = 'Linux';
// verify that we pass even with a conflict if we aren't installing or downloading
$test->assertEquals(true, $validator->validateOSDependency($os), 'UNINSTALLING');
$test->assertEquals(0, count($errs), 'UNINSTALLING count');

?>
===DONE===
--EXPECT--
===DONE===