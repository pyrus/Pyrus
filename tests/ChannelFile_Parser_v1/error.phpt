--TEST--
\PEAR2\Pyrus\ChannelFile\Parser\v1 errors
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $parser->parse('', false, 'stdClass');
    throw new Exception('stdClass worked and should not');
} catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
    $test->assertEquals('Class stdClass' .
                ' passed to parse() must be a child class of \PEAR2\Pyrus\ChannelFile\v1',
                $e->getMessage(), 'stdClass');
}

try {
    $parser->parse('<channel');
    throw new Exception('invalid xml worked and should not');
} catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
    $test->assertEquals('Invalid channel.xml',
                $e->getMessage(), 'invalid xml');
}
?>
===DONE===
--EXPECT--
===DONE===