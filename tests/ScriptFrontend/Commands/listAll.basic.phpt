--TEST--
\Pyrus\ScriptFrontend\Commands::listAll() --basic
--FILE--
<?php
/**
 * Test a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0 (1.2.3 is latest version)
 *
 * P2 1.2.3 -> P3
 *          -> P5
 *
 * P2 1.2.2 -> P3
 *
 * P3
 *
 * P4 -> P2 != 1.2.3
 *
 * P5
 *
 * This causes a conflict when P1 and P4 are installed that must resolve to installing:
 *
 * P1
 * P2 1.2.2
 * P3
 * P4
 */

include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare(new \Pyrus\Package('pear2/P1-1.0.0'));
\Pyrus\Installer::prepare(new \Pyrus\Package('pear2/P4-stable', true));
\Pyrus\Installer::commit();

ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array ('remote-list', '--basic', 'pear2.php.net'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n" .
'Remote packages for channel pear2.php.net:
Category 1:
  *P1 latest stable: 1.0.0, latest release: 1.0.0 (stable)
  *P3 latest stable: 1.0.0, latest release: 1.0.0 (stable)
   P5 latest stable: 1.0.0, latest release: 1.0.0 (stable)
Category 2:
  *P2 latest stable: 1.2.3, latest release: 1.2.3 (stable)
  *P4 latest stable: 1.0.0, latest release: 1.0.0 (stable)
', $contents, 'output');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===