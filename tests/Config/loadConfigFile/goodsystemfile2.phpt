--TEST--
\pear2\Pyrus\Config::loadConfigFile() good systemfile 2
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path($testpath); // disable include_path cascading for simplicity
file_put_contents($testpath . '/.config', '<?xml version="1.0" ?>
<c>
 <!-- make sure php_dir and data_dir are not processed -->
 <php_dir>oops</php_dir>
 <ext_dir>@php_dir@/foo</ext_dir>
 <doc_dir>@php_dir@/bah</doc_dir>
 <bin_dir>@php_dir@/bar</bin_dir>
 <www_dir>@php_dir@/boo</www_dir>
 <test_dir>@php_dir@/blah</test_dir>
 <php_bin>/path/to/php</php_bin>
 <php_ini>/path/to/php.ini</php_ini>
 <unknown>ha!</unknown>
</c>');
$a = $configclass::singleton($testpath, $testpath . '/blah');
restore_include_path();
$test->assertEquals($testpath, $a->path, 'peardir');
$test->assertEquals($testpath . '/blah', $a->userfile, 'userfile');
$test->assertEquals($testpath . '/php', $a->php_dir, 'php_dir');
$test->assertEquals($testpath . '/data', $a->data_dir, 'data_dir');
$test->assertEquals($testpath . '/foo', $a->ext_dir, 'ext_dir');
$test->assertEquals($testpath . '/bah', $a->doc_dir, 'doc_dir');
$test->assertEquals($testpath . '/bar', $a->bin_dir, 'bin_dir');
$test->assertEquals($testpath . '/boo', $a->www_dir, 'www_dir');
$test->assertEquals($testpath . '/blah', $a->test_dir, 'test_dir');
$test->assertEquals('/path/to/php', $a->php_bin, 'php_bin');
$test->assertEquals('/path/to/php.ini', $a->php_ini, 'php_ini');
try {
    $test->assertEquals('this should NOT execute, should go to exception', $a->unknown, 'unknown');
} catch (\pear2\Pyrus\Config\Exception $e) {
    echo "here\n";
    $test->assertEquals('Unknown configuration variable "unknown" in location ' .
            $a->path, $e->getMessage(), 'exception message');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
here
===DONE===
