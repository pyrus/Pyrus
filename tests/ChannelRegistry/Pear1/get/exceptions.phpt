--TEST--
\pear2\Pyrus\ChannelRegistry\Pear1::get, exceptions
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$creg = new pear2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit', true);
$fail = function($action, $expect) use ($test) {
    try {
        $action();
        throw new Exception($expect . ' should fail and did not');
    } catch (pear2\Pyrus\ChannelRegistry\Exception $e) {
        $test->assertEquals($expect, $e->getMessage(), $expect);
    }
};

$fail(function() use($creg) {$creg->get('unknown');}, 'Channel unknown does not exist');

class foo extends pear2\Pyrus\ChannelRegistry\Pear1
{
    function channelFileName($channel) {return parent::channelFileName($channel);}
}

$foo = new foo(dirname(__DIR__) . '/testit');
file_put_contents($foo->channelFileName('pear.php.net'), serialize(array('channel' => array('oops'))));
$fail(function() use($creg) {$creg->get('pear.php.net');},
      'Channel pear.php.net PEAR1 registry file is invalid channel information');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===