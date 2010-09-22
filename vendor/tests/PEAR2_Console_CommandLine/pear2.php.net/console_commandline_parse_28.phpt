--TEST--
Test for PEAR2\Console\CommandLine::parse() method (invalid subcommand detection).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
upgrade --dry-run=foo
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = buildParser4();
try {
    $result = $parser->parse();
} catch (Exception $exc) {
    echo $exc->getMessage();
}

?>
--EXPECT--
Option "dry_run" does not expect a value (got "foo").