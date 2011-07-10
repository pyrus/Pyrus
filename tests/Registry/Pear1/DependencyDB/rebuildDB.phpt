--TEST--
Pear1 registry dependency database: rebuildDB()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
$stuffdir = realpath(__DIR__ . '/../../../Mocks/Internet/install.prepare.circulardep/get/') . '/';
mkdir($dir . DIRECTORY_SEPARATOR . 'php');
$reg = Pyrus\Config::current()->registry;
$p = new Pyrus\Package($stuffdir . 'P1-1.2.0.tar');
$reg->install($p->getPackagefileObject());
$p = new Pyrus\Package($stuffdir . 'P2-1.0.0.tar');
$reg->install($p->getPackagefileObject());
$p = new Pyrus\Package($stuffdir . 'P3-1.0.0.tar');
$reg->install($p->getPackagefileObject());
$p = new Pyrus\Package($stuffdir . 'P4-1.0.0.tar');
$reg->install($p->getPackagefileObject());

$db = new Pyrus\Registry\Pear1\DependencyDB($dir);
$db->rebuildDB();
$test->assertEquals(array (
  '_version' => '1.0',
  'dependencies' => 
  array (
    'pear2.php.net' => 
    array (
      'p1' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'P2',
            'channel' => 'pear2.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'p2' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'P3',
            'channel' => 'pear2.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'p3' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'P4',
            'channel' => 'pear2.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'p4' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'P1',
            'channel' => 'pear2.php.net',
            'max' => '1.2.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
    ),
  ),
  'packages' => 
  array (
    'pear2.php.net' => 
    array (
      'p2' => 
      array (
        0 => 
        array (
          'channel' => 'pear2.php.net',
          'package' => 'p1',
        ),
      ),
      'p3' => 
      array (
        0 => 
        array (
          'channel' => 'pear2.php.net',
          'package' => 'p2',
        ),
      ),
      'p4' => 
      array (
        0 => 
        array (
          'channel' => 'pear2.php.net',
          'package' => 'p3',
        ),
      ),
      'p1' => 
      array (
        0 => 
        array (
          'channel' => 'pear2.php.net',
          'package' => 'p4',
        ),
      ),
    ),
  ),
), $db->_getDepdb(), 'the stored data');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===