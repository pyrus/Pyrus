--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::listPackages(), existing PEAR1 registry, packages installed
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/listPackages.pear1', __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();

$cli = new PEAR2_Pyrus_ScriptFrontend_Commands(true);

ob_start();
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
$cli->run($args = array (__DIR__.'/listPackages.pear1', 'list-packages'));
restore_include_path();

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'listPackages.pear1' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1]:' . "\n"
                    . "[channel pear.php.net]:\n"
                    . " PEAR\n"
                    . " PHP_Archive\n"
                    . " Console_Getopt\n"
                    . " xdebug\n"
                    . " Structures_Graph\n"
                    . " Archive_Tar\n"
                    . " XML_Util\n"
                    . " Text_Diff\n",
                    $contents,
                    'list packages');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===