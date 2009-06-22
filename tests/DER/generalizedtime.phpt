--TEST--
Pyrus DER: GeneralizedTime
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals(chr(0x18) . chr(strlen('20090501050000Z')) . '20090501050000Z',
                    $der->generalizedTime(new DateTime('2009-05-01', new DateTimeZone('America/Chicago')))->serialize(),
                    '2009-05-01');
?>
===DONE===
--EXPECT--
===DONE===