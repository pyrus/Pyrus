<?php
/**
 * Create a dependency tree like so:
 *
 * P1-1.0.0
 * P1-1.1.0RC1
 *
 * P2 -> P3
 *
 * P3 -> P1
 */

require __DIR__ . '/../InternetMaker.php';

$maker = new InternetMaker(__DIR__);

$pf = $maker->getPassablePf('P1', '1.0.0');
$pf->files['glooby1'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P1', '1.1.0RC1', 'beta');
$pf->files['glooby1'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P2', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P3']->save();
$pf->files['glooby2'] =  array('role' => 'php');
$maker->makePackage($pf);

$pf = $maker->getPassablePf('P3', '1.0.0');
$pf->dependencies['required']->package['pear2.php.net/P1']->save();
$pf->files['glooby3'] =  array('role' => 'php');
$maker->makePackage($pf);
