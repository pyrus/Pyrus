--TEST--
Pyrus XMLParser: complex recursive tags
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$xml = '<?xml version="1.0" ?><package>
<lead>
 <name>test</name>
 <another>tag</another>
</lead>
<lead>
 <name>second</name>
 <another>thing</another>
</lead>
<contents>
 <dir name="blah">
  <dir name="two">
   <dir name="three">
    <file name="my"/>
   </dir>
  </dir>
 </dir>
 <dir name="four">
  <file name="my2"/>
 </dir>
</contents>
</package>';
$res = $parser->parseString($xml);

$test->assertEquals(array (
  'package' =>
  array (
    'lead' =>
    array (
      0 =>
      array (
        'name' => 'test',
        'another' => 'tag',
      ),
      1 =>
      array (
        'name' => 'second',
        'another' => 'thing',
      ),
    ),
    'contents' =>
    array (
      'dir' =>
      array (
        0 =>
        array (
          'attribs' =>
          array (
            'name' => 'blah',
          ),
          'dir' =>
          array (
            'attribs' =>
            array (
              'name' => 'two',
            ),
            'dir' =>
            array (
              'attribs' =>
              array (
                'name' => 'three',
              ),
              'file' =>
              array (
                'attribs' =>
                array (
                  'name' => 'my',
                ),
              ),
            ),
          ),
        ),
        1 =>
        array (
          'attribs' =>
          array (
            'name' => 'four',
          ),
          'file' =>
          array (
            'attribs' =>
            array (
              'name' => 'my2',
            ),
          ),
        ),
      ),
    ),
  ),
), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===