--TEST--
\Pyrus\Package::parsePackageDescription()
--FILE--
<?php
include __DIR__ . '/setup.minimal.php.inc';
@mkdir($d = TESTDIR);
file_put_contents($d . '/foob', '<?xml version="1.0" ?>
                  <package version="2.0">');

$test->assertEquals(array('Pyrus\Package\Xml', $d . '/foob', false),
                    \Pyrus\Package::parsePackageDescription($d . '/foob'),
                    'no extension, xml');

file_put_contents($d . '/foob', 'not xml');

$test->assertEquals(array('Pyrus\Package\Phar', $d . '/foob', false),
                    \Pyrus\Package::parsePackageDescription($d . '/foob'),
                    'no extension, not xml');

$test->assertEquals(array('Pyrus\Package\Remote', 'http://blah', false),
                    \Pyrus\Package::parsePackageDescription('http://blah'),
                    'http');

$test->assertEquals(array('Pyrus\Package\Remote', 'https://blah', false),
                    \Pyrus\Package::parsePackageDescription('https://blah'),
                    'https');

$test->assertEquals(array('Pyrus\Package\Remote', 'foob', false),
                    \Pyrus\Package::parsePackageDescription('foob', true),
                    'force remote');

try {
    \Pyrus\Package::parsePackageDescription('Yorng/$%#');
    throw new Exception('should have failed');
} catch (\Pyrus\Package\Exception $e) {
    $test->assertEquals('package "Yorng/$%#" is unknown', $e->getMessage(), 'Yorng/$%%#');
    $test->assertEquals('Unable to process package name', $e->getPrevious()->getMessage(), 'name parse error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===