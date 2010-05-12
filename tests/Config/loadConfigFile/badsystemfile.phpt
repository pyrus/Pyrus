--TEST--
\PEAR2\Pyrus\Config::loadConfigFile() corrupt systemfile
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path($testpath); // disable include_path cascading for simplicity
file_put_contents($testpath . '/.config', '<?xml version="1.0" ?>oops> <cra&p>; ?>');
try {
    $a = \PEAR2\Pyrus\Config::singleton($testpath, $testpath . '/plugins/blah');
    restore_include_path();
    throw new Exception('bad news - passed and should have failed');
} catch (\PEAR2\Pyrus\Config\Exception $e) {
    restore_include_path();
    $test->assertException($e, '\PEAR2\Pyrus\Config\Exception', 'Unable to parse invalid PEAR configuration at "' . $testpath . '"', 'exception');
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
