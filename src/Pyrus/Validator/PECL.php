<?php
/**
 * Channel Validator for the pecl.php.net channel
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Channel Validator for the pecl.php.net channel
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Validator;
class PECL extends \Pyrus\Validate
{
    function validateVersion()
    {
        if ($this->_state == \Pyrus\Validate::PACKAGING) {
            $version = $this->_packagexml->version['release'];
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
        if (in_array($this->_packagexml->getPackageType(), array('extsrc', 'zendextsrc'))) {
            $package  = $this->_packagexml->name;
            $provides = $this->_packagexml->providesextension;
            if (strtolower($package) != strtolower($provides)) {
                $this->_addWarning('providesextension', 'package name "' .
                    $package . '" is different from extension name "' .
                    $provides . '"');
            }
        }

        return $ret;
    }
}