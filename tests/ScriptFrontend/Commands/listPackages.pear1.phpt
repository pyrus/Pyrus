--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::listPackages(), existing PEAR1 registry, packages installed
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
$c = \pear2\Pyrus\Config::singleton(__DIR__.'/listPackages.pear1', __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();

$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);

ob_start();
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
$cli->run($args = array (__DIR__.'/listPackages.pear1', 'list-packages'));
restore_include_path();

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'listPackages.pear1' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1]:' . "\n"
                    . "[channel pear.php.net]:\n"
                    . ' Archive_Tar
 Console_Getopt
 PEAR
 PHP_Archive
 Structures_Graph
 Text_Diff
 xdebug
 XML_Util',
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