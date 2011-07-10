--TEST--
\Pyrus\Installer::commit() configuration (cfg) role
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';

$c = getTestConfig();

$pf = new \Pyrus\PackageFile\v2;
$pf->name = 'P1';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');

$pf->setPackagefile(__DIR__ . '/package.xml');
file_put_contents(TESTDIR . '/test.txt', 'first try');
$pf->files['test.txt'] = array('role' => 'cfg', 'md5sum' => md5('first try'));
file_put_contents(TESTDIR . '/package.xml', $pf);

$package = new \Pyrus\Package(TESTDIR . '/package.xml');

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$test->assertEquals(true, file_exists(TESTDIR . '/cfg/pear2.php.net/P1/test.txt'), 'cfg was installed');

$pf->version['release'] = '1.0.1';
file_put_contents(TESTDIR . '/package.xml', $pf);
$package = new \Pyrus\Package(TESTDIR . '/package.xml');
\Pyrus\Main::$options['upgrade'] = true;

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$test->assertEquals('1.0.1', \Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'), 'upgraded');
$test->assertEquals(true, \Pyrus\Config::current()->registry->exists('P1', 'pear2.php.net'), 'exists');
$test->assertEquals('first try', file_get_contents(TESTDIR . '/cfg/pear2.php.net/P1/test.txt'), 'cfg contents normal');
$test->assertEquals(false, file_exists(TESTDIR . '/cfg/pear2.php.net/P1/test.txt.new-1.0.1'),
                    'cfg should not be detected as changed 1.0.1');

$pf->version['release'] = '1.0.2';
file_put_contents(TESTDIR . '/test.txt', 'second try');
$pf->files['test.txt'] = array('role' => 'cfg', 'md5sum' => md5('second try'));
file_put_contents(TESTDIR . '/package.xml', $pf);
$package = new \Pyrus\Package(TESTDIR . '/package.xml');

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$test->assertEquals('1.0.2', \Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'upgraded 2');
$test->assertEquals('second try', file_get_contents(TESTDIR . '/cfg/pear2.php.net/P1/test.txt'), 'cfg contents normal');
$test->assertEquals(false, file_exists(TESTDIR . '/cfg/pear2.php.net/P1/test.txt.new-1.0.2'),
                    'cfg should not be detected as changed 1.0.2');

$pf->version['release'] = '1.0.3';
file_put_contents(TESTDIR . '/package.xml', $pf);
$package = new \Pyrus\Package(TESTDIR . '/package.xml');
file_put_contents(TESTDIR . '/cfg/pear2.php.net/P1/test.txt', 'I modified this');

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();

$test->assertEquals('1.0.3', \Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'upgraded 3');
$test->assertEquals('I modified this', file_get_contents(TESTDIR . '/cfg/pear2.php.net/P1/test.txt'), 'cfg contents normal');
$test->assertEquals(true, file_exists(TESTDIR . '/cfg/pear2.php.net/P1/test.txt.new-1.0.3'),
                    'cfg should be detected as changed 1.0.3');
$test->assertEquals('second try', file_get_contents(TESTDIR . '/cfg/pear2.php.net/P1/test.txt.new-1.0.3'),
                    'cfg new contents normal');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===