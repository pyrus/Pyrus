--TEST--
\Pyrus\ChannelRegistry\Pear1::get, exceptions
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$creg = new Pyrus\ChannelRegistry\Pear1(TESTDIR, true);
$fail = function($action, $expect) use ($test) {
    try {
        $action();
        throw new Exception($expect . ' should fail and did not');
    } catch (Pyrus\ChannelRegistry\Exception $e) {
        $test->assertEquals($expect, $e->getMessage(), $expect);
    }
};

$fail(function() use($creg) {$creg->get('unknown');}, 'Channel unknown does not exist');

class foo extends Pyrus\ChannelRegistry\Pear1
{
    function channelFileName($channel) {return parent::channelFileName($channel);}
}

$foo = new foo(TESTDIR);
file_put_contents($foo->channelFileName('pear.php.net'), serialize(array('channel' => array('oops'))));
$fail(function() use($creg) {$creg->get('pear.php.net');},
      'Channel pear.php.net PEAR1 registry file is invalid channel information');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===