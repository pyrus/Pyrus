--TEST--
ChannelFile: random channelfile tests 2
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$c = new \Pyrus\ChannelFile(file_get_contents(__DIR__ . '/../../ChannelRegistry/sample_channel.xml'), true);

$c->fromArray(array('validatepackage' => 'foo', '_lastmodified' => 'hi'));
$test->assertEquals(false, $c->name, 'setting name to unset value');
$test->assertEquals(null, $c->blahfoo, 'retrieving random name');
$c->ssl = true;
$test->assertEquals(true, $c->ssl, 'setting ssl from scratch');
try {
    $c->summary = "oops\nie";
    throw new Exception('passed and should fail');
} catch (Pyrus\Channel\Exception $e) {
    $test->assertEquals('Channel summary cannot be multi-line', $e->getMessage(),
                        'multi-line summary');
}
$test->assertEquals('', $c->alias, 'alias before');
$test->assertEquals(null, $c->localalias, 'localalias before');
$test->assertEquals('', $c->suggestedalias, 'suggestedalias before');
$c->alias = 'foo';
$c->localalias = 'localfoo';
$test->assertEquals('localfoo', $c->alias, 'alias');
$test->assertEquals('foo', $c->suggestedalias, 'alias');
$test->assertEquals('localfoo', $c->localalias, 'localalias');

$test->assertEquals(array('attribs' => array('version' => 'default'), '_content' => 'foo'),
                    $c->getValidationPackage(), 'validatepackage');
$test->assertEquals('hi', $c->lastModified(), 'lastmodified');
$test->assertSame($c->internal, $c->toChannelFile(), 'toChannelFile');

foreach ($c->protocols->rest as $type => $url) {
    throw new Exception('should have failed, did not');
}
$c->protocols->rest['TEST']->baseurl = 'hi';
$test->assertEquals('hi/', $c->protocols->rest['TEST']->baseurl, 'adds trailing slash');
foreach ($c->protocols->rest as $type => $url) {
    if ($type === 'TEST') {
        $test->assertEquals('hi/', $url, 'iteration adds trailing slash');
        goto nofail;
    }
}
echo "TEST was not found on line ", __LINE__ , "\n";
nofail:
?>
===DONE===
--EXPECT--
===DONE===