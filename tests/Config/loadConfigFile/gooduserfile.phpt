--TEST--
\Pyrus\Config::loadConfigFile() good userfile
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(''); // disable include_path cascading for simplicity
file_put_contents($testpath . '/.config', '<?xml version="1.0" ?>
<c>
 <!-- make sure php_dir and data_dir are not processed -->
 <php_dir>oops</php_dir>
 <data_dir>I did it again</data_dir>
 <ext_dir>@php_dir@/foo</ext_dir>
 <doc_dir>@php_dir@/bah</doc_dir>
 <bin_dir>@php_dir@/bar</bin_dir>
 <www_dir>@php_dir@/boo</www_dir>
 <test_dir>@php_dir@/blah</test_dir>
 <php_bin>/path/to/php</php_bin>
 <php_ini>/path/to/php.ini</php_ini>
 <unknown>ha!</unknown>
</c>');
file_put_contents($testpath . '/blah', '<?xml version="1.0" ?>
<c>
 <default_channel>pear.poo.net</default_channel>
 <preferred_mirror><pearDOTpooDOTnet>pear.poo.de</pearDOTpooDOTnet></preferred_mirror>
 <auto_discover>2</auto_discover>
 <http_proxy>hi</http_proxy>
 <cache_dir>/path/tomy/dir</cache_dir>
 <temp_dir>/path/to/temp</temp_dir>
 <download_dir><pearDOTpooDOTnet>/download</pearDOTpooDOTnet></download_dir>
 <username><pearDOTpooDOTnet>boo</pearDOTpooDOTnet></username>
 <password><pearDOTpooDOTnet>ya</pearDOTpooDOTnet></password>
 <verbose>5</verbose>
 <preferred_state>beta</preferred_state>
 <umask>' . 0642 . '</umask>
 <cache_ttl>1</cache_ttl>
 <openssl_cert><pearDOTpooDOTnet>/path/to/buh</pearDOTpooDOTnet></openssl_cert>
 <handle><pearDOTpooDOTnet>buh</pearDOTpooDOTnet></handle>
 <my_pear_path>' . $testpath . '</my_pear_path>
 <unknown>ha!</unknown>
</c>');
$a = $configclass::singleton($testpath, $testpath . '/blah');
$test->assertEquals($testpath, $a->path, 'peardir');
$test->assertEquals('pear.poo.net', $a->default_channel, 'default_channel');
$test->assertEquals('pear.poo.de', $a->preferred_mirror, 'preferred_mirror');
$test->assertEquals('2', $a->auto_discover, 'auto_discover');
$test->assertEquals('hi', $a->http_proxy, 'http_proxy');
$test->assertEquals('/path/tomy/dir', $a->cache_dir, 'cache_dir');
$test->assertEquals('/path/to/temp', $a->temp_dir, 'temp_dir');
$test->assertEquals('/download', $a->download_dir, 'download_dir');
$test->assertEquals('boo', $a->username, 'username');
$test->assertEquals('ya', $a->password, 'password');
$test->assertEquals('5', $a->verbose, 'verbose');
$test->assertEquals('beta', $a->preferred_state, 'preferred_state');
$test->assertEquals((string) 0642, $a->umask, 'umask');
$test->assertEquals('1', $a->cache_ttl, 'cache_ttl');
$test->assertEquals('/path/to/buh', $a->openssl_cert, 'openssl_cert');
$test->assertEquals('buh', $a->handle, 'handle');
$test->assertEquals($testpath, $a->my_pear_path, 'my_pear_path');
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
