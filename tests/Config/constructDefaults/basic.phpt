--TEST--
PEAR2_Pyrus_Config::constructDefaults() basic test
--INI--
extension_dir=
--ENV--
PATH=.
PHP_PEAR_BIN_DIR=
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertEquals(array(
            'php_dir' => '@php_dir@/src', // pseudo-value in this implementation
            'ext_dir' => '@php_dir@/ext_dir',
            'doc_dir' => '@php_dir@/docs',
            'bin_dir' => PHP_BINDIR,
            'data_dir' => '@php_dir@/data', // pseudo-value in this implementation
            'cfg_dir' => '@php_dir@/cfg',
            'www_dir' => '@php_dir@/www',
            'test_dir' => '@php_dir@/tests',
            'php_bin' => '',
            'php_ini' => '',
            'default_channel' => 'pear2.php.net',
            'preferred_mirror' => 'pear2.php.net',
            'auto_discover' => 0,
            'http_proxy' => '',
            'cache_dir' => '@php_dir@/cache',
            'temp_dir' => '@php_dir@/temp',
            'download_dir' => '@php_dir@/downloads',
            'username' => '',
            'password' => '',
            'verbose' => 1,
            'preferred_state' => 'stable',
            'umask' => '0022',
            'cache_ttl' => 3600,
            'sig_type' => '',
            'sig_bin' => '',
            'sig_keyid' => '',
            'sig_keydir' => '',
            'my_pear_path' => '@php_dir@',
        ), tc::getTestDefaults(), 'before init');
tc::constructDefaults();
$test->assertEquals(array(
            'php_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'src',
            'ext_dir' => PEAR_EXTENSION_DIR,
            'doc_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'docs',
            'bin_dir' => PHP_BINDIR,
            'data_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'data', // pseudo-value in this implementation
            'cfg_dir' => '@php_dir@/cfg',
            'www_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'www',
            'test_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'tests',
            'php_bin' => '',
            'default_channel' => 'pear2.php.net',
            'preferred_mirror' => 'pear2.php.net',
            'auto_discover' => '0',
            'http_proxy' => '',
            'cache_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'cache',
            'temp_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'temp',
            'download_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'downloads',
            'username' => '',
            'password' => '',
            'verbose' => '1',
            'preferred_state' => 'stable',
            'umask' => '0022',
            'cache_ttl' => '3600',
            'sig_type' => '',
            'sig_bin' => '',
            'sig_keyid' => '',
            'sig_keydir' => '',
            'my_pear_path' => '@php_dir@',
        ), tc::getTestDefaults(true), 'after');
$phpini = tc::getTestDefaults();
$test->assertRegex('/\.ini/', $phpini['php_ini'], 'php_ini');
// increase code coverage
$c = PEAR2_Pyrus_Config::current();
$test->assertSame($c, PEAR2_Pyrus_Config::singleton());
?>
===DONE===
--EXPECT--
===DONE===
