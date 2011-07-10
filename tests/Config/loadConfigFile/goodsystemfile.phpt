--TEST--
\Pyrus\Config::loadConfigFile() good systemfile
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path($testpath); // disable include_path cascading for simplicity
file_put_contents($testpath . DIRECTORY_SEPARATOR . '.config', '<?xml version="1.0" ?>
<c>
 <!-- make sure php_dir and data_dir are not processed -->
 <data_dir>I did it again</data_dir>
 <ext_dir>@php_dir@'. DIRECTORY_SEPARATOR . 'foo</ext_dir>
 <doc_dir>@php_dir@'. DIRECTORY_SEPARATOR . 'bah</doc_dir>
 <bin_dir>@php_dir@'. DIRECTORY_SEPARATOR . 'bar</bin_dir>
 <www_dir>@php_dir@'. DIRECTORY_SEPARATOR . 'boo</www_dir>
 <test_dir>@php_dir@'. DIRECTORY_SEPARATOR . 'blah</test_dir>
 <php_bin>'. DIRECTORY_SEPARATOR . 'path'. DIRECTORY_SEPARATOR . 'to'. DIRECTORY_SEPARATOR . 'php</php_bin>
 <php_ini>'. DIRECTORY_SEPARATOR . 'path'. DIRECTORY_SEPARATOR . 'to'. DIRECTORY_SEPARATOR . 'php.ini</php_ini>
 <unknown>ha!</unknown>
</c>');
$a = $configclass::singleton($testpath, $testpath . DIRECTORY_SEPARATOR . 'blah');
restore_include_path();
$test->assertEquals($testpath, $a->location, 'peardir');
$test->assertEquals($testpath, $a->path, 'peardir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'blah', $a->userfile, 'userfile');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'php', $a->php_dir, 'php_dir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'data', $a->data_dir, 'data_dir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'foo', $a->ext_dir, 'ext_dir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'bah', $a->doc_dir, 'doc_dir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'bar', $a->bin_dir, 'bin_dir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'boo', $a->www_dir, 'www_dir');
$test->assertEquals($testpath . DIRECTORY_SEPARATOR . 'blah', $a->test_dir, 'test_dir');
$test->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, '/path/to/php'), $a->php_bin, 'php_bin');
$test->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, '/path/to/php.ini'), $a->php_ini, 'php_ini');
try {
    $test->assertEquals('this should NOT execute, should go to exception', $a->unknown, 'unknown');
} catch (\Pyrus\Config\Exception $e) {
    echo "here\n";
    $test->assertEquals('Unknown configuration variable "unknown" in location ' .
            $a->path, $e->getMessage(), 'exception message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
here
===DONE===
