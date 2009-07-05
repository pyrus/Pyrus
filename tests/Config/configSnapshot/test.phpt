--TEST--
\pear2\Pyrus\Config::configSnapshot()
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path(''); // disable include_path cascading for simplicity
$a = $configclass::singleton($testpath, $testpath . '/blah');
$d = DIRECTORY_SEPARATOR;
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.xml', $a->configSnapshot(), 1);
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.xml', $a->configSnapshot(), 2);
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.xml', $a->configSnapshot(), 3);
$t = $a->test_dir;
$a->test_dir = 'hi';
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.1.xml', $a->configSnapshot(), 4);
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.1.xml', $a->configSnapshot(), 5);
$a->test_dir = $t;
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.xml', $a->configSnapshot(), 6);
$a->test_dir = 'another';
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.1.1.xml', $a->configSnapshot(), 7);

$test->assertEquals('<?xml version="1.0"?>
<pearconfig version="1.0"><php_dir>' .
    $a->php_dir . '</php_dir><ext_dir>' .
    $a->ext_dir . '</ext_dir><cfg_dir>' .
    $a->cfg_dir . '</cfg_dir><doc_dir>' .
    $a->doc_dir . '</doc_dir><bin_dir>' .
    $a->bin_dir . '</bin_dir><data_dir>' .
    $a->data_dir . '</data_dir><www_dir>' .
    $a->www_dir . '</www_dir><test_dir>' .
    $t . '</test_dir><src_dir>' .
    $a->src_dir . '</src_dir><php_bin>' .
    $a->php_bin . '</php_bin><php_ini>' .
    $a->php_ini . '</php_ini><php_prefix></php_prefix><php_suffix></php_suffix></pearconfig>
', file_get_contents($cdir . '/configsnapshot-' . date('Y-m-d H:i:s') . '.xml'), 'contents 1');
$test->assertEquals('<?xml version="1.0"?>
<pearconfig version="1.0"><php_dir>' .
    $a->php_dir . '</php_dir><ext_dir>' .
    $a->ext_dir . '</ext_dir><cfg_dir>' .
    $a->cfg_dir . '</cfg_dir><doc_dir>' .
    $a->doc_dir . '</doc_dir><bin_dir>' .
    $a->bin_dir . '</bin_dir><data_dir>' .
    $a->data_dir . '</data_dir><www_dir>' .
    $a->www_dir . '</www_dir><test_dir>' .
    'hi</test_dir><src_dir>' .
    $a->src_dir . '</src_dir><php_bin>' .
    $a->php_bin . '</php_bin><php_ini>' .
    $a->php_ini . '</php_ini><php_prefix></php_prefix><php_suffix></php_suffix></pearconfig>
', file_get_contents($cdir . '/configsnapshot-' . date('Y-m-d H:i:s') . '.1.xml'), 'contents 2');
$test->assertEquals('<?xml version="1.0"?>
<pearconfig version="1.0"><php_dir>' .
    $a->php_dir . '</php_dir><ext_dir>' .
    $a->ext_dir . '</ext_dir><cfg_dir>' .
    $a->cfg_dir . '</cfg_dir><doc_dir>' .
    $a->doc_dir . '</doc_dir><bin_dir>' .
    $a->bin_dir . '</bin_dir><data_dir>' .
    $a->data_dir . '</data_dir><www_dir>' .
    $a->www_dir . '</www_dir><test_dir>' .
    'another</test_dir><src_dir>' .
    $a->src_dir . '</src_dir><php_bin>' .
    $a->php_bin . '</php_bin><php_ini>' .
    $a->php_ini . '</php_ini><php_prefix></php_prefix><php_suffix></php_suffix></pearconfig>
', file_get_contents($cdir . '/configsnapshot-' . date('Y-m-d H:i:s') . '.1.1.xml'), 'contents 3');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
