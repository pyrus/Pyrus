--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::help()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(__DIR__ . '/testit');
ob_start();
$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);
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
  -v, --verbose   increase verbosity
  -p, --paranoid  set or increase paranoia level
  -h, --help      show this help message and exit
  --version       show the program version and exit

Commands:
  install           Install a package.  Use install --plugin to install
                    plugins (alias: i)
  upgrade           Upgrade a package.  Use upgrade --plugin to upgrade
                    plugins (alias: up)
  uninstall         Uninstall a package.  Use uninstall --plugin to
                    uninstall plugins (alias: un)
  info              Display information about a package (alias: in)
  build             Build a PHP extension package from source and install
                    the compiled extension (alias: b)
  list-upgrades     List packages with upgrades available (alias: lu)
  remote-list       List all remote packages in a channel, organized by
                    category (alias: rd)
  download          Download a remote package to the current directory
                    (alias: d)
  list-packages     List all installed packages in all channels (alias: l)
  list-channels     List all discovered channels (alias: lc)
  channel-discover  Discover a new channel (alias: di)
  channel-add       Add a new channel to the registry (alias: ca)
  channel-del       Remove a channel from the registry (alias: cd)
  upgrade-registry  Upgrade an old PEAR installation to the new registry
                    format (alias: ur)
  run-scripts       Run all post-install scripts for a package (alias: r)
  config-show       Show all configuration values (alias: cs)
  set               Set a configuration value (alias: set)
  mypear            Set a configuration value (alias: m)
  help              Get help on a particular command, or all commands
                    (alias: h)
  make              Create or update a package.xml from a standard PEAR2
                    directory layout (alias: mk)
  pickle            Create or update a package.xml and then package a PECL
                    extension release (alias: pi)
  package           Create a release from an existing package.xml (alias:
                    p)
  run-phpt          Run PHPT tests (alias: rp)

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