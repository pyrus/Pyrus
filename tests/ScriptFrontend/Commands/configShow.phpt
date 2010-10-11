--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::configShow()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$a = \PEAR2\Pyrus\Config::singleton();
$a->ext_dir = TESTDIR . DIRECTORY_SEPARATOR . 'ext';
$a->bin_dir = TESTDIR . DIRECTORY_SEPARATOR . 'bin';
$a->my_pear_path = TESTDIR;

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'config-show'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . TESTDIR . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 =
   "System paths:\n"
 . "  php_dir => " . TESTDIR . "${d}php\n"
 . "  ext_dir => " . TESTDIR . "${d}ext\n"
 . "  cfg_dir => " . TESTDIR . "${d}cfg\n"
 . "  doc_dir => " . TESTDIR . "${d}docs\n"
 . "  bin_dir => " . TESTDIR . "${d}bin\n"
 . "  data_dir => " . TESTDIR . "${d}data\n"
 . "  www_dir => " . TESTDIR . "${d}www\n"
 . "  test_dir => " . TESTDIR . "${d}tests\n"
 . "  src_dir => " . TESTDIR . "${d}src\n"
 . "  php_bin => " . $a->php_bin . "\n"
 . "  php_ini => " . php_ini_loaded_file() . "\n"
 . "  php_prefix => \n"
 . "  php_suffix => \n"
 . "Custom System paths:\n"
 . "User config (from " . TESTDIR . "${d}plugins${d}foo.xml):\n"
 . "  default_channel => pear2.php.net\n"
 . "  auto_discover => 0\n"
 . "  http_proxy => \n"
 . "  cache_dir => " . TESTDIR . "${d}cache\n"
 . "  temp_dir => " . TESTDIR . "${d}temp\n"
 . "  verbose => 1\n"
 . "  preferred_state => stable\n"
 . "  umask => 0022\n"
 . "  cache_ttl => 3600\n"
 . "  my_pear_path => " . TESTDIR . "\n"
 . "  plugins_dir => " . TESTDIR . "${d}plugins\n"
 . "(variables specific to pear2.php.net):\n"
 . "  username => \n"
 . "  password => \n"
 . "  preferred_mirror => pear2.php.net\n"
 . "  download_dir => " . TESTDIR . "${d}downloads\n"
 . "  openssl_cert => \n"
 . "  handle => \n"
 . "  paranoia => 2\n"
 . "Custom User config (from " . TESTDIR . "${d}plugins${d}foo.xml):\n"
 . "(variables specific to pear2.php.net):\n";

$test->assertEquals($help1 . $help2,
                    $contents,
                    'help output');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===