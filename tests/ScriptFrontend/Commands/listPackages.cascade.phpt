--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::listPackages(), cascading include_path
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . '/testit');
mkdir(__DIR__ . '/testit/php');
chdir(__DIR__ . '/testit');

set_include_path(__DIR__ . '/testit/php' . PATH_SEPARATOR . __DIR__ . '/listPackages.pear1');
\pear2\Pyrus\Config::singleton(false, __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();

ob_start();
$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array ('list-packages'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installations found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit:' .
                    __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . ']:' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1' . ']:' . "\n"
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