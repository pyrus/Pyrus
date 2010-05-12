--TEST--
PackageFile v2: test package.xml usestask property errors
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \PEAR2\Pyrus\PackageFile\v2;

try {
    $reg->usestask['foo']['foo'];
    throw new Exception('[foo][foo] did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usestask', $e->getMessage(), '[foo][foo]');
}

try {
    $reg->usestask['foo']['foo'] = 1;
    throw new Exception('[foo][foo] = 1 did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usestask', $e->getMessage(), '[foo][foo] = 1');
}

try {
    isset($reg->usestask['foo']['foo']);
    throw new Exception('isset([foo][foo]) did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usestask', $e->getMessage(), 'isset([foo][foo])');
}

try {
    unset($reg->usestask['foo']['foo']);
    throw new Exception('unset([foo][foo]) did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usestask', $e->getMessage(), 'unset([foo][foo])');
}


try {
    $reg->usestask['foo'] = 1;
    throw new Exception('[foo] = 1 did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Can only set usestask to a \PEAR2\Pyrus\PackageFile\v2\UsesRoleTask object', $e->getMessage(), '[foo] = 1');
}

try {
    $reg->usestask['foo'] = $reg->usesrole['foo'];
    throw new Exception('task = role did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Cannot set usestask to a usesrole object', $e->getMessage(), 'task = role');
}


try {
    $reg->usestask->foo;
    throw new Exception('->foo did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use [] operator to access usestasks', $e->getMessage(), '->foo');
}

try {
    $reg->usestask->foo = 1;
    throw new Exception('->foo = 1 did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use [] operator to access usestasks', $e->getMessage(), '->foo = 1');
}

try {
    isset($reg->usestask->foo);
    throw new Exception('isset(->foo) did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use [] operator to access usestasks', $e->getMessage(), 'isset(->foo)');
}

try {
    unset($reg->usestask->foo);
    throw new Exception('unset(->foo) did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('use [] operator to access usestasks', $e->getMessage(), 'unset(->foo)');
}

try {
    unset($reg->usestask['foo']->foo);
    throw new Exception('unset([foo]->foo) did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Unknown variable foo requested, should be one of task, package, channel, uri', $e->getMessage(), 'unset([foo]->foo)');
}

try {
    isset($reg->usestask['foo']->foo);
    throw new Exception('isset([foo]->foo) did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Unknown variable foo requested, should be one of task, package, channel, uri', $e->getMessage(), 'isset([foo]->foo)');
}

try {
    $reg->usestask['foo']->foo();
    throw new Exception('[foo]->foo() did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Unknown variable foo, must be one of task, package, channel, uri', $e->getMessage(), '[foo]->foo()');
}

try {
    $a = $reg->usestask['foo']->foo;
    throw new Exception('$a = [foo]->foo did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Unknown variable foo, must be one of task, package, channel, uri', $e->getMessage(), '$a = [foo]->foo');
}

try {
    $reg->usestask['foo']->foo = 1;
    throw new Exception('[foo]->foo = 1 did not fail and should');
} catch (\PEAR2\Pyrus\PackageFile\v2\UsesRoleTask\Exception $e) {
    $test->assertEquals('Unknown variable foo, must be one of task, package, channel, uri', $e->getMessage(), '[foo]->foo = 1');
}

?>
===DONE===
--EXPECT--
===DONE===