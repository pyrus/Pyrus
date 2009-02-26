<?php
$package = new PEAR2_Pyrus_Package(__DIR__.'/../../Registry/AllRegistries/package.xml');

$info = $package->getInternalPackage()->getPackageFile()->info;
// set up some fake dependencies so we can test this too

$deps = $info->dependencies;
$deps['required']->package['pear.php.net/File']->min('1.2.0');
$deps['required']->subpackage['pear2.php.net/Foo']->min('1.0.0')->max('2.0.0')
                                        ->recommended('1.2.0')->exclude('1.1.0')->exclude('1.1.1');
$deps['required']->package['pear.php.net/Other']->conflicts();

$deps['optional']->package['pear.php.net/PEAR']->min('1.7.0');
$deps['optional']->subpackage['pear2.php.net/Bar']->min('1.5.0');
$deps['required']->package['pecl.php.net/PackageName']->min('1.1.0')->max('1.2.0')->recommended('1.1.1')
            ->exclude('1.1.0a1')->exclude('1.1.0a2')->providesextension('packagename');

$deps['required']->extension['phar']->min('2.0.0');
$deps['required']->os['windows'] = true;
$deps['required']->os['vista'] = false;
$deps['required']->arch['i386'] = true;

$group = $deps['group']->name;
$group->hint = 'Install optional stuff as a group';
$group->package['pear2.php.net/PackageName1']->save();
$group->package['pear2.php.net/PackageName2']->min('1.2.0');
$group->subpackage['pear2.php.net/PackageName3']->recommended('1.2.1');
$group->extension['extension']->save();
$group->package['pecl.php.net/PackageName']->min('1.1.0')->max('1.2.0')->recommended('1.1.1')
            ->exclude('1.1.0a1')->exclude('1.1.0a2')->providesextension('packagename');
