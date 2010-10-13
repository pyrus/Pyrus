--TEST--
\PEAR2\Pyrus\Installer::install() verify files installed correctly
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
\PEAR2\Pyrus\Main::$options['packagingroot'] = TESTDIR;

set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR . '/local/lib');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertFileExists(TESTDIR . TESTDIR . '/bin/pearscs', 'bin/pearscs');
$test->assertEquals(decoct(0755), decoct(0777 & fileperms(TESTDIR . TESTDIR . '/bin/pearscs')), 'bin/pearscs perms');
$test->assertFileExists(TESTDIR . TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer.php',
                        'src/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(TESTDIR . TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer.php'), 'files match');

$test->assertEquals(array (
  TESTDIR . '/local/lib/docs/PEAR2_SimpleChannelServer/pear2.php.net/examples/update_channel.php' => 
  array (
    'role' => 'doc',
    'name' => 'examples/update_channel.php',
    'installed_as' => TESTDIR . '/local/lib/docs/PEAR2_SimpleChannelServer/pear2.php.net/examples/update_channel.php',
    'relativepath' => 'PEAR2_SimpleChannelServer/pear2.php.net/examples/update_channel.php',
    'configpath' => TESTDIR . '/local/lib/docs',
  ),
  TESTDIR . '/bin/pearscs' => 
  array (
    'role' => 'script',
    'name' => 'scripts/pearscs',
    'baseinstalldir' => '/',
    'installed_as' => TESTDIR . '/bin/pearscs',
    'relativepath' => 'pearscs',
    'configpath' => TESTDIR . '/bin',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer.php',
    'relativepath' => 'PEAR2/SimpleChannelServer.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/CLI.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/CLI.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/CLI.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/CLI.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Categories.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Categories.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Categories.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Categories.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Categories/Exception.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Categories/Exception.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Categories/Exception.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Categories/Exception.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Channel.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Channel.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Channel.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Channel.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Exception.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Exception.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Exception.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Exception.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Get.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/Get.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/Get.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/Get.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Category.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Category.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Category.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Category.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Maintainer.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Maintainer.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Maintainer.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Maintainer.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Manager.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Manager.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Manager.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Manager.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Package.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Package.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Package.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Package.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
  TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Release.php' => 
  array (
    'role' => 'php',
    'name' => 'src/SimpleChannelServer/REST/Release.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => TESTDIR . '/local/lib/php/PEAR2/SimpleChannelServer/REST/Release.php',
    'relativepath' => 'PEAR2/SimpleChannelServer/REST/Release.php',
    'configpath' => TESTDIR . '/local/lib/php',
  ),
), \PEAR2\Pyrus\Config::current()->registry
                    ->info('PEAR2_SimpleChannelServer', 'pear2.php.net', 'installedfiles'), 'registered files');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===