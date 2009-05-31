--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::upgrade(), basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.explicitstate',
                       'http://pear2.php.net/');
PEAR2_Pyrus_REST::$downloadClass = 'Internet';
PEAR2_Pyrus_Package_Remote::$downloadClass = 'Internet';

PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package(__DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P2-1.0.0.tar'));
PEAR2_Pyrus_Installer::commit();

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'upgrade', 'P2-beta'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Installed pear2.php.net/P2-1.1.0RC3' . "\n",
                    $contents,
                    'list packages');

$test->assertFileExists(__DIR__ . '/testit/php/glooby2', 'glooby2');
$test->assertEquals('hi',
                    file_get_contents(__DIR__ . '/testit/php/glooby2'), 'files match');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===