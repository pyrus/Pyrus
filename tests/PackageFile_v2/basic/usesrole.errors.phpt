--TEST--
PackageFile v2: test package.xml usesrole property errors
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new PEAR2_Pyrus_PackageFile_v2;

try {
    $reg->usesrole['foo']['foo'];
    throw new Exception('[foo][foo] did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usesrole', $e->getMessage(), '[foo][foo]');
}

try {
    $reg->usesrole['foo']['foo'] = 1;
    throw new Exception('[foo][foo] = 1 did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usesrole', $e->getMessage(), '[foo][foo] = 1');
}

try {
    isset($reg->usesrole['foo']['foo']);
    throw new Exception('isset([foo][foo]) did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usesrole', $e->getMessage(), 'isset([foo][foo])');
}

try {
    unset($reg->usesrole['foo']['foo']);
    throw new Exception('unset([foo][foo]) did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use -> operator to access properties of a usesrole', $e->getMessage(), 'unset([foo][foo])');
}


try {
    $reg->usesrole['foo'] = 1;
    throw new Exception('[foo] = 1 did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Can only set usesrole to a PEAR2_Pyrus_PackageFile_v2_UsesRoleTask object', $e->getMessage(), '[foo] = 1');
}

try {
    $reg->usesrole['foo'] = $reg->usestask['foo'];
    throw new Exception('role = task did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Cannot set usesrole to a usestask object', $e->getMessage(), 'role = task');
}


try {
    $reg->usesrole->foo;
    throw new Exception('->foo did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use [] operator to access usesroles', $e->getMessage(), '->foo');
}

try {
    $reg->usesrole->foo = 1;
    throw new Exception('->foo = 1 did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use [] operator to access usesroles', $e->getMessage(), '->foo = 1');
}

try {
    isset($reg->usesrole->foo);
    throw new Exception('isset(->foo) did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use [] operator to access usesroles', $e->getMessage(), 'isset(->foo)');
}

try {
    unset($reg->usesrole->foo);
    throw new Exception('unset(->foo) did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('use [] operator to access usesroles', $e->getMessage(), 'unset(->foo)');
}

try {
    unset($reg->usesrole['foo']->foo);
    throw new Exception('unset([foo]->foo) did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Unknown variable foo requested, should be one of role, package, channel, uri', $e->getMessage(), 'unset([foo]->foo)');
}

try {
    isset($reg->usesrole['foo']->foo);
    throw new Exception('isset([foo]->foo) did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Unknown variable foo requested, should be one of role, package, channel, uri', $e->getMessage(), 'isset([foo]->foo)');
}

try {
    $reg->usesrole['foo']->foo();
    throw new Exception('[foo]->foo() did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Unknown variable foo, must be one of role, package, channel, uri', $e->getMessage(), '[foo]->foo()');
}

try {
    $a = $reg->usesrole['foo']->foo;
    throw new Exception('$a = [foo]->foo did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Unknown variable foo, must be one of role, package, channel, uri', $e->getMessage(), '$a = [foo]->foo');
}

try {
    $reg->usesrole['foo']->foo = 1;
    throw new Exception('[foo]->foo = 1 did not fail and should');
} catch (PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception $e) {
    $test->assertEquals('Unknown variable foo, must be one of role, package, channel, uri', $e->getMessage(), '[foo]->foo = 1');
}

?>
===DONE===
--EXPECT--
===DONE===