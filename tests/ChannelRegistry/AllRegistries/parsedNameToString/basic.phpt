--TEST--
\pear2\Pyrus\ChannelRegistry::parsedNameToString() basic test
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$creg = new \pear2\Pyrus\ChannelRegistry(__DIR__ . '/testit');
$cregp = new \pear2\Pyrus\ChannelRegistry(__DIR__ . '/testit/blahblah');
$chan = $cregp['pear2.php.net']->toChannelFile();
$chan->name = 'boo.example.com';
$chan->alias = 'boo';
$cregp[] = $chan;
$creg->setParent($cregp);

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'boo.example.com',
), $creg->parseName('boo.example.com/foo'), 'boo.example.com/foo');

$test->assertEquals('channel://boo.example.com/foo',
                    $creg->parsedNameToString(array(
                        'package' => 'foo',
                        'channel' => 'boo.example.com',
                    )),
                    'right parsed thing');

// throw this in there for coverage reasons
$test->assertEquals('pear.php.net', $creg->getPearChannel()->name, '__call test');

$pf = new pear2\Pyrus\PackageFile\v2;
$pf->name = 'hi';
$pf->channel = 'pear2.php.net';
$pf->version['release'] = '1.2.3';

$test->assertEquals('channel://pear2.php.net/hi-1.2.3', $creg->parsedNameToString($pf), 'object');
$test->assertEquals('pear2/hi', $creg->parsedNameToString($pf, true), 'object brief');

$pf->uri = 'http://blah.blah';

$test->assertEquals('http://blah.blah', $creg->parsedNameToString($pf), 'object __uri');

$test->assertEquals('http://foo.example.com/hi',
                    $creg->parsedNameToString(array('uri' => 'http://foo.example.com/hi')), 'uri');

$test->assertEquals('channel://cellog:yeahright@pear2.php.net/hi-alpha',
                    $creg->parsedNameToString(array(
                        'package' => 'hi',
                        'channel' => 'pear2.php.net',
                        'user' => 'cellog',
                        'pass' => 'yeahright',
                        'state' => 'alpha',
                    )), 'user/pass');

$test->assertEquals('channel://pear2.php.net/hi.tgz',
                    $creg->parsedNameToString(array(
                        'package' => 'hi',
                        'channel' => 'pear2.php.net',
                        'extension' => 'tgz',
                    )), 'extension');

$test->assertEquals('channel://pear2.php.net/hi?hi=there+&you=fool+me',
                    $creg->parsedNameToString(array(
                        'package' => 'hi',
                        'channel' => 'pear2.php.net',
                        'opts' => array('hi' => 'there ', 'you' => 'fool me')
                    )), 'opts');

$test->assertEquals('channel://pear2.php.net/hi#foo',
                    $creg->parsedNameToString(array(
                        'package' => 'hi',
                        'channel' => 'pear2.php.net',
                        'group' => 'foo',
                    )), 'group');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===