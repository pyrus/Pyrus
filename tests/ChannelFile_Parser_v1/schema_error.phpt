--TEST--
\Pyrus\ChannelFile\Parser\v1: Test xsd validation.
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

try {
    $parser->parse('<?xml version="1.0" encoding="ISO-8859-1" ?>
<channel version="1.0" xmlns="http://pear.php.net/channel-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://pear.php.net/dtd/channel-1.0 http://pear.php.net/dtd/channel-1.0.xsd">
 <name>pear.unl.edu</name>
 <summary>UNL PHP Extension and Application Repository</summary>
 <suggestedalias>unl</suggestedalias>
</channel>');
	
    throw new Exception('invalid xml worked and should not');
} catch (\Pyrus\ChannelFile\Exception $e) {
    $test->assertEquals('Invalid channel.xml',
                $e->getMessage(), 'invalid xml');
}
?>
===DONE===
--EXPECT--
===DONE===