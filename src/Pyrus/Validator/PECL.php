<?php
/**
 * Channel Validator for the pecl.php.net channel
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
 * Channel Validator for the pecl.php.net channel
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Validator_PECL extends PEAR2_Pyrus_Validate
{
    function validateVersion()
    {
        if ($this->_state == PEAR2_Pyrus_Validate::PACKAGING) {
            $version = $this->_packagexml->getVersion();
            $versioncomponents = explode('.', $version);
            $last = array_pop($versioncomponents);
            if (substr($last, 1, 2) == 'rc') {
                $this->_addFailure('version', 'Release Candidate versions must have ' .
                'upper-case RC, not lower-case rc');
                return false;
            }
        }
        return true;
    }

    function validatePackageName()
    {
        $ret = parent::validatePackageName();
        if ($this->_packagexml->getPackageType() == 'extsrc' ||
              $this->_packagexml->getPackageType() == 'zendextsrc') {
            if (strtolower($this->_packagexml->getPackage()) !=
                  strtolower($this->_packagexml->getProvidesExtension())) {
                $this->_addWarning('providesextension', 'package name "' .
                    $this->_packagexml->getPackage() . '" is different from extension name "' .
                    $this->_packagexml->getProvidesExtension() . '"');
            }
        }
        return $ret;
    }
}
?>