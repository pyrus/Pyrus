--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::install() --optionaldeps
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
require __DIR__ . '/setup.php.inc';
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.optionaldeps',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'install', '--optionaldeps', 'pear2/P1'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n" .
'

Installed pear2.php.net/P1-1.0.0
Installed pear2.php.net/P2-1.0.0
Installed pear2.php.net/P3-1.0.0
Installed pear2.php.net/P4-1.0.0
',                     $contents,
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