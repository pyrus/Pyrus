<?php
/**
 * \pear2\Pyrus\Package\DuplicateDependency
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
 * Class represents a set of package dependencies on the same package that are
 * possibly different versions.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\Package;
class DuplicateDependency extends \pear2\Pyrus\Package
{
    protected $primaryNode;
    protected $duplicates = array();
    protected $explicitVersion = false;
    protected $allSameVersion = false;

    function __construct(array $duplicates)
    {
        $this->duplicates = $duplicates;
        $this->scanDuplicates();
    }

    function scanDuplicates()
    {
        $high = false;
        $this->allSameVersion = false;
        foreach ($this->duplicates as $package) {
            if (!$high) {
                $high = $package->version['release'];
                $this->primaryNode = $package;
                $this->allSameVersion = true;
            } else {
                $compare = version_compare($high, $package->version['release']);
                if ($compare == 1) {
                    $high = $package->version['release'];
                    $this->primaryNode = $package;
                    $this->allSameVersion = false;
                } elseif ($compare) {
                    $this->allSameVersion = false;
                }
            }
            if ($package->getExplicitVersion()) {
                if ($this->explicitVersion && $this->explicitVersion != $package->getExplicitVersion()) {
                    // XX TODO: this error message needs to be a whole lot smarter eventually
                    throw new Dependency\Set\Exception('Impossible dependency conflict - two explicit versions requested');
                }
                $this->explicitVersion = $package->getExplicitVersion();
                $this->primaryNode = $package;
                break;
            }
        }
    }

    function allSameVersion()
    {
        return $this->allSameVersion;
    }

    function isExplicitVersion()
    {
        return $this->explicitVersion;
    }

    function primaryNode()
    {
        return $this->primaryNode;
    }

    function failCurrent()
    {
        foreach ($this->duplicates as $i => $dupe) {
            if ($dupe === $this->primaryNode) {
                unset($this->duplicates[$i]);
                $this->duplicates = array_values($this->duplicates);
                return $this->scanDuplicates();
            }
        }
    }

    function possible()
    {
        if (!count($this->duplicates)) {
            return false;
        }
        return true;
    }
}