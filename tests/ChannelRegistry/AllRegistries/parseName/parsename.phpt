--TEST--
\PEAR2\Pyrus\ChannelRegistry\Base::parseName()
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR);

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear2.php.net',
), $creg->parseName('foo'), 'foo');

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear.php.net',
), $creg->parseName('foo', 'pear.php.net'), 'foo pear.php.net');

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear.php.net',
), $creg->parseName('channel://pear/foo'), 'channel://pear/foo');

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pecl.php.net',
    'user' => 'user',
    'pass' => 'pass',
), $creg->parseName('channel://user:pass@pecl.php.net/foo'), 'channel://pear/foo');

$test->assertEquals(array(
    'uri' => 'http://user:pass@pecl.php.net/foo',
    'channel' => '__uri',
), $creg->parseName('http://user:pass@pecl.php.net/foo'), 'http://user:pass@pecl.php.net/foo');

$test->assertEquals(array(
    'uri' => 'https://user:pass@pecl.php.net/foo',
    'channel' => '__uri',
), $creg->parseName('https://user:pass@pecl.php.net/foo'), 'https://user:pass@pecl.php.net/foo');

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear2.php.net',
    'group' => 'group',
), $creg->parseName('foo#group'), 'foo#group');

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear2.php.net',
    'group' => 'group',
    'opts' => array(
        'one' => 'one',
        'two' => 'two',
    )
), $creg->parseName('foo?one=one&two=two#group'), 'foo?one=one&two=two#group');

foreach (array('tgz', 'tar', 'zip', 'tbz', 'phar') as $ext) {
    $test->assertEquals(array(
        'package' => 'foo',
        'channel' => 'pear2.php.net',
        'extension' => $ext,
    ), $creg->parseName("foo.$ext"), "foo.$ext");
}

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear2.php.net',
    'version' => '1.2.3',
), $creg->parseName('foo-1.2.3'), 'foo-1.2.3');

foreach (array('devel', 'snapshot', 'alpha', 'beta', 'stable') as $state) {
    $test->assertEquals(array(
        'package' => 'foo',
        'channel' => 'pear2.php.net',
        'state' => $state,
    ), $creg->parseName("foo-$state"), "foo-$state");
}

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===