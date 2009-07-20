<?php
/**
 * Create a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0
 * P3 -> P2 <= 1.1.0
 *
 * to test composite dep failure
 */

require __DIR__ . '/../InternetMaker.php';

$maker = new InternetMaker(__DIR__);

$pf = $maker->getPassablePf('P1', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P2']->min('1.2.0');
$pf->files['glooby1'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P2', '1.2.1');
$pf->files['glooby2'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P3', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P2']->max('1.0.0');
$pf->files['glooby3'] =  array('role' => 'php');
$maker->makePackage($pf);
