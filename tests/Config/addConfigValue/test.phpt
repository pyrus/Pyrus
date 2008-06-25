--TEST--
PEAR2_Pyrus_Config::addConfigValue()
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path(''); // disable include_path cascading for simplicity
$a = $configclass::singleton($testpath, $testpath . '/blah');
$a->addConfigValue('foo', 'booya');
$a->addConfigValue('foo2', 'booya2', false);
$test->assertEquals('booya', $a->foo, 'foo');
$test->assertEquals('booya2', $a->foo2, 'foo');
$a->foo = 'hi';
$a->foo2 = 'hi2';
$test->assertEquals('hi', $a->foo, 'foo');
$test->assertEquals('hi2', $a->foo2, 'foo2');
$d = DIRECTORY_SEPARATOR;
$test->assertEquals('configsnapshot-' . date('Y-m-d H:i:s') . '.xml', $a->configSnapshot(), 1);

$test->assertEquals('<?xml version="1.0"?>
<pearconfig version="1.0"><php_dir>' .
    $a->php_dir . '</php_dir><ext_dir>' .
    $a->ext_dir . '</ext_dir><cfg_dir>' .
    $a->cfg_dir . '</cfg_dir><doc_dir>' .
    $a->doc_dir . '</doc_dir><bin_dir>' .
    $a->bin_dir . '</bin_dir><data_dir>' .
    $a->data_dir . '</data_dir><www_dir>' .
    $a->www_dir . '</www_dir><test_dir>' .
    $a->test_dir . '</test_dir><php_bin>' .
    $a->php_bin . '</php_bin><php_ini>' .
    $a->php_ini . '</php_ini><foo>' .
    $a->foo . '</foo></pearconfig>
', file_get_contents($cdir . '/configsnapshot-' . date('Y-m-d H:i:s') . '.xml'), 'contents 1');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
