<?php
/**
 * PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * filtered iterator for file installation
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter extends FilterIterator
{
    static private $_parent;
    static private $_installGroup;
    static function setParent(PEAR2_Pyrus_PackageFile_v2 $parent)
    {
        self::$_parent = $parent;
        $errs = new PEAR2_MultiErrors;
        $depchecker = new PEAR2_Pyrus_Dependency_Validator(
            array('channel' => self::$_parent->channel,
                  'package' => self::$_parent->package),
            PEAR2_Pyrus_Validate::INSTALLING, $errs);
        foreach (self::$_parent->installGroup as $instance) {
            try {
                if (isset($instance['installconditions'])) {
                    $installconditions = $instance['installconditions'];
                    if (is_array($installconditions)) {
                        foreach ($installconditions as $type => $conditions) {
                            if (!isset($conditions[0])) {
                                $conditions = array($conditions);
                            }
                            foreach ($conditions as $condition) {
                                $ret = $depchecker->{"validate{$type}Dependency"}($condition);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // can't use this release
                continue;
            }
            $release = array('install' => array(), 'ignore' => array());
            // this is the release to use
            if (isset($instance['filelist'])) {
                // ignore files
                if (isset($instance['filelist']['ignore'])) {
                    $ignore = isset($instance['filelist']['ignore'][0]) ?
                        $instance['filelist']['ignore'] :
                        array($instance['filelist']['ignore']);
                    foreach ($ignore as $ig) {
                        $release['ignore'][$ig['attribs']['name']] = true;
                    }
                }
                // install files as this name
                if (isset($instance['filelist']['install'])) {
                    $installas = isset($instance['filelist']['install'][0]) ?
                        $instance['filelist']['install'] :
                        array($instance['filelist']['install']);
                    foreach ($installas as $as) {
                        $release['install'][$as['attribs']['name']] =
                            $as['attribs']['as'];
                    }
                }
            }
            self::$_installGroup = $release;
            return;
        }
    }

    function current()
    {
        $file = $this->key();
        $curfile = parent::current();
        if (isset(self::$_installGroup['install'][$file])) {
            // add the install-as attribute for these files
            $curfile['attribs']['install-as'] =
                self::$_installGroup['install'][$file];
        }
        if ($b = self::$_parent->getBaseInstallDir($file)) {
            $curfile['attribs']['baseinstalldir'] = $b;
        }
        return new PEAR2_Pyrus_PackageFile_v2Iterator_FileTag($curfile,
            dirname($file), self::$_parent);
    }

    function accept()
    {
        $file = $this->getInnerIterator()->key();
        if (isset(self::$_installGroup['ignore'][$file])) {
            // skip ignored files
            return false;
        }
        return true;
    }
}