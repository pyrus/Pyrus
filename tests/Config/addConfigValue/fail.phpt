--TEST--
\Pyrus\Config::addConfigValue() failure
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(''); // disable include_path cascading for simplicity
$a = $configclass::singleton($testpath, $testpath . '/blah');
foreach (array('registry',
                                        'channelregistry',
                                        'systemvars',
                                        'uservars',
                                        'mainsystemvars',
                                        'mainuservars',
                                        'userfile',
                                        'path') as $var) {
    try {
        $a->addConfigValue($var, 'fail');
        throw new Exception($var . ' should have failed!');
    } catch (\Pyrus\Config\Exception $e) {
        $test->assertEquals('Invalid custom configuration variable ' . $var .
                            ', already in use for retrieving configuration information', $e->getMessage(), $var . ' exception message');
    }
}
try {
    $a->addConfigValue("a\n!", 'sneaky');
    throw new Exception('sneaky should have failed!');
} catch (\Pyrus\Config\Exception $e) {
    $test->assertEquals('Invalid custom configuration variable name "'.  "a\n!" . '"', $e->getMessage(), 'sneaky message');
}
try {
    $a->addConfigValue("a\n!", 'sneaky', 'user');
    throw new Exception('sneaky user should have failed!');
} catch (\Pyrus\Config\Exception $e) {
    $test->assertEquals('Invalid custom configuration variable name "'.  "a\n!" . '"', $e->getMessage(), 'sneaky user message');
}
foreach ($a->mainsystemvars as $var) {
    try {
        $a->addConfigValue($var, 'fail');
        throw new Exception($var . ' system should have failed!');
    } catch (\Pyrus\Config\Exception $e) {
        $test->assertEquals('Cannot override existing configuration value "' . $var . '"', $e->getMessage(),
                            $var . ' system exception message');
    }
}
foreach ($a->mainsystemvars as $var) {
    try {
        $a->addConfigValue($var, 'fail', 'user');
        throw new Exception($var . ' system user should have failed!');
    } catch (\Pyrus\Config\Exception $e) {
        $test->assertEquals('Cannot override existing configuration value "' . $var . '" with user value', $e->getMessage(),
                            $var . ' system user exception message');
    }
}
foreach ($a->mainuservars as $var) {
    try {
        $a->addConfigValue($var, 'fail');
        throw new Exception($var . ' user should have failed!');
    } catch (\Pyrus\Config\Exception $e) {
        $test->assertEquals('Cannot override existing user configuration value "' . $var . '" with system value', $e->getMessage(),
                            $var . ' user exception message');
    }
}
foreach ($a->mainuservars as $var) {
    try {
        $a->addConfigValue($var, 'fail', 'user');
        throw new Exception($var . ' user user should have failed!');
    } catch (\Pyrus\Config\Exception $e) {
        $test->assertEquals('Cannot override existing user configuration value "' . $var . '"', $e->getMessage(),
                            $var . ' user user exception message');
    }
}

$a->addConfigValue('foo', 'booya');
$a->addConfigValue('foo2', 'booya2', 'user');

try {
    $a->addConfigValue('foo', 'fail');
    throw new Exception('foo should have failed!');
} catch (\Pyrus\Config\Exception $e) {
    $test->assertEquals('Cannot override existing custom configuration value "foo"', $e->getMessage(),
                        'foo exception message');
}

try {
    $a->addConfigValue('foo', 'fail', 'user');
    throw new Exception('foo user should have failed!');
} catch (\Pyrus\Config\Exception $e) {
    $test->assertEquals('Cannot override existing custom configuration value "foo" with user value', $e->getMessage(),
                        'foo user exception message');
}

try {
    $a->addConfigValue('foo2', 'fail');
    throw new Exception('foo2 should have failed!');
} catch (\Pyrus\Config\Exception $e) {
    $test->assertEquals('Cannot override existing custom user configuration value "foo2" with system value', $e->getMessage(),
                        'foo2 exception message');
}

try {
    $a->addConfigValue('foo2', 'fail', 'user');
    throw new Exception('foo2 user should have failed!');
} catch (\Pyrus\Config\Exception $e) {
    $test->assertEquals('Cannot override existing custom user configuration value "foo2"', $e->getMessage(),
                        'foo2 user exception message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
