--TEST--
Registry base: test makeUninstallConnections()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.channels.template';

$package = $reg->package['pear2.php.net/HooHa'];
$graph = new \pear2\Pyrus\DirectedGraph;

$packages = array();
$info = new \pear2\Pyrus\PackageFile\v2;
$info->name = 'foo';
$info->channel = 'bar.poo';
$packages['bar.poo/foo'] = clone $info;
$info->name = 'fra';
$info->uri = 'http://bar';
$packages['__uri/fra'] = clone $info;
$info->name = 'boop';
$info->channel = 'is.used';
$packages['is.used/boop'] = clone $info;
$info->name = 'boo';
$info->channel = 'not.used';
$packages['not.used/boo'] = clone $info;
$package->dependencies['required']->package['bar.poo/foo']->min('1.0');
$package->dependencies['optional']->subpackage['__uri/fra']->uri('http://bar');
$package->dependencies['group']->boo->package['is.used/boop']->min('1.0');
$package->dependencies['required']->subpackage['not.used/boo']->conflicts();
$package->makeUninstallConnections($graph, $packages);
$result = array();
foreach ($graph as $pack) {
    $result[] = $pack->channel . '/' . $pack->name;
}
sort($result);

$test->assertEquals(array(
    '__uri/fra',
    'bar.poo/foo',
    'is.used/boop',
    'pear2.php.net/HooHa',
), $result, 'result');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===