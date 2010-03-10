--TEST--
Pyrus DER: UTCTime
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals(chr(0x17) . chr(strlen('090501050000Z')) . '090501050000Z',
                    $der->UTCTime(new DateTime('2009-05-01', new DateTimeZone('America/Chicago')))->serialize(),
                    '2009-05-01');

$der = new \pear2\Pyrus\DER;
$test->assertEquals(chr(0x17) . chr(strlen('990501050000Z')) . '990501050000Z',
                    $der->UTCTime(new DateTime('1999-05-01', new DateTimeZone('America/Chicago')))->serialize(),
                    '1999-05-01');
?>
===DONE===
--EXPECT--
===DONE===