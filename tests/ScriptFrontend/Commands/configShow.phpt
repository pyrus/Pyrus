--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::configShow()
--FILE--
<?php
set_include_path(dirname(__FILE__).'/testit');
define('MYDIR', __DIR__);
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$a = PEAR2_Pyrus_Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/foo.xml');
$a->ext_dir = __DIR__ . '/testit/ext';
$a->bin_dir = __DIR__ . '/testit/bin';
file_put_contents(__DIR__ . '/testit/foo.xml', '<pearconfig version="1.0"></pearconfig>');

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'config-show'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 =
   "System paths:\n"
 . "  php_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}src\n"
 . "  ext_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}ext\n"
 . "  cfg_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}cfg\n"
 . "  doc_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}docs\n"
 . "  bin_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}bin\n"
 . "  data_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}data\n"
 . "  www_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}www\n"
 . "  test_dir => " . __DIR__ . DIRECTORY_SEPARATOR . "testit${d}tests\n"
 . "  php_bin => " . $a->php_bin . "\n"
 . "  php_ini => " . php_ini_loaded_file() . "\n"
 . "Custom System paths:\n"
 . "User config (from " . __DIR__ . "${d}testit${d}foo.xml):\n"
 . "  default_channel => pear2.php.net\n"
 . "  preferred_mirror => pear2.php.net\n"
 . "  auto_discover => 0\n"
 . "  http_proxy => \n"
 . "  cache_dir => " . __DIR__ . "${d}testit${d}cache\n"
 . "  temp_dir => " . __DIR__ . "${d}testit${d}temp\n"
 . "  download_dir => " . __DIR__ . "${d}testit${d}downloads\n"
 . "  username => \n"
 . "  password => \n"
 . "  verbose => 1\n"
 . "  preferred_state => stable\n"
 . "  umask => 0022\n"
 . "  cache_ttl => 3600\n"
 . "  sig_type => \n"
 . "  sig_bin => \n"
 . "  sig_keyid => \n"
 . "  sig_keydir => \n"
 . "  my_pear_path => " . __DIR__ . DIRECTORY_SEPARATOR . "testit\n"
 . "  plugins_dir => " . __DIR__ . "${d}testit\n"
 . "Custom User config (from " . __DIR__ . "${d}testit${d}foo.xml):\n";

$test->assertEquals($help1 . $help2,
                    $contents,
                    'help output');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===