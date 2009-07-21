<?php
/**
 * Create a dependency tree like so:
 *
 * P1 -> P2
 *
 * P2 -> P3
 *
 * P3 -> P4
 *
 * P4-1.0.0 -> P1 <= 1.2.0
 * P4-1.1.0 -> P1
 *
 * and P1 1.3.0 exists
 */

require __DIR__ . '/../InternetMaker.php';

$maker = new InternetMaker(__DIR__);

$pf = $maker->getPassablePf('P1', '1.2.0');
$pf->dependencies['required']->package['pear2.php.net/P2']->save();
$pf->files['glooby1'] =  array('role' => 'php');
$maker->makePackage($pf);
$pf->version['release'] = '1.3.0';
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P2', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P3']->save();
$pf->files['glooby2'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P3', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P4']->save();
$pf->files['glooby3'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P4', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P1']->max('1.2.0');
$pf->files['glooby4'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P4', '1.1.0');
$pf->dependencies['required']->package['pear2.php.net/P1']->save();
$pf->files['glooby4'] =  array('role' => 'php');
$maker->makePackage($pf);
