--TEST--
PackageFile v2: test basic package.xml properties
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../setupFiles/setupPackageFile.php.inc';
$reg = $package; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/info/basic.template';

// don't try this at home!
$reg->fromArray(array('package' => array()));
$test->assertEquals(false, $reg->{'api-version'}, 'api-version blank');
$test->assertEquals(false, $reg->{'api-state'}, 'api-state blank');
$test->assertEquals(false, $reg->{'release-version'}, 'api-version blank');
$test->assertEquals(false, $reg->state, 'state blank');
$test->assertEquals(false, $reg->bundledpackage, 'bundledpackage');
$test->assertEquals(false, $reg->releases, 'releases');

$reg->fromArray(array('package' => array('uri' => 'test', 'bundle' => '')));
$test->assertEquals(array(), $reg->bundledpackage->getInfo(), 'bundledpackage 2');
$test->assertEquals('__uri', $reg->channel, 'uri channel');

$test->assertEquals('2.0', $reg->getPackagexmlVersion(), '2.0 bundle');
$test->assertEquals(false, $reg->sourcepackage, 'sourcepackage bundle');

$reg->fromArray(array('package' => array('phprelease' => array('', ''))));
$test->assertEquals(array('', ''), $reg->installGroup, 'installGroup');

$test->assertEquals('2.0', $reg->getPackagexmlVersion(), '2.0 php');
$test->assertEquals(false, $reg->sourcepackage, 'sourcepackage php');

$reg->fromArray(array('package' => array('zendextsrcrelease' => '')));
$test->assertEquals('zendextsrc', $reg->getPackageType(), 'zendextsrc');

$test->assertEquals('2.1', $reg->getPackagexmlVersion(), '2.1 zendextsrc');
$test->assertEquals(false, $reg->sourcepackage, 'sourcepackage zendsrc');

$reg->fromArray(array('package' => array('zendextbinrelease' => '', 'srcpackage' => 'hi', 'srcchannel' => 'there')));
$test->assertEquals('zendextbin', $reg->getPackageType(), 'zendextbin');
$test->assertEquals(array('channel' => 'there', 'package' => 'hi'), $reg->sourcepackage, 'sourcepackage zend bin');

$test->assertEquals('2.1', $reg->getPackagexmlVersion(), '2.1 zendextbin');

$reg->fromArray(array('package' => array('extsrcrelease' => '')));
$test->assertEquals('extsrc', $reg->getPackageType(), 'extsrc');

$test->assertEquals('2.0', $reg->getPackagexmlVersion(), '2.0 extsrc');
$test->assertEquals(false, $reg->sourcepackage, 'sourcepackage src');

$reg->fromArray(array('package' => array('extbinrelease' => '', 'srcpackage' => 'hi', 'srcchannel' => 'there')));
$test->assertEquals('extbin', $reg->getPackageType(), 'zendextbin');
$test->assertEquals(array('channel' => 'there', 'package' => 'hi'), $reg->sourcepackage, 'sourcepackage bin');

$test->assertEquals('2.0', $reg->getPackagexmlVersion(), '2.0 extbin');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===