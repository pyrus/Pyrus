<?php
/**
 * \Pyrus\Validate
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Validation class for package.xml - channel-level advanced validation
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
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
     * @var \Pyrus\PackageFileInterface
     */
    var $_packagexml;

    /**
     * @var \Pyrus\ChannelFileInterface
     */
    var $channel;

    /**
     * @var int one of the \Pyrus\Validate::* constants
     */
    var $_state = Validate::NORMAL;

    /**
     * @var \PEAR2\MultiErrors
     */
    protected $failures;

    /**
     * Override this method to handle validation of normal package names
     * @param string
     * @return bool
     */
    protected function _validPackageName($name)
    {
        return (bool) preg_match('/^' . $this->packageregex . '$/', $name);
    }

    /**
     * @param string package name to validate
     * @param string name of channel-specific validation package
     */
    final public function validPackageName($name, $validateName = false)
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
     */
    static final public function validGroupName($name)
    {
        return (bool) preg_match('/^[A-Za-z][a-zA-Z0-9_]+$/', $name);
    }

    /**
     * Determine whether $state represents a valid stability level
     * @param string
     * @return bool
     */
    static final public function validState($state)
    {
        return in_array($state, array('snapshot', 'devel', 'alpha', 'beta', 'stable'));
    }

    /**
     * Get a list of valid stability levels
     * @return array
     */
    static final public function getValidStates()
    {
        return array('snapshot', 'devel', 'alpha', 'beta', 'stable');
    }

    /**
     * Determine whether a version is a properly formatted version number that can be used
     * by version_compare
     * @param string
     * @return bool
     */
    static final public function validVersion($ver)
    {
        return (bool) preg_match('/^\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?$/', $ver);
    }

    /**
     * @param \Pyrus\PackageFileInterface
     */
    public function setPackageFile(PackageFileInterface $pf)
    {
        $this->_packagexml = $pf;
    }

    public function setChannel(ChannelFileInterface $chan)
    {
        $this->channel = $chan;
    }

    protected function _addFailure($field, $reason)
    {
        $this->failures->E_ERROR[] = new Validate\Exception($reason, $field);
    }

    protected function _addWarning($field, $reason)
    {
        $this->failures->E_WARNING[] = new Validate\Exception($reason, $field);
    }

    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * @param int one of the \Pyrus\Validate::* constants
     */
    public function validate($state = null)
    {
        if (!isset($this->_packagexml)) {
            return false;
        }

        if ($state !== null) {
            $this->_state = $state;
        }

        $this->failures = new \PEAR2\MultiErrors;
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

    protected function validatePackageName()
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

    protected function validateVersion()
    {
        if ($this->_packagexml->stability['release'] == 'snapshot') {
            // allow any version
            return true;
        }

        $version = $this->_packagexml->version['release'];
        if ($this->_state != Validate::PACKAGING && !$this->validVersion($version)) {
            $this->_addFailure('version', 'Invalid version number "' . $version . '"');
            return false;
        }

        $versioncomponents = explode('.', $version);
        if (count($versioncomponents) !== 3) {
            $this->_addWarning('version', 'A version number should have 3 decimals (x.y.z)');
            return true;
        }

        $name = $this->_packagexml->name;
        // version must be based upon state
        switch ($this->_packagexml->stability['release']) {
            case 'devel':
                if ($versioncomponents[0] . 'a' == '0a') {
                    return true;
                }

                if ($versioncomponents[0] == 0) {
                    $versioncomponents[0] = '0';
                    $this->_addWarning('version', 'version "' . $version . '" should be "' .
                        implode('.' , $versioncomponents) . '"');
                } else {
                    $this->_addWarning('version', 'packages with devel stability must be < version 1.0.0');
                }

                return true;
            case 'alpha':
            case 'beta':
                // check for a package that extends a package,
                // like Foo and Foo2
                if ($this->_state == Validate::PACKAGING && substr($versioncomponents[2], 1, 2) == 'rc') {
                    $this->_addFailure('version', 'Release Candidate versions ' .
                            'must have capital RC, not lower-case rc');
                    return false;
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
            case 'stable':
                if ($versioncomponents[0] == '0') {
                    $this->_addWarning('version', 'versions less than 1.0.0 cannot ' .
                    'be stable');
                    return true;
                }

                if (!is_numeric($versioncomponents[2]) &&
                    preg_match('/\d+(rc|a|alpha|b|beta)\d*/i', $versioncomponents[2])
                ) {
                    $this->_addWarning('version', 'version "' . $version . '" or any ' .
                            'RC/beta/alpha version cannot be stable');
                    return true;
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
            default:
                return false;
        }
    }

    protected function validateMaintainers()
    {
        // maintainers can only be truly validated server-side for most channels
        // but allow this customization for those who wish it
        return true;
    }

    protected function validateDate()
    {
        if ($this->_state == Validate::NORMAL || $this->_state == Validate::PACKAGING) {
            $date = $this->_packagexml->date;
            if (!preg_match('/(\d\d\d\d)\-(\d\d)\-(\d\d)/', $date, $res) ||
                count($res) < 4 ||
                !checkdate($res[2], $res[3], $res[1])
            ) {
                $this->_addFailure('date', 'invalid release date "' . $date . '"');
                return false;
            }


            if ($this->_state == Validate::PACKAGING && $date != date('Y-m-d')) {
                $this->_addWarning('date', 'Release Date "' . $date . '" is not today');
            }
        }

        return true;
    }

    protected function validateTime()
    {
        $time = $this->_packagexml->time;
        if (!$time) {
            // default of no time value set
            return true;
        }

        // packager automatically sets time, so only validate if pear validate is called
        if ($this->_state = Validate::NORMAL) {
            if (!preg_match('/\d\d:\d\d:\d\d/', $time)) {
                $this->_addFailure('time', 'invalid release time "' . $time . '"');
                return false;
            }

            if (strtotime($time) === false) {
                $this->_addFailure('time', 'invalid release time "' . $time . '"');
                return false;
            }
        }

        return true;
    }

    protected function validateStability()
    {
        $ret = true;
        $stability = $this->_packagexml->stability['release'];
        if (!self::validState($stability)) {
            $this->_addFailure('state', 'invalid release stability "' .
                $stability . '", must be one of: ' . implode(', ', self::getValidStates()));
            $ret = false;
        }

        $stability = $this->_packagexml->stability['api'];
        $apistates = self::getValidStates();
        array_shift($apistates); // snapshot is not allowed
        if (!in_array($stability, $apistates)) {
            $this->_addFailure('state', 'invalid API stability "' .
                $stability. '", must be one of: ' . implode(', ', $apistates));
            $ret = false;
        }

        return $ret;
    }

    protected function validateSummary()
    {
        return true;
    }

    protected function validateDescription()
    {
        return true;
    }

    protected function validateLicense()
    {
        return true;
    }

    protected function validateNotes()
    {
        return true;
    }

    /**
     * for package.xml 2.0 only - channels can't use package.xml 1.0
     */
    protected function validateDependencies()
    {
        return true;
    }

    /**
     * for package.xml 2.0 only
     */
    protected function validateMainFilelist()
    {
        return true; // placeholder for now
    }

    /**
     * for package.xml 2.0 only
     */
    protected function validateReleaseFilelist()
    {
        return true; // placeholder for now
    }

    protected function validateChangelog()
    {
        return true;
    }
}