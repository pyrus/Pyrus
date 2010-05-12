--TEST--
PackageFile v2: test SimpleProperty exceptions
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$package = new \PEAR2\Pyrus\PackageFile\v2;
try {
    unset($package->version['foo']);
    throw new Exception('unset(foo) worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown version property foo', $e->getMessage(), 'unset(foo)');
}

try {
    unset($package->stability['foo']);
    throw new Exception('unset(foo) stability worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown stability property foo', $e->getMessage(), 'unset(foo) stability');
}

try {
    isset($package->version['foo']);
    throw new Exception('isset(foo) worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown version property foo', $e->getMessage(), 'isset(foo)');
}

try {
    isset($package->stability['foo']);
    throw new Exception('isset(foo) stability worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown stability property foo', $e->getMessage(), 'isset(foo) stability');
}

try {
    $package->version['foo'] = '1.0.0';
    throw new Exception('foo = 1.0.0 worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown version property foo', $e->getMessage(), 'foo = 1.0.0');
}

try {
    $package->stability['foo'] = '1.0.0';
    throw new Exception('foo = 1.0.0 stability worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown stability property foo', $e->getMessage(), 'foo = 1.0.0 stability');
}

try {
    $a = $package->version['foo'];
    throw new Exception('$a = foo worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown version property foo', $e->getMessage(), '$a = foo');
}

try {
    $a = $package->stability['foo'];
    throw new Exception('$a = foo stability worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Unknown stability property foo', $e->getMessage(), '$a = foo stability');
}

try {
    $package->version['release'] = 1;
    throw new Exception('release = 1 worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Can only set version to string', $e->getMessage(), 'release = 1');
}

try {
    $package->stability['release'] = 1;
    throw new Exception('release = 1 stability worked and should not');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Can only set stability to string', $e->getMessage(), 'release = 1 stability');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===