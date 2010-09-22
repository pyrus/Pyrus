--TEST--
Test for PEAR2\Console\CommandLine options defaults.
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

try {
    $parser = buildParser3();
    $parser->force_options_defaults = true;
    $result = $parser->parse();
    foreach ($result->options as $k => $v) {
        echo $k . ":"; var_dump($v);
    }
} catch (PEAR2\Console\CommandLine\Exception $exc) {
    $parser->displayError($exc->getMessage());
}

?>
--EXPECT--
true:bool(false)
false:bool(true)
int:int(0)
float:float(0)
string:NULL
counter:int(0)
callback:NULL
array:array(0) {
}
password:NULL
help:NULL
version:NULL
