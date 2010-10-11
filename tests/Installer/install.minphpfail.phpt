--TEST--
\PEAR2\Pyrus\Installer: install remote packages, explicit version requested, fail on php dep
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/install.force',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';

try {
    \PEAR2\Pyrus\Installer::begin();
    \PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P2-1.0.0'));
    \PEAR2\Pyrus\Installer::commit();
    throw new Exception('worked and should not');
} catch (PEAR2\Pyrus\Installer\Exception $e) {
    $test->assertEquals('pear2.php.net/P2 requires PHP (version >= 10000.345.56), installed version is ' .
                        phpversion(),
                        $e->getPrevious()->E_ERROR[0]->getMessage(), 'right message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===