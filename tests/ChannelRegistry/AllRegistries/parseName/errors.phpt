--TEST--
PEAR2_Pyrus_ChannelRegistry_Base::parseName()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$creg = new PEAR2_Pyrus_ChannelRegistry(__DIR__ . '/testit');
$testit = function($name, $message, $why) use ($test, $creg) {
    try {
        $creg->parseName($name);
        throw new Exception($name . ' should have failed and did not');
    } catch ( PEAR2_Pyrus_ChannelRegistry_Exception $e) {
        $test->assertEquals('Unable to process package name', $e->getMessage(),
                            'exception message');
        $test->assertEquals('PEAR2_Pyrus_ChannelRegistry_ParseException',
                            get_class($e->getCause()), 'cause class ' . $name);
        $test->assertEquals($message, $e->getCause()->getMessage(),
                            'message ' . $name);
        $test->assertEquals($why, $e->getCause()->why, 'why '. $name);
    }
};
$testit('ftp://oops', 'parsePackageName(): only channel:// uris may ' .
                        'be downloaded, not "ftp://oops"', 'scheme');
$testit('https://', 'parsePackageName(): array $param ' .
                        'must contain a valid package name in "https://"', 'path');
$testit('/oops', 'parsePackageName(): this is not ' .
                        'a package name, it begins with "/" in "/oops"', 'invalid');
$testit('oops-too-many', 'parseName(): only one version/state ' .
                        'delimiter "-" is allowed in "oops-too-many"', 'invalid');
$testit('pear2/^$@', 'parseName(): invalid package name "' .
                        '^$@" in "pear2/^$@"', 'package');
$testit('pear2/foo#$%', 'parseName(): dependency group "' .
                        '$%" is not a valid group name in "pear2/foo#$%"', 'group');
$testit('pear2/foo-*', 'parseName(): "*' .
                        '" is neither a valid version nor a valid state in "' .
                        'pear2/foo-*"', 'version/state');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===