--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::install(), basic test
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'install', __DIR__ . '/packages/Net_URL-1.0.15.tgz'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Installed pear.php.net/Net_URL-1.0.15' . "\n",
                    $contents,
                    'list packages');
                    
$test->assertFileExists(__DIR__ . '/testit/docs/Net_URL/Net/Docs/example.php', 'Docs installed to old doc directory.');


?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===