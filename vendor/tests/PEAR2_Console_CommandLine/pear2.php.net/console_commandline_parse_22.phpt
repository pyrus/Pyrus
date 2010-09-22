--TEST--
Test for PEAR2\Console\CommandLine::parse() method (--help with renderer options).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
--list
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addOption('list', array(
    'long_name'     => '--list',
    'action'        => 'List',
    'action_params' => array(
        'list'      => array('foo', 'bar', 'baz'),
        'message'   => 'foobarbaz---',
        'delimiter' => '|',
        'post'      => '---foobarbaz',
    ),
));
$parser->parse();

?>
--EXPECT--
foobarbaz---foo|bar|baz---foobarbaz
