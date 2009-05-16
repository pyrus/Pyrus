--TEST--
PEAR2_Pyrus_Package::parsePackageDescription()
--FILE--
<?php
include __DIR__ . '/setup.minimal.php.inc';
mkdir($d = __DIR__ . '/testit');
file_put_contents($d . '/foob', '<?xml version="1.0" ?>
                  <package version="2.0">');

$test->assertEquals('PEAR2_Pyrus_Package_Xml', PEAR2_Pyrus_Package::parsePackageDescription($d . '/foob'),
                    'no extension, xml');

file_put_contents($d . '/foob', 'not xml');

$test->assertEquals('PEAR2_Pyrus_Package_Phar', PEAR2_Pyrus_Package::parsePackageDescription($d . '/foob'),
                    'no extension, not xml');

$test->assertEquals('PEAR2_Pyrus_Package_Remote', PEAR2_Pyrus_Package::parsePackageDescription('http://blah'),
                    'http');

$test->assertEquals('PEAR2_Pyrus_Package_Remote', PEAR2_Pyrus_Package::parsePackageDescription('https://blah'),
                    'https');

$test->assertEquals('PEAR2_Pyrus_Package_Remote', PEAR2_Pyrus_Package::parsePackageDescription('foob', true),
                    'force remote');

try {
    PEAR2_Pyrus_Package::parsePackageDescription('Yorng/$%#');
    throw new Exception('should have failed');
} catch (PEAR2_Pyrus_Package_Exception $e) {
    $test->assertEquals('package "Yorng/$%#" is unknown', $e->getMessage(), 'Yorng/$%%#');
    $test->assertEquals('Unable to process package name', $e->getCause()->getMessage(), 'name parse error');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===