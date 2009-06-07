--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::download()
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

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.explicitstate',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';

mkdir(__DIR__ . '/testit/oksavehere');
chdir(__DIR__ . '/testit/oksavehere');
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'download', __DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P1-1.1.0RC1.tar',
                                'P2-beta', 'P1', 'unknown', 'P3-1.1.0RC2'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . "failed to init unknown for download (package unknown does not exist)\n"
                    . 'Downloading ' . __DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P1-1.1.0RC1.tar...' .
                                "done (" . __DIR__ . DIRECTORY_SEPARATOR . 'testit' .
                                DIRECTORY_SEPARATOR . 'oksavehere' . DIRECTORY_SEPARATOR . "P1-1.1.0RC1.tar)\n"
                    . 'Downloading P2-beta...' .
                                "done (" . __DIR__ . DIRECTORY_SEPARATOR . 'testit' .
                                DIRECTORY_SEPARATOR . 'oksavehere' . DIRECTORY_SEPARATOR . "P2-1.1.0RC3.tgz)\n"
                    . 'Downloading P1...' .
                                "done (" . __DIR__ . DIRECTORY_SEPARATOR . 'testit' .
                                DIRECTORY_SEPARATOR . 'oksavehere' . DIRECTORY_SEPARATOR . "P1-1.0.0.tgz)\n"
                    . 'Downloading P3-1.1.0RC2...' .
                                "failed! (Could not download abstract package pear2.php.net/P3)\n",
                     $contents,
                    'list packages');

$test->assertFileExists(__DIR__ . '/testit/oksavehere/P1-1.1.0RC1.tar', 'P1 tarball');
$test->assertFileExists(__DIR__ . '/testit/oksavehere/P2-1.1.0RC3.tgz', 'P2-beta');
$test->assertFileExists(__DIR__ . '/testit/oksavehere/P1-1.0.0.tgz', 'P1');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===