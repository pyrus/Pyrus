<?php
/**
 * \pear2\Pyrus\Validate
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Validation class for package.xml - channel-level advanced validation
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus;
class Validate
{
/**#@+
 * Constants for install stage
 */
    const INSTALLING = 1;
    const UNINSTALLING = 2; // this is not bit-mapped like the others
    const NORMAL = 3;
    const DOWNLOADING = 4; // this is not bit-mapped like the others
    const PACKAGING = 7;
/**#@-*/
    var $packageregex = '[A-Za-z][a-zA-Z0-9_]+';
    /**
     * @var \pear2\Pyrus\PackageFileInterface
     */
    var $_packagexml;
    /**
     * @var \pear2\Pyrus\ChannelFileInterface
     */
    var $channel;
    /**
     * @var int one of the \pear2\Pyrus\Validate::* constants
     */
    var $_state = Validate::NORMAL;
    /**
     * @var \pear2\MultiErrors
     */
    protected $failures;

    /**
     * Override this method to handle validation of normal package names
     * @param string
     * @return bool
     * @access protected
     */
    protected function _validPackageName($name)
    {
        return (bool) preg_match('/^' . $this->packageregex . '$/', $name);
    }

    /**
     * @param string package name to validate
     * @param string name of channel-specific validation package
     * @final
     */
    final function validPackageName($name, $validateName = false)
    {
        if ($validateName && strtolower($name) == strtolower($validateName)) {
            return (bool) preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*$/', $name);
        }

        return $this->_validPackageName($name);
    }

    /**
     * This validates a bundle name, and bundle names must conform
     * to the PEAR naming convention, so the method is final and static.
     * @param string
     * @final
     * @static
     */
    static final function validGroupName($name)
    {
        return (bool) preg_match('/^[A-Za-z][a-zA-Z0-9_]+$/', $name);
    }

    /**
     * Determine whether $state represents a valid stability level
     * @param string
     * @return bool
     * @static
     * @final
     */
    static final function validState($state)
    {
        return in_array($state, array('snapshot', 'devel', 'alpha', 'beta', 'stable'));
    }

    /**
     * Get a list of valid stability levels
     * @return array
     * @static
     * @final
     */
    static final function getValidStates()
    {
        return array('snapshot', 'devel', 'alpha', 'beta', 'stable');
    }

    /**
     * Determine whether a version is a properly formatted version number that can be used
     * by version_compare
     * @param string
     * @return bool
     * @static
     * @final
     */
    static final function validVersion($ver)
    {
        return (bool) preg_match('/^\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?$/', $ver);
    }

    /**
     * @param \pear2\Pyrus\PackageFileInterface
     */
    function setPackageFile(PackageFileInterface $pf)
    {
        $this->_packagexml = $pf;
    }

    function setChannel(ChannelFileInterface $chan)
    {
        $this->channel = $chan;
    }

    /**
     * @access private
     */
    protected function _addFailure($field, $reason)
    {
        $this->failures->E_ERROR[] = new Validate\Exception($reason, $field);
    }

    /**
     * @access private
     */
    protected function _addWarning($field, $reason)
    {
        $this->failures->E_WARNING[] = new Validate\Exception($reason, $field);
    }

    function getFailures()
    {
        return $this->failures;
    }

    /**
     * @param int one of the \pear2\Pyrus\Validate::* constants
     */
    function validate($state = null)
    {
        if (!isset($this->_packagexml)) {
            return false;
        }

        if ($state !== null) {
            $this->_state = $state;
        }

        $this->failures = new \pear2\MultiErrors;
        $this->validatePackageName();
        $this->validateVersion();
        $this->validateMaintainers();
        $this->validateDate();
        $this->validateSummary();
        $this->validateDescription();
        $this->validateLicense();
        $this->validateNotes();
        $this->validateTime();
        $this->validateStability();
        $this->validateDependencies();
        $this->validateMainFilelist();
        $this->validateReleaseFilelist();
        //$this->validateGlobalTasks();
        $this->validateChangelog();
        return !((bool) count($this->failures->E_ERROR));
    }

    /**
     * @access protected
     */
    function validatePackageName()
    {
        if ($this->_state == Validate::PACKAGING || $this->_state == Validate::NORMAL) {
            if ($this->_packagexml->extends) {
                $version = $this->_packagexml->version['release'] . '';
                $name = $this->_packagexml->name;
                $test = array_shift($a = explode('.', $version));
                if ($test == '0') {
                    return true;
                }

                $vlen = strlen($test);
                $majver = substr($name, strlen($name) - $vlen);
                while ($majver && !is_numeric($majver{0})) {
                    $majver = substr($majver, 1);
                }

                if ($majver != $test) {
                    $this->_addWarning('package', "package $name extends package " .
                        $this->_packagexml->extends . ' and so the name should ' .
                        'have a postfix equal to the major version like "' .
                        $this->_packagexml->extends . $test . '"');
                    return true;
                } elseif (substr($name, 0, strlen($name) - $vlen) !=
                            $this->_packagexml->extends) {
                    $this->_addWarning('package', "package $name extends package " .
                        $this->_packagexml->extends . ' and so the name must ' .
                        'be an extension like "' . $this->_packagexml->extends .
                        $test . '"');
                    return true;
                }
            }
        }

        $vpackage = $this->channel->getValidationPackage();
        if ($this->validPackageName($this->_packagexml->name, $vpackage['_content'])) {
            return true;
        }

        $this->_addFailure('package', 'package name "' . $this->_packagexml->name . '" is invalid');
        return false;
    }

    /**
     * @access protected
     */
    function validateVersion()
    {
        if ($this->_packagexml->stability['release'] == 'snapshot') {
            // allow any version
            return true;
        }

        if ($this->_state != Validate::PACKAGING) {
            if (!$this->validVersion($this->_packagexml->version['release'])) {
                $this->_addFailure('version',
                    'Invalid version number "' . $this->_packagexml->version['release'] . '"');
                return false;
            }
        }

        $version = $this->_packagexml->version['release'];
        $versioncomponents = explode('.', $version);
        if (count($versioncomponents) != 3) {
            $this->_addWarning('version',
                'A version number should have 3 decimals (x.y.z)');
            return true;
        }

        $name = $this->_packagexml->name;
        // version must be based upon state
        switch ($this->_packagexml->stability['release']) {
            case 'devel' :
                if ($versioncomponents[0] . 'a' == '0a') {
                    return true;
                }

                if ($versioncomponents[0] == 0) {
                    $versioncomponents[0] = '0';
                    $this->_addWarning('version',
                        'version "' . $version . '" should be "' .
                        implode('.' ,$versioncomponents) . '"');
                } else {
                    $this->_addWarning('version',
                        'packages with devel stability must be < version 1.0.0');
                }
                return true;
            break;
            case 'alpha' :
            case 'beta' :
                // check for a package that extends a package,
                // like Foo and Foo2
                if ($this->_state == Validate::PACKAGING) {
                    if (substr($versioncomponents[2], 1, 2) == 'rc') {
                        $this->_addFailure('version', 'Release Candidate versions ' .
                            'must have capital RC, not lower-case rc');
                        return false;
                    }
                }

                if (!$this->_packagexml->extends) {
                    if ($versioncomponents[0] == '1') {
                        if ($versioncomponents[2]{0} == '0') {
                            if ($versioncomponents[2] == '0') {
                                // version 1.*.0000
                                $this->_addWarning('version',
                                    'version 1.' . $versioncomponents[1] .
                                        '.0 probably should not be alpha or beta');
                                return true;
                            } elseif (strlen($versioncomponents[2]) > 1 && !is_numeric($versioncomponents[2])) {
                                // version 1.*.0RC1 or 1.*.0beta24 etc.
                                return true;
                            }

                            // version 1.*.001 or something
                            $this->_addWarning('version', 'version 1.' . $versioncomponents[1] .
                                    '.' . $versioncomponents[2] . ' probably should not be alpha or beta');
                            return true;
                        }

                        $this->_addWarning('version',
                            'bugfix versions (1.3.x where x > 0) probably should ' .
                            'not be alpha or beta');
                        return true;
                    } elseif ($versioncomponents[0] != '0') {
                        $this->_addWarning('version',
                            'major versions greater than 1 are not allowed for packages ' .
                            'without an <extends> tag or an identical postfix (foo2 v2.0.0)');
                        return true;
                    }

                    if ($versioncomponents[0] . 'a' == '0a') {
                        return true;
                    }

                    if ($versioncomponents[0] == 0) {
                        $versioncomponents[0] = '0';
                        $this->_addWarning('version', 'version "' . $version . '" should be "' .
                                                      implode('.' ,$versioncomponents) . '"');
                    }
                } else {
                    $vlen = strlen($versioncomponents[0] . '');
                    $majver = substr($name, strlen($name) - $vlen);
                    while ($majver && !is_numeric($majver{0})) {
                        $majver = substr($majver, 1);
                    }

                    if (($versioncomponents[0] != 0) && $majver != $versioncomponents[0]) {
                        $this->_addWarning('version', 'first version number "' .
                            $versioncomponents[0] . '" must match the postfix of ' .
                            'package name "' . $name . '" (' .
                            $majver . ')');
                        return true;
                    }

                    if ($versioncomponents[0] == $majver) {
                        if ($versioncomponents[2]{0} == '0') {
                            if ($versioncomponents[2] == '0') {
                                // version 2.*.0000
                                $this->_addWarning('version',
                                    "version $majver." . $versioncomponents[1] .
                                        '.0 probably should not be alpha or beta');
                                return false;
                            } elseif (strlen($versioncomponents[2]) > 1 && !is_numeric($versioncomponents[2])) {
                                // version 2.*.0RC1 or 1.*.0beta24 etc.
                                return true;
                            }

                            // version 2.*.001 or something
                            $this->_addWarning('version',
                                'version ' . $version . ' probably should not be alpha or beta');
                            return true;
                        }

                        $this->_addWarning('version',
                            "bugfix versions ($majver.x.y where y > 0) should " .
                            'not be alpha or beta');
                        return true;
                    }

                    if ($versioncomponents[0] . 'a' == '0a') {
                        return true;
                    }

                    if ($versioncomponents[0] == 0) {
                        $versioncomponents[0] = '0';
                        $this->_addWarning('version',
                            'version "' . $version . '" should be "' .
                            implode('.' ,$versioncomponents) . '"');
                    }
                }
                return true;
            break;
            case 'stable' :
                if ($versioncomponents[0] == '0') {
                    $this->_addWarning('version', 'versions less than 1.0.0 cannot ' .
                    'be stable');
                    return true;
                }

                if (!is_numeric($versioncomponents[2])) {
                    if (preg_match('/\d+(rc|a|alpha|b|beta)\d*/i',
                          $versioncomponents[2])) {
                        $this->_addWarning('version', 'version "' . $version . '" or any ' .
                            'RC/beta/alpha version cannot be stable');
                        return true;
                    }
                }

                // check for a package that extends a package,
                // like Foo and Foo2
                if ($this->_packagexml->extends) {
                    $vlen = strlen($versioncomponents[0] . '');
                    $majver = substr($name, strlen($name) - $vlen);
                    while ($majver && !is_numeric($majver{0})) {
                        $majver = substr($majver, 1);
                    }

                    if (($versioncomponents[0] != 0) && $majver != $versioncomponents[0]) {
                        $this->_addWarning('version', 'first version number "' .
                            $versioncomponents[0] . '" must match the postfix of ' .
                            'package name "' . $name . '" (' .
                            $majver . ')');
                        return true;
                    }
                } elseif ($versioncomponents[0] > 1) {
                    $this->_addWarning('version', 'major version x in x.y.z may not be greater than ' .
                        '1 for any package that does not have an <extends> tag');
                }

                return true;
            break;
            default :
                return false;
            break;
        }
    }

    /**
     * @access protected
     */
    function validateMaintainers()
    {
        // maintainers can only be truly validated server-side for most channels
        // but allow this customization for those who wish it
        return true;
    }

    /**
     * @access protected
     */
    function validateDate()
    {
        if ($this->_state == Validate::NORMAL || $this->_state == Validate::PACKAGING) {

            if (!preg_match('/(\d\d\d\d)\-(\d\d)\-(\d\d)/',
                  $this->_packagexml->date, $res) ||
                  count($res) < 4
                  || !checkdate($res[2], $res[3], $res[1])
            ) {
                $this->_addFailure('date', 'invalid release date "' . $this->_packagexml->date . '"');
                return false;
            }


            if ($this->_state == Validate::PACKAGING && $this->_packagexml->date != date('Y-m-d')) {
                $this->_addWarning('date', 'Release Date "' . $this->_packagexml->date . '" is not today');
            }
        }
        return true;
    }

    /**
     * @access protected
     */
    function validateTime()
    {
        if (!$this->_packagexml->time) {
            // default of no time value set
            return true;
        }
        // packager automatically sets time, so only validate if
        // pear validate is called
        if ($this->_state = Validate::NORMAL) {
            if (!preg_match('/\d\d:\d\d:\d\d/', $this->_packagexml->time)) {
                $this->_addFailure('time', 'invalid release time "' . $this->_packagexml->time . '"');
                return false;
            }

            if (strtotime($this->_packagexml->time) == -1) {
                $this->_addFailure('time', 'invalid release time "' . $this->_packagexml->time . '"');
                return false;
            }
        }

        return true;
    }

    /**
     * @access protected
     */
    function validateStability()
    {
        $ret = true;
        $packagestability = $this->_packagexml->stability['release'];
        $apistability = $this->_packagexml->stability['api'];
        if (!self::validState($packagestability)) {
            $this->_addFailure('state', 'invalid release stability "' .
                $this->_packagexml->stability['release'] . '", must be one of: ' .
                implode(', ', self::getValidStates()));
            $ret = false;
        }

        $apistates = self::getValidStates();
        array_shift($apistates); // snapshot is not allowed
        if (!in_array($apistability, $apistates)) {
            $this->_addFailure('state', 'invalid API stability "' .
                $this->_packagexml->stability['api'] . '", must be one of: ' .
                implode(', ', $apistates));
            $ret = false;
        }

        return $ret;
    }

    /**
     * @access protected
     */
    function validateSummary()
    {
        return true;
    }

    /**
     * @access protected
     */
    function validateDescription()
    {
        return true;
    }

    /**
     * @access protected
     */
    function validateLicense()
    {
        return true;
    }

    /**
     * @access protected
     */
    function validateNotes()
    {
        return true;
    }

    /**
     * for package.xml 2.0 only - channels can't use package.xml 1.0
     * @access protected
     */
    function validateDependencies()
    {
        return true;
    }

    /**
     * for package.xml 2.0 only
     * @access protected
     */
    function validateMainFilelist()
    {
        return true; // placeholder for now
    }

    /**
     * for package.xml 2.0 only
     * @access protected
     */
    function validateReleaseFilelist()
    {
        return true; // placeholder for now
    }

    /**
     * @access protected
     */
    function validateChangelog()
    {
        return true;
    }
}