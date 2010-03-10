--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::install(), --packagingroot test
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
require __DIR__ . '/setup.php.inc';
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \pear2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/blah.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

ob_start();
$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'install', __DIR__.'/../../Mocks/SimpleChannelServer/package.xml',
                         '--packagingroot=' . __DIR__ . '/testit'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Downloading pear2.php.net/PEAR2_SimpleChannelServer' . "\n"
                    . 'Installed pear2.php.net/PEAR2_SimpleChannelServer-0.1.0' . "\n",
                    $contents,
                    'installation info');

$test->assertFileExists(__DIR__ . '/testit/' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/bin/pearscs', 'bin/pearscs');
$test->assertEquals(decoct(0755), decoct(0777 & fileperms(__DIR__ . '/testit/' . __DIR__ . DIRECTORY_SEPARATOR .
                                                          'testit/bin/pearscs')), 'bin/pearscs perms');
$test->assertFileExists(__DIR__ . '/testit' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer.php',
                        'src/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(__DIR__ . '/testit/' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer.php'), 'files match');

$test->assertEquals(array (
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/docs/PEAR2_SimpleChannelServer/pear2.php.net/examples/update_channel.php' =>
  array (
    'role' => 'doc',
    'name' => 'examples/update_channel.php',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/docs/PEAR2_SimpleChannelServer/pear2.php.net/examples/update_channel.php',
    'relativepath' => 'PEAR2_SimpleChannelServer/pear2.php.net/examples/update_channel.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/docs',
  ),
  __DIR__ . '/testit/bin/pearscs' =>
  array (
    'role' => 'script',
    'name' => 'scripts/pearscs',
    'baseinstalldir' => '/',
    'installed_as' => __DIR__ . '/testit/bin/pearscs',
    'relativepath' => 'pearscs',
    'configpath' => __DIR__ . '/testit/bin',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer.php',
    'relativepath' => 'PEAR2/SimpleChannelServer.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/CLI.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/CLI.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/CLI.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/CLI.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Categories.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Categories.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Categories.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Categories.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Categories/Exception.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Categories/Exception.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Categories/Exception.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Categories/Exception.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Channel.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Channel.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Channel.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Channel.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Exception.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Exception.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Exception.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Exception.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Get.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Get.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/Get.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Get.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Category.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Category.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Category.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Category.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Maintainer.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Maintainer.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Maintainer.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Maintainer.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Manager.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Manager.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Manager.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Manager.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Package.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Package.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Package.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Package.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
  '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Release.php' =>
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Release.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php/PEAR2/SimpleChannelServer/REST/Release.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Release.php',
    'configpath' => '' . __DIR__ . DIRECTORY_SEPARATOR . 'testit/php',
  ),
), \pear2\Pyrus\Config::current()->registry
                    ->info('PEAR2_SimpleChannelServer', 'pear2.php.net', 'installedfiles'), 'registered files');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===