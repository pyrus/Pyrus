--TEST--
\PEAR2\Pyrus\Config::constructDefaults() basic test
--INI--
extension_dir=
--ENV--
PATH=.
PHP_PEAR_BIN_DIR=
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertEquals(array(
            'php_dir' => '@php_dir@/php', // pseudo-value in this implementation
            'ext_dir' => '@php_dir@/ext',
            'doc_dir' => '@php_dir@/docs',
            'bin_dir' => PHP_BINDIR,
            'data_dir' => '@php_dir@/data', // pseudo-value in this implementation
            'cfg_dir' => '@php_dir@/cfg',
            'www_dir' => '@php_dir@/www',
            'test_dir' => '@php_dir@/tests',
            'src_dir' => '@php_dir@/src',
            'php_bin' => '',
            'php_prefix' => '',
            'php_suffix' => '',
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
            'paranoia' => 2,
            'preferred_state' => 'stable',
            'umask' => '0022',
            'cache_ttl' => 3600,
            'openssl_cert' => '',
            'handle' => '',
            'my_pear_path' => '@php_dir@',
            'plugins_dir' => '@default_config_dir@',
        ), tc::getTestDefaults(), 'before init');
tc::constructDefaults();
$test->assertEquals(array(
            'php_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'php',
            'ext_dir' => PEAR_EXTENSION_DIR,
            'doc_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'docs',
            'bin_dir' => PHP_BINDIR,
            'data_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'data', // pseudo-value in this implementation
            'cfg_dir' => '@php_dir@/cfg',
            'www_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'www',
            'test_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'tests',
            'src_dir' => '@php_dir@' . DIRECTORY_SEPARATOR . 'src',
            'php_bin' => \PEAR2\Pyrus\Config::current()->php_bin, // no way to reliably test this, so a cop-out
            'php_prefix' => '',
            'php_suffix' => '',
            'php_ini' => php_ini_loaded_file(),
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
            'paranoia' => '2',
            'preferred_state' => 'stable',
            'umask' => '0022',
            'cache_ttl' => '3600',
            'openssl_cert' => '',
            'handle' => '',
            'my_pear_path' => '@php_dir@',
            'plugins_dir' => '@default_config_dir@',
        ), tc::getTestDefaults(), 'after');
$phpini = tc::getTestDefaults();
$test->assertRegex('/\.ini/', $phpini['php_ini'], 'php_ini');
// increase code coverage
$c = \PEAR2\Pyrus\Config::current();
$test->assertSame($c, \PEAR2\Pyrus\Config::singleton(), 'current = singleton');
?>
===DONE===
--EXPECT--
===DONE===
