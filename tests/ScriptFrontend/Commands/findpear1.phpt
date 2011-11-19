--TEST--
\Pyrus\ScriptFrontend\Commands::_findPEAR test 1: explicit config path
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'help'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . TESTDIR . "\n";
$help2 =
'
Pyrus, the PHP manager

Usage:
  php findpear1.php [/path/to/pyrus] [options]
  php findpear1.php [/path/to/pyrus] [options] <command> [options] [args]

Options:
  -v, --verbose   increase verbosity
  -p, --paranoid  set or increase paranoia level
  -h, --help      show this help message and exit
  --version       show the program version and exit

Commands:
  install             Install a package.  Use install --plugin to install
                      plugins
  upgrade             Upgrade a package.  Use upgrade --plugin to upgrade
                      plugins
  uninstall           Uninstall a package.  Use uninstall --plugin to
                      uninstall plugins
  info                Display information about a package
  build               Build a PHP extension package from source and install
                      the compiled extension
  list-upgrades       List packages with upgrades available
  remote-list         List all remote packages in a channel, organized by
                      category
  download            Download a remote package to the current directory
  list-packages       List all installed packages in all channels
  list-channels       List all discovered channels
  channel-discover    Discover a new channel
  channel-del         Remove a channel from the registry
  upgrade-registry    Upgrade an old PEAR installation to the new registry
                      format
  run-scripts         Run all post-install scripts for a package
  set                 Set a configuration value
  get                 Get configuration value(s). Leave blank for all
                      values
  mypear              Set a configuration value
  help                Get help on a particular command, or all commands
  search              Search a registry of PEAR channels for packages
  make                Create or update a package.xml from a standard PEAR2
                      directory layout
  pickle              Create or update a package.xml and then package a
                      PECL extension release
  package             Create a release from an existing package.xml
  run-phpt            Run PHPT tests
  generate-pear2      Generate the source layout for a new
                      Pyrus-installable package
  generate-ext        Generate the source layout for a new PHP extension
                      that is PECL-ready
  scs-update          Simple channel server: Update all releases of a
                      within the get/ directory.
  scs-create          Simple channel server: Create a channel.xml, get/ and
                      rest/ directory for a channel
  scs-add-maintainer  Simple Channel Server: Add a new maintaing developer
                      to the channel
  scs-add-category    Simple Channel Server: Add a new category to the
                      channel
  scs-categorize      Simple Channel Server: Categorize a package
  scs-release         Simple Channel Server: Release a package

';
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