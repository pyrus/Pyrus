--TEST--
Dependency_Validator: subpackage dependency, conflicts, installed failure --force
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

\PEAR2\Pyrus\Main::$options['force'] = true;
$fake = new \PEAR2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
\PEAR2\Pyrus\Config::current()->registry->install($fake);

$foo = $fake->dependencies['required']->subpackage['pear2.php.net/foo']->conflicts(true);

$test->assertEquals(true, $validator->validateSubpackageDependency($foo, array()), 'foo');
$test->assertEquals(1, count($errs->E_WARNING), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('warning: channel://pear2.php.net/test conflicts with package "channel://pear2.php.net/foo", installed version is 1.2.3', $errs->E_WARNING[0]->getMessage(), 'foo error');

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, \PEAR2\Pyrus\Validate::DOWNLOADING, $errs);

\PEAR2\Pyrus\Config::current()->registry->uninstall('foo', 'pear2.php.net');

$test->assertEquals(true, $validator->validateSubpackageDependency($foo, array($fake)), 'foo downloading');
$test->assertEquals(1, count($errs->E_WARNING), 'foo downloading count');
$test->assertEquals(1, count($errs), 'foo downloading count 2');
$test->assertEquals('warning: channel://pear2.php.net/test conflicts with package "channel://pear2.php.net/foo", downloaded version is 1.2.3', $errs->E_WARNING[0]->getMessage(), 'foo downloading error');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===