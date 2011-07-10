<?php
require __DIR__ . '/../../setup.php.inc';
$c = getTestConfig();
$reg = new \Pyrus\Registry\Sqlite3(TESTDIR);
$errs = new \PEAR2\MultiErrors;