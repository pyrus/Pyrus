--TEST--
\Pyrus\ScriptFrontend\Commands::help() with specific command help requested
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(TESTDIR);
ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (0 => 'help', 'install'));

$contents = ob_get_contents();
ob_end_clean();
restore_include_path();
$help1 = 'Using PEAR installation found at ' . TESTDIR . "\n";
$help2 =
'
Install a package.  Use install --plugin to install plugins

Usage:
  php help.command.php [options] install [options] <package...>

Options:
  -p, --plugin                                     Manage plugin
                                                   installation only
  -r packagingroot, --packagingroot=packagingroot  Install the package in a
                                                   directory in preparation
                                                   for packaging with tools
                                                   like RPM
  -o, --optionaldeps                               Automatically download
                                                   and install all optional
                                                   dependencies
  -f, --force                                      Force the installation
                                                   to proceed independent
                                                   of errors.  USE
                                                   SPARINGLY.

Arguments:
  package  package.xml, local package archive, remove package archive, or
           abstract package.


Installs listed packages.

local package.xml example:
php pyrus.phar install package.xml

local package archive example:
php pyrus.phar install PackageName-1.2.0.tar

remote package archive example:
php pyrus.phar install http://www.example.com/PackageName-1.2.0.tgz

Examples of an abstract package:
php pyrus.phar install PackageName
 installs PackageName from the default channel with stability preferred_state
php pyrus.phar pear/PackageName
 installs PackageName from the pear.php.net channel with stability preferred_state
php pyrus.phar install channel://doc.php.net/PackageName
 installs PackageName from the doc.php.net channel with stability preferred_state
php pyrus.phar install PackageName-beta
 installs PackageName from the default channel, beta or stable stability
php pyrus.phar install PackageName-1.2.0
 installs PackageName from the default channel, version 1.2.0

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