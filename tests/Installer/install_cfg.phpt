--TEST--
\PEAR2\Pyrus\Installer::commit() configuration (cfg) role
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';

@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
$c->saveConfig();

$pf = new \PEAR2\Pyrus\PackageFile\v2;
$pf->name = 'P1';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');

$pf->setPackagefile(__DIR__ . '/package.xml');
file_put_contents(__DIR__ . '/testit/test.txt', 'first try');
$pf->files['test.txt'] = array('role' => 'cfg', 'md5sum' => md5('first try'));
file_put_contents(__DIR__ . '/testit/package.xml', $pf);

$package = new \PEAR2\Pyrus\Package(__DIR__ . '/testit/package.xml');

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertEquals(true, file_exists(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt'), 'cfg was installed');

$pf->version['release'] = '1.0.1';
file_put_contents(__DIR__ . '/testit/package.xml', $pf);
$package = new \PEAR2\Pyrus\Package(__DIR__ . '/testit/package.xml');
\PEAR2\Pyrus\Main::$options['upgrade'] = true;

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertEquals('1.0.1', \PEAR2\Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'), 'upgraded');
$test->assertEquals(true, \PEAR2\Pyrus\Config::current()->registry->exists('P1', 'pear2.php.net'), 'exists');
$test->assertEquals('first try', file_get_contents(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt'), 'cfg contents normal');
$test->assertEquals(false, file_exists(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt.new-1.0.1'),
                    'cfg should not be detected as changed 1.0.1');

$pf->version['release'] = '1.0.2';
file_put_contents(__DIR__ . '/testit/test.txt', 'second try');
$pf->files['test.txt'] = array('role' => 'cfg', 'md5sum' => md5('second try'));
file_put_contents(__DIR__ . '/testit/package.xml', $pf);
$package = new \PEAR2\Pyrus\Package(__DIR__ . '/testit/package.xml');

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertEquals('1.0.2', \PEAR2\Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'upgraded 2');
$test->assertEquals('second try', file_get_contents(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt'), 'cfg contents normal');
$test->assertEquals(false, file_exists(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt.new-1.0.2'),
                    'cfg should not be detected as changed 1.0.2');

$pf->version['release'] = '1.0.3';
file_put_contents(__DIR__ . '/testit/package.xml', $pf);
$package = new \PEAR2\Pyrus\Package(__DIR__ . '/testit/package.xml');
file_put_contents(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt', 'I modified this');

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();

$test->assertEquals('1.0.3', \PEAR2\Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'upgraded 3');
$test->assertEquals('I modified this', file_get_contents(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt'), 'cfg contents normal');
$test->assertEquals(true, file_exists(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt.new-1.0.3'),
                    'cfg should be detected as changed 1.0.3');
$test->assertEquals('second try', file_get_contents(__DIR__ . '/testit/cfg/P1/pear2.php.net/test.txt.new-1.0.3'),
                    'cfg new contents normal');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===