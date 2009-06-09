--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::help()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(__DIR__ . '/testit');
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands(true);
$cli->run($args = array (0 => 'help'));

$contents = ob_get_contents();
ob_end_clean();
restore_include_path();
$help1 = 'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n";
$help2 =
'
Pyrus, the installer for PEAR2

Usage:
  php help.php [/path/to/pear] [options]
  php help.php [/path/to/pear] [options] <command> [options] [args]

Options:
  -v, --verbose  increase verbosity
  -h, --help     show this help message and exit
  --version      show the program version and exit

Commands:
  install           Install a package.  Use install --plugin to install
                    plugins
  upgrade           Upgrade a package.  Use upgrade --plugin to upgrade
                    plugins
  uninstall         Uninstall a package.  Use uninstall --plugin to
                    uninstall plugins
  info              Display information about a package
  build             Build a PHP extension package from source and install
                    the compiled extension
  list-upgrades     List packages with upgrades available
  download          Download a remote package to the current directory
  list-packages     List all installed packages in all channels
  list-channels     List all discovered channels
  channel-discover  Discover a new channel
  channel-add       Add a new channel to the registry
  channel-del       Remove a channel from the registry
  upgrade-registry  Upgrade an old PEAR installation to the new registry
                    format
  run-scripts       Run all post-install scripts for a package
  config-show       Show all configuration values
  set               Set a configuration value
  mypear            Set a configuration value
  help              Get help on a particular command, or all commands

';
$test->assertEquals($help1 . $help2,
                    $contents,
                    'help output');

ob_start();
$cli->run($args = array ());

$contents = ob_get_contents();
ob_end_clean();

$test->assertEquals($help1 . $help2, $contents, 'no args help');
ob_start();
$cli->run($args = array ('fooburp'));

$contents = ob_get_contents();
ob_end_clean();

$test->assertEquals($help1 .
                    'Unknown command: fooburp' . "\n" . $help2, $contents, 'unknown command help');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===