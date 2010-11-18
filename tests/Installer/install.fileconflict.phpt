--TEST--
\PEAR2\Pyrus\Installer: install failure: file conflict between 2 downloaded packages
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/upgrade.packagesplitting',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P1-1.0.0'));
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P2'));
try {
    \PEAR2\Pyrus\Installer::commit();
    throw new Exception('passed and should have failed');
} catch (\PEAR2\Pyrus\Installer\Exception $e) {
    $test->assertEquals('File conflicts detected:
 Package pear2.php.net/P1:
  glooby2 (conflicts with package pear2.php.net/P2)
', $e->getMessage(), 'file conflict');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===