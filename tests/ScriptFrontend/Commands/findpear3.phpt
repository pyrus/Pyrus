--TEST--
\Pyrus\ScriptFrontend\Commands::_findPEAR test 3: no userfile detected, decline creation
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
test_scriptfrontend::$stdin = array(
    'no', // answer to "It appears you have not used Pyrus before, welcome!  Initialize install?"
    '', // conclusion
);
$cli = new test_scriptfrontend();
$cli->run($args = array ());

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
Pyrus: No user configuration file detected
It appears you have not used Pyrus before, welcome!  Initialize install?
Please choose:
  yes
  no
[yes] : no
OK, thank you, finishing execution now