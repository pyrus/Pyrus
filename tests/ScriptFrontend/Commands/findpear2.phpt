--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::_findPEAR test 2: no userfile detected
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
touch(__DIR__ . '/testfoo');

test_scriptfrontend::$stdin = array(
    'yes', // answer to "It appears you have not used Pyrus before, welcome!  Initialize install?"
    __DIR__ . '/testfoo', // answer to "Where would you like to install packages by default?"
    __DIR__ . '/testit2', // answer to "Where would you like to install packages by default?"
    'no',  // answer to "Create it?"
    __DIR__ . '/testit', // answer to "Where would you like to install packages by default?"
    'yes', // answer to "Create it?"
    ''     // continue
);
$cli = new test_scriptfrontend();

set_include_path(__DIR__ . '/testit');
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
'  ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . "foo.xml\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : " . __DIR__ . DIRECTORY_SEPARATOR . "testfoo\n" .
"You have chosen:\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testfoo\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testfoo exists, and is not a directory\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : " . __DIR__ . DIRECTORY_SEPARATOR . "testit2\n" .
"You have chosen:\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testit2\n" .
" this path does not yet exist\n" .
"Create it?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : no\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : " . __DIR__ . DIRECTORY_SEPARATOR . "testit\n" .
"You have chosen:\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testit\n" .
" this path does not yet exist\n" .
"Create it?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : yes\n" .
"Thank you, enjoy using Pyrus\n" .
"Documentation is at http://pear.php.net\n" .
'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n" 
;
$help2 =
'
Pyrus, the installer for PEAR2

Usage:
  php findpear2.php [/path/to/pear] [options]
  php findpear2.php [/path/to/pear] [options] <command> [options] [args]

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
                    'initialize choice');
?>
===DONE===
--CLEAN--
<?php
unlink(__DIR__ . '/testfoo');
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===