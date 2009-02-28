--TEST--
PackageFile v2: test package.xml dependencies property, errors
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$package = new PEAR2_Pyrus_PackageFile_v2; // simulate registry package using packagefile
try {
    $a = $package->dependencies->min;
    throw new Exception('getting min worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot retrieve dependency type, choose $pf->dependencies[\'required\']->' .
                        'min or $pf->dependencies[\'optional\']->min', $e->getMessage(), 'getting min');
}
try {
    $a = $package->dependencies->min = 1;
    throw new Exception('setting min worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set dependency type, choose $pf->dependencies[\'required\']->' .
                        'min or $pf->dependencies[\'optional\']->min', $e->getMessage(), 'setting min');
}
try {
    $a = $package->dependencies['required']->min;
    throw new Exception('unknown dep type worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown dependency type: min', $e->getMessage(), 'Unknown dep type');
}

try {
    $package->dependencies['required']->min = 1;
    throw new Exception('unknown dep type set worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown dependency type: min', $e->getMessage(), 'Unknown dep type set');
}

try {
    $a = $package->dependencies['optional']->arch;
    throw new Exception('getting optional->arch worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('arch dependency is not supported as an optional dependency',
                        $e->getMessage(), 'getting optional->arch');
}
try {
    $a = $package->dependencies['optional']->os;
    throw new Exception('getting optional->os worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('os dependency is not supported as an optional dependency',
                        $e->getMessage(), 'getting optional->os');
}

try {
    $package->dependencies['optional']->arch = 1;
    throw new Exception('setting optional->arch worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('arch dependency is not supported as an optional dependency',
                        $e->getMessage(), 'setting optional->arch');
}
try {
    $package->dependencies['optional']->os = 1;
    throw new Exception('setting optional->os worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('os dependency is not supported as an optional dependency',
                        $e->getMessage(), 'setting optional->os');
}

try {
    $package->dependencies['required']->arch = 1;
    throw new Exception('setting required->arch to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set arch to PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep object',
                        $e->getMessage(), 'setting required->arch to 1');
}
try {
    $package->dependencies['required']->os = 1;
    throw new Exception('setting required->os to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set os to PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep object',
                        $e->getMessage(), 'setting required->os to 1');
}

try {
    $package->dependencies['group']->blah->arch = 1;
    throw new Exception('setting group->arch worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Only package, subpackage, and ' .
                        'extension dependencies are supported in dependency groups, asked for arch',
                        $e->getMessage(), 'setting group->arch');
}
try {
    $package->dependencies['group']->blah->os = 1;
    throw new Exception('setting group->os worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Only package, subpackage, and ' .
                        'extension dependencies are supported in dependency groups, asked for os',
                        $e->getMessage(), 'setting group->os');
}

try {
    $a = $package->dependencies['group']->blah->arch;
    throw new Exception('getting group->arch worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Only package, subpackage, and ' .
                        'extension dependencies are supported in dependency groups, asked for arch',
                        $e->getMessage(), 'getting group->arch');
}
try {
    $a = $package->dependencies['group']->blah->os;
    throw new Exception('getting group->os worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Only package, subpackage, and ' .
                        'extension dependencies are supported in dependency groups, asked for os',
                        $e->getMessage(), 'getting group->os');
}

try {
    $package->dependencies['required']->package = 1;
    throw new Exception('setting required->package to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set package to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting required->package to 1');
}
try {
    $package->dependencies['required']->subpackage = 1;
    throw new Exception('setting required->subpackage to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set subpackage to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting required->subpackage to 1');
}
try {
    $package->dependencies['required']->extension = 1;
    throw new Exception('setting required->extension to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set extension to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting required->extension to 1');
}

try {
    $package->dependencies['optional']->package = 1;
    throw new Exception('setting optional->package to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set package to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting optional->package to 1');
}
try {
    $package->dependencies['optional']->subpackage = 1;
    throw new Exception('setting optional->subpackage to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set subpackage to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting optional->subpackage to 1');
}
try {
    $package->dependencies['optional']->extension = 1;
    throw new Exception('setting optional->extension to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set extension to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting optional->extension to 1');
}

