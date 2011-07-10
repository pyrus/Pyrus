--TEST--
\Pyrus\ChannelRegistry\Pear1::add, exceptions
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
$fail(function() use($creg) {$creg->add($creg->get('pear.php.net'));},
      'Cannot add channel, registry is read-only');

$creg = new Pyrus\ChannelRegistry\Pear1(TESTDIR);
// the chmod is not working on windows so let's skip it
if (substr(PHP_OS, 0, 3) !== 'WIN') {
    $p = fileperms(TESTDIR . '/php/.channels');
    chmod(TESTDIR . '/php/.channels', 0444);
    $fail(function() use($creg) {$creg->add($creg->get('pear.php.net'));},
          'Cannot add channel pear.php.net, channel registry path is not writable');
    chmod(TESTDIR . '/php/.channels', $p);
}

$fail(function() use($creg) {$creg->add($creg->get('pear.php.net'));},
      'Cannot add channel pear.php.net, channel already exists, use update to change');

$chan = $creg->get('pear.php.net')->toChannelFile();
$chan->name = 'unknown';
$fail(function() use($creg, $chan) {$creg->update(new Pyrus\Channel($chan));},
      'Error: channel unknown is unknown');

class foo extends Pyrus\ChannelRegistry\Pear1
{
    function channelFileName($channel) {return parent::channelFileName($channel);}
    function channelAliasFileName($channel) {return parent::channelAliasFileName($channel);}
}

$chan = $creg->get('pear.php.net');

$foo = new foo(TESTDIR);
$p = fileperms($foo->channelFileName('pear.php.net'));
chmod($foo->channelFileName('pear.php.net'), 0444);
$fail(function() use($creg, $chan) {$creg->update($chan);},
      'Cannot add/update channel pear.php.net, unable to open PEAR1 channel registry file');
chmod($foo->channelFileName('pear.php.net'), $p);

$p = fileperms($foo->channelAliasFileName('pear'));
chmod($foo->channelAliasFileName('pear'), 0444);
$fail(function() use($creg, $chan) {$creg->update($chan);},
      'Cannot add/update channel pear.php.net, unable to open PEAR1 channel alias file');
chmod($foo->channelAliasFileName('pear'), $p);

$foo = new foo(TESTDIR);
$fp = fopen($foo->channelFileName('pear.php.net'), 'w');
$fail(function() use($creg) {$creg->update($creg->get('pear.php.net'));},
      'Channel pear.php.net PEAR1 registry file is corrupt');
fclose($fp);
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===