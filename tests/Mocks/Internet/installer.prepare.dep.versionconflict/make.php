<?php
/**
 * Create a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0 (1.2.3 is latest version)
 *
 * P2 1.2.3 -> P3
 *          -> P5
 *
 * P2 1.2.2 -> P3
 *
 * P3
 *
 * P4 -> P2 != 1.2.3
 *
 * P5
 *
 * This causes a conflict when P1 and P4 are installed that must resolve to installing:
 *
 * P1
 * P2 1.2.2
 * P3
 * P4
 */

require __DIR__ . '/../InternetMaker.php';

$maker = new InternetMaker(__DIR__);

$cat = pear2\SimpleChannelServer\Categories::create('Category 1', 'First Category')
                                           ->create('Category 2', 'Second Category');

$cat->link('P1', 'Category 1');
$cat->link('P2', 'Category 2');
$cat->link('P3', 'Category 1');
$cat->link('P4', 'Category 2');
$cat->link('P5', 'Category 1');

$pf = $maker->getPassablePf('P1', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P2']->min('1.2.0');
$pf->files['glooby1'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P2', '0.9.0', 'beta');
$pf->files['glooby2'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P2', '1.2.2');
$pf->dependencies['required']->package['pear2.php.net/P3']->save();
$pf->files['glooby2'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P2', '1.2.3');
$pf->dependencies['required']->package['pear2.php.net/P3']->save();
$pf->dependencies['required']->package['pear2.php.net/P5']->save();
$pf->files['glooby2'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P3', '1.0.0');
$pf->files['glooby3'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P4', '1.0.0');
$pf->files['glooby4'] =  array('role' => 'php');
$pf->dependencies['required']->package['pear2.php.net/P2']->min('1.2.0')->exclude('1.2.3');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P5', '1.0.0', 'stable');
$pf->files['glooby5'] =  array('role' => 'php');
$maker->makePackage($pf);