try {
    $package->dependencies['group']->groupname->package = 1;
    throw new Exception('setting group->package to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set package to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting group->package to 1');
}
try {
    $package->dependencies['group']->groupname->subpackage = 1;
    throw new Exception('setting group->subpackage to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set subpackage to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting group->subpackage to 1');
}
try {
    $package->dependencies['group']->groupname->extension = 1;
    throw new Exception('setting group->extension to 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set extension to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), 'setting group->extension to 1');
}
$test->assertEquals(false, isset($package->dependencies->oops), 'isset on dependencies');
$test->assertEquals(true, isset($package->dependencies['required']), 'isset 1');
unset($package->dependencies['required']);
$test->assertEquals(false, isset($package->dependencies['required']), 'isset 2');

try {
    $a = $package->dependencies['required']['required'];
    throw new Exception('getting required->required worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot access $pf->dependencies[\'required\'][\'required\']',
                        $e->getMessage(), 'getting required->required');
}

try {
    $a = $package->dependencies['required']['required'] = 1;
    throw new Exception('setting required->required worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set $pf->dependencies[\'required\'][\'required\']',
                        $e->getMessage(), 'setting required->required');
}

try {
    $a = $package->dependencies['required']->package['channel/Foobar']->gronk(1);
    throw new Exception('setting gronk worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown variable gronk, should be one of name, channel, uri, min, max, recommended, exclude, providesextension, conflicts',
                        $e->getMessage(), 'setting gronk');
}

try {
    $package->dependencies['optional']->package['channel/Foobar']->conflicts();
    throw new Exception('setting conflicts worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown variable conflicts, should be one of name, channel, uri, min, max, recommended, exclude, providesextension',
                        $e->getMessage(), 'setting optional->conflicts');
}

try {
    $package->dependencies['required']->php->oops();
    throw new Exception('oops worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown variable oops(), must be one of min, max, exclude',
                        $e->getMessage(), 'setting optional->conflicts');
}

try {
    $package->dependencies['required'] = 1;
    throw new Exception('required = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set required to anything' .
                            ' but a PEAR2_Pyrus_PackageFile_v2_Dependencies object',
                        $e->getMessage(), 'required = 1');
}

try {
    $package->dependencies['optional'] = 1;
    throw new Exception('optional = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set optional to anything' .
                            ' but a PEAR2_Pyrus_PackageFile_v2_Dependencies object',
                        $e->getMessage(), 'optional = 1');
}

try {
    $package->dependencies['group'] = 1;
    throw new Exception('group = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set group to anything' .
                            ' but a PEAR2_Pyrus_PackageFile_v2_Dependencies_Group object',
                        $e->getMessage(), 'group = 1');
}

try {
    $package->dependencies['required']->arch->foo(1);
    throw new Exception('foo = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown method foo called',
                        $e->getMessage(), 'foo = 1');
}

try {
    $package->dependencies['required']->os->foo2(1);
    throw new Exception('foo2 = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Unknown method foo2 called',
                        $e->getMessage(), 'foo2 = 1');
}

try {
    $package->dependencies['group']->groupname->foo = null;
    throw new Exception('group->foo = null worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Only package, subpackage, and ' .
                                    'extension dependencies are supported in dependency groups, asked for foo',
                        $e->getMessage(), 'group->foo = null');
}

try {
    $package->dependencies['group']->groupname->hint = 1;
    throw new Exception('group->hint = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('hint must be a string, was integer',
                        $e->getMessage(), 'group->hint = 1');
}

try {
    $a = $package->dependencies['required']->package['channel/package']['min'];
    throw new Exception('[min] worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use -> operator to access dependency properties',
                        $e->getMessage(), '[min]');
}

try {
    $package->dependencies['required']->package['channel/package']['min'];
    throw new Exception('[min] worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use -> operator to access dependency properties',
                        $e->getMessage(), '[min]');
}

try {
    $package->dependencies['required']->package['channel/package']['min'];
    throw new Exception('[min] worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use -> operator to access dependency properties',
                        $e->getMessage(), '[min]');
}

try {
    $package->dependencies['required']->package['channel/package']['min'] = 1;
    throw new Exception('[min] = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use -> operator to access dependency properties',
                        $e->getMessage(), '[min] = 1');
}

try {
    $package->dependencies['required']->package['channel/package'] = 1;
    throw new Exception('[channel/package] = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set $pf->dependencies[\'required\']->package[\'channel/package' .
                '\'] to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), '[channel/package] = 1');
}

try {
    $package->dependencies['required']->package['channel/package'] = 1;
    throw new Exception('[channel/package] = 1 worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Can only set $pf->dependencies[\'required\']->package[\'channel/package' .
                '\'] to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object',
                        $e->getMessage(), '[channel/package] = 1');
}

$fake = new PEAR2_Pyrus_PackageFile_v2;

try {
    $package->dependencies['required']->package['channel/package'] = $fake->dependencies['required']->package['oops/notsame']->min('1.0.0');
    throw new Exception('[channel/package] = oops/notsame worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set channel/package' .
                ' to oops/notsame, use $pf->dependencies[\'required\']->package[] to set a new value',
                        $e->getMessage(), '[channel/package] = oops/notsame');
}

try {
    $package->dependencies['required']->subpackage['channel/subpackage'] = $fake->dependencies['required']->subpackage['oops/notsame']->min('1.0.0');
    throw new Exception('[channel/subpackage] = oops/notsame worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set channel/subpackage' .
                ' to oops/notsame, use $pf->dependencies[\'required\']->subpackage[] to set a new value',
                        $e->getMessage(), '[channel/subpackage] = oops/notsame');
}

try {
    $package->dependencies['required']->extension['extension'] = $fake->dependencies['required']->extension['notsame']->min('1.0.0');
    throw new Exception('[extension] = notsame worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set extension' .
                ' to notsame, use $pf->dependencies[\'required\']->extension[] to set a new value',
                        $e->getMessage(), '[extension] = notsame');
}

try {
    $package->dependencies['required']->extension['extension'] = $fake->dependencies['required']->package['oops/notsame']->min('1.0.0');
    throw new Exception('[extension] = [package] worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set extension dependency to package dependency',
                        $e->getMessage(), '[extension] = [package]');
}

try {
    $a = $package->dependencies['required']->package['oops'];
    throw new Exception('[oops] worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot access "oops", must use "channel/package" to specify a package dependency to access',
                        $e->getMessage(), '[oops]');
}

try {
    $package->dependencies['required']->package['oops'] = $fake->dependencies['required']->package['ok/name'];
    throw new Exception('[oops] = ok/name worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot set "oops", must use "channel/package" to specify a package dependency to set',
                        $e->getMessage(), '[oops] = ok/name');
}

try {
    isset($package->dependencies['required']->package['oops/toomany']['fooey']);
    throw new Exception('isset([oops/toomany][fooey]) worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use -> operator to access dependency properties',
                        $e->getMessage(), 'isset([oops/toomany][fooey])');
}

try {
    unset($package->dependencies['required']->package['oops/toomany']['fooey']);
    throw new Exception('unset([oops/toomany][fooey]) worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use -> operator to access dependency properties',
                        $e->getMessage(), 'unset([oops/toomany][fooey])');
}

try {
    $a = $package->dependencies['required']->package->fooey;
    throw new Exception('$a = fooey worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use [] operator to access packages',
                        $e->getMessage(), '$a = fooey');
}

try {
    $a = $package->dependencies['required']->package->fooey();
    throw new Exception('$a = fooey() worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Use [] operator to access packages',
                        $e->getMessage(), '$a = fooey()');
}

try {
    $a = $package->dependencies['required']->extension['fooey']->name('sneaky');
    throw new Exception('name(sneaky) worked and should not');
} catch (PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception $e) {
    $test->assertEquals('Cannot change dependency name, use unset() to remove the old dependency',
                        $e->getMessage(), 'name(sneaky)');
}

?>
===DONE===
--EXPECT--
===DONE===