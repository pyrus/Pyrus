--TEST--
\Pyrus\ScriptFrontend\Commands::_findPEAR test 2: no userfile detected
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
if (file_exists(TESTDIR . DIRECTORY_SEPARATOR . 'testit')) {
    include __DIR__ . '/../../clean.php.inc';
}
touch(TESTDIR . '/testfoo');

test_scriptfrontend::$stdin = array(
    'yes', // answer to "It appears you have not used Pyrus before, welcome!  Initialize install?"
    TESTDIR . DIRECTORY_SEPARATOR . 'testfoo', // answer to "Where would you like to install packages by default?"
    TESTDIR . DIRECTORY_SEPARATOR . 'testit2', // answer to "Where would you like to install packages by default?"
    'no',  // answer to "Create it?"
    TESTDIR . DIRECTORY_SEPARATOR . 'testit', // answer to "Where would you like to install packages by default?"
    'yes', // answer to "Create it?"
    ''     // continue
);
$cli = new test_scriptfrontend();

set_include_path(TESTDIR . '/testit');
ob_start();
$cli->run($args = array ());
$contents = ob_get_contents();
ob_end_clean();
restore_include_path();

$help1 = "Pyrus: No user configuration file detected\n" .
"It appears you have not used Pyrus before, welcome!  Initialize install?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : yes\n" .
"Great.  We will store your configuration in:\n" .
'  ' . TESTDIR . DIRECTORY_SEPARATOR . "foo.xml\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : " . TESTDIR . DIRECTORY_SEPARATOR . "testfoo\n" .
"You have chosen:\n" .
TESTDIR . DIRECTORY_SEPARATOR . "testfoo\n" .
TESTDIR . DIRECTORY_SEPARATOR . "testfoo exists, and is not a directory\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : " . TESTDIR . DIRECTORY_SEPARATOR . "testit2\n" .
"You have chosen:\n" .
TESTDIR . DIRECTORY_SEPARATOR . "testit2\n" .
" this path does not yet exist\n" .
"Create it?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : no\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : " . TESTDIR . DIRECTORY_SEPARATOR . "testit\n" .
"You have chosen:\n" .
TESTDIR . DIRECTORY_SEPARATOR . "testit\n" .
" this path does not yet exist\n" .
"Create it?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : yes\n" .
"Thank you, enjoy using Pyrus\n" .
"Documentation is at http://pear.php.net\n" .
'Using PEAR installation found at ' . TESTDIR . DIRECTORY_SEPARATOR . 'testit' . "\n"
;
$help2 =
'
Pyrus, the installer for PEAR2

Usage:
  php findpear2.php [/path/to/pyrus] [options]
  php findpear2.php [/path/to/pyrus] [options] <command> [options] [args]

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
  make                Create or update a package.xml from a standard PEAR2
                      directory layout
  pickle              Create or update a package.xml and then package a
                      PECL extension release
  package             Create a release from an existing package.xml
  run-phpt            Run PHPT tests
  generate-pear2      Generate the subversion source layout for a new PEAR2
                      package
  generate-ext        Generate the subversion source layout for a new PHP
                      extension that is PECL-ready
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
                    'initialize choice');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===