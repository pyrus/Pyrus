--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::uninstall(), plugin
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
require __DIR__ . '/setup.php.inc';
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'install', '-p',
                         __DIR__.'/Pyrus_Developer/package.xml'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Downloading pear2.php.net/PEAR2_Pyrus_Developer
Installed pear2.php.net/PEAR2_Pyrus_Developer-0.1.0' . "\n",
                    $contents,
                    'list packages');

$test->assertFileExists(__DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN.php',
                        'PEAR2SVN.php not installed properly');
$test->assertEquals(array (
  __DIR__ . '/testit/plugins/data/pear2.php.net/PEAR2_Pyrus_Developer/commands.xml' => 
  array (
    'role' => 'customcommand',
    'name' => 'customcommand/commands.xml',
    'baseinstalldir' => '/',
    'installed_as' => __DIR__ . '/testit/plugins/data/pear2.php.net/PEAR2_Pyrus_Developer/commands.xml',
    'relativepath' => 'pear2.php.net/PEAR2_Pyrus_Developer/commands.xml',
    'configpath' => __DIR__ . '/testit/plugins/data',
  ),
  __DIR__ . '/testit/plugins/data/pear2.php.net/PEAR2_Pyrus_Developer/phartemplate.php' => 
  array (
    'role' => 'data',
    'name' => 'data/phartemplate.php',
    'baseinstalldir' => '/',
    'installed_as' => __DIR__ . '/testit/plugins/data/pear2.php.net/PEAR2_Pyrus_Developer/phartemplate.php',
    'relativepath' => 'pear2.php.net/PEAR2_Pyrus_Developer/phartemplate.php',
    'configpath' => __DIR__ . '/testit/plugins/data',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Exception.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/Creator/Exception.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Exception.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/Creator/Exception.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Phar.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/Creator/Phar.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Phar.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/Creator/Phar.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Phar/PHPArchive.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/Creator/Phar/PHPArchive.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Phar/PHPArchive.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/Creator/Phar/PHPArchive.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Tar.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/Creator/Tar.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Tar.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/Creator/Tar.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Xml.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/Creator/Xml.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Xml.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/Creator/Xml.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Zip.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/Creator/Zip.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/Creator/Zip.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/Creator/Zip.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/Commands.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/PackageFile/Commands.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/Commands.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/PackageFile/Commands.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/PackageFile/PEAR2SVN.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN/Filter.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/PackageFile/PEAR2SVN/Filter.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN/Filter.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/PackageFile/PEAR2SVN/Filter.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
  __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/v2.php' => 
  array (
    'role' => 'php',
    'name' => 'src/Pyrus/Developer/PackageFile/v2.php',
    'baseinstalldir' => 'PEAR2',
    'installed_as' => __DIR__ . '/testit/plugins/php/PEAR2/Pyrus/Developer/PackageFile/v2.php',
    'relativepath' => 'PEAR2/Pyrus/Developer/PackageFile/v2.php',
    'configpath' => __DIR__ . '/testit/plugins/php',
  ),
), \PEAR2\Pyrus\Config::current()->pluginregistry->info('PEAR2_Pyrus_Developer',
                                                                                 'pear2.php.net',
                                                                                 'installedfiles'), 'file installed');


ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'uninstall', 'PEAR2_Pyrus_Developer'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Uninstalled pear2.php.net/PEAR2_Pyrus_Developer' . "\n",
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