--TEST--
Pyrus Download progress listener
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$download = new \Pyrus\DownloadProgressListener;
$download->update(1, 'connect', 1);
$download->update(1, 'disconnect', 1);
$download->update(1, 'connect', 1);
$download->update(1, 'redirect', 'foobar');
$download->update(1, 'mime-type', 'blah/blah');
$download->update(1, 'filesize', 100000);
$download->update(1, 'downloadprogress', 0);
$download->update(1, 'downloadprogress', 1000);
$download->update(1, 'downloadprogress', 8000);
$download->update(1, 'downloadprogress', 20000);
$download->update(1, 'downloadprogress', 32000);
$download->update(1, 'downloadprogress', 44000);
$download->update(1, 'downloadprogress', 56000);
$download->update(1, 'downloadprogress', 68000);
$download->update(1, 'downloadprogress', 80000);
$download->update(1, 'downloadprogress', 92000);
$download->update(1, 'downloadprogress', 100000);
$download->update(1, 'disconnect', 1);

$download = new \Pyrus\DownloadProgressListener;
$download->update(1, 'connect', 1);
$download->update(1, 'redirect', 'foobar');
$download->update(1, 'mime-type', 'blah/blah');
$download->update(1, 'downloadprogress', 0);
$download->update(1, 'downloadprogress', 1000);
$download->update(1, 'downloadprogress', 8000);
$download->update(1, 'downloadprogress', 20000);
$download->update(1, 'downloadprogress', 32000);
$download->update(1, 'downloadprogress', 44000);
$download->update(1, 'downloadprogress', 56000);
$download->update(1, 'downloadprogress', 68000);
$download->update(1, 'downloadprogress', 80000);
$download->update(1, 'downloadprogress', 92000);
$download->update(1, 'downloadprogress', 100000);
$download->update(1, 'disconnect', 1);
$test->assertEquals(array (
  '',
  'Redirected to foobar
Mime-type: blah/blah',
  '[=>                                                                                                  ] 1% ( 0/97 kb)',
  '[========>                                                                                           ] 8% ( 7/97 kb)',
  '[====================>                                                                               ] 20% (19/97 kb)',
  '[================================>                                                                   ] 32% (31/97 kb)',
  '[============================================>                                                       ] 44% (42/97 kb)',
  '[========================================================>                                           ] 56% (54/97 kb)',
  '[====================================================================>                               ] 68% (66/97 kb)',
  '[================================================================================>                   ] 80% (78/97 kb)',
  '[============================================================================================>       ] 92% (89/97 kb)',
  '[====================================================================================================>] 100% (97/97 kb)',
  '',
  'Redirected to foobar
Mime-type: blah/blah',
  'Unknown filesize..  0 kb done..',
  'Unknown filesize..  7 kb done..',
  'Unknown filesize.. 19 kb done..',
  'Unknown filesize.. 31 kb done..',
  'Unknown filesize.. 42 kb done..',
  'Unknown filesize.. 54 kb done..',
  'Unknown filesize.. 66 kb done..',
  'Unknown filesize.. 78 kb done..',
  'Unknown filesize.. 89 kb done..',
  'Unknown filesize.. 97 kb done..',
  '',
), \Pyrus\Logger::$log[0], 'download log');
?>
===DONE===
--EXPECT--
===DONE===