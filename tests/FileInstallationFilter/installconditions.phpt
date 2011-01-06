--TEST--
FileInstallationFilter: verify installconditions modifies files correctly
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';

$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleApp/package.xml');

$files = array();
foreach ($package->getInstallContents() as $file) {
    $files[] = $file;
}

// Assume we're on windows
$correct_file = 'pake.bat';

if (substr(PHP_OS, 0, 3) != 'WIN') {
    // The correct script file should be pake.sh
    $correct_file = 'pake.sh';
}
$test->assertEquals(1, count($files), 'correct count');
$test->assertEquals('bin/'.$correct_file, $files[0]['attribs']['name'], 'correct script file');

?>
===DONE===
--EXPECT--
===DONE===