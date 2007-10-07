<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Greg Beaver <cellog@php.net>                                 |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id: Validator.php,v 1.1 2006/12/28 20:42:32 cellog Exp $
/**
 * Private validation class used by PEAR2_Pyrus_PackageFile_v2 - do not use directly, its
 * sole purpose is to split up the PEAR/PackageFile/v2.php file to make it smaller
 * @author Greg Beaver <cellog@php.net>
 * @access private
 */
class PEAR2_Pyrus_PackageFile_v2_Validator
{
    /**
     * @var array
     */
    var $_packageInfo;
    /**
     * @var PEAR2_Pyrus_PackageFile_v2
     */
    var $_pf;
    /**
     * @var PEAR2_Pyrus_ErrorStack
     */
    var $_stack;
    /**
     * @var int
     */
    var $_isValid = 0;
    /**
     * @var int
     */
    var $_filesValid = 0;
    /**
     * @var int
     */
    var $_curState = 0;
    private $_contents = array();
    private $_errors;

    /**
     * @param PEAR2_Pyrus_PackageFile_v2
     * @param int
     */
    function validate(PEAR2_Pyrus_PackageFile_v2 $pf, $state = PEAR2_Pyrus_Validate::NORMAL)
    {
        $this->_errors = new PEAR2_MultiErrors;
        if (!$pf->schemaOK) {
            // this package.xml was created from scratch, not loaded from an existing
            // package.xml
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadXML($pf);
            $a = $pf->toArray();
            if ($a['package']['attribs']['version'] == '2.1') {
                $schema = realpath(dirname(__FILE__) .
                    '/../../../../../data/pear2.php.net/PEAR2_Pyrus/package-2.1.xsd');
                // for running out of cvs
                if (!$schema) {
                    $schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/package-2.1.xsd';
                }
            } else {
                $schema = realpath(dirname(__FILE__) .
                    '/../../../../../data/pear2.php.net/PEAR2_Pyrus/package-2.0.xsd');
                // for running out of cvs
                if (!$schema) {
                    $schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/package-2.0.xsd';
                }
            }
            $dom->schemaValidate($schema);
            $causes = array();
            foreach (libxml_get_errors() as $error) {
                $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception("Line " .
                     $error->line . ': ' . $error->message);
            }
            if (count($this->_errors)) {
                throw new PEAR2_Pyrus_PackageFile_Exception('Invalid package.xml, does' .
                    ' not validate against schema', $this->_errors);
            }
        }
        $this->_pf = $pf;
        $this->_curState = $state;
        $this->_packageInfo = $this->_pf->toArray();
        $this->_packageInfo = $this->_packageInfo['package'];
        $this->_isValid = $this->_pf->_isValid;
        $this->_filesValid = $this->_pf->_filesValid;
        if (($this->_isValid & $state) == $state) {
            return true;
        }
        if (!isset($this->_packageInfo) || !is_array($this->_packageInfo)) {
            return false;
        }
        $myversion = self::VERSION;
        if ($myversion === '@PACKAGE_VERSION@') {
            // we're running from CVS, assume we're 2.0.0
            $myversion = '2.0.0';
        }
        $test = $this->_packageInfo;
        if (isset($test['dependencies']) &&
              isset($test['dependencies']['required']) &&
              isset($test['dependencies']['required']['pearinstaller']) &&
              isset($test['dependencies']['required']['pearinstaller']['min']) &&
              version_compare($myversion,
                $test['dependencies']['required']['pearinstaller']['min'], '<')) {
            $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                'This package.xml requires PEAR version ' .
                $test['dependencies']['required']['pearinstaller']['min'] .
                ' to parse properly, we are version ' . $myversion);
        $this->_errors = new PEAR2_MultiErrors;
        }
        $fail = false;
        if (!count($this->_contents) && isset($this->_packageInfo['contents'])) {
            $contents = array();
            foreach ($pf->contents as $file) {
                // leverage the hidden iterators to do our validation
                $name = $file->dir . $file->name;
                if ($name[0] == '.' && $name[1] == '/') {
                    // name is something like "./doc/whatever.txt"
                    $this->_errors->E_ERROR[] = new PEAR2_Pyrus_Package_Exception(
                        'File "' . $name . '" cannot begin with "."');
                    continue;
                }
                if (!$this->_validateRole($file->role)) {
                    if (isset($this->_packageInfo['usesrole'])) {
                        $roles = $this->_packageInfo['usesrole'];
                        if (!isset($roles[0])) {
                            $roles = array($roles);
                        }
                        foreach ($roles as $role) {
                            if ($role['role'] = $file->role) {
                                if (isset($role['uri'])) {
                                    $package = $role['uri'];
                                } else {
                                    $package = PEAR2_Pyrus_ChannelRegistry::
                                        parsedPackageNameToString(array('package' =>
                                            $role['package'], 'channel' => $role['channel']),
                                            true);
                                }
                                $msg = 'This package contains role "' . $file->role .
                                    '" and requires package "' . $package
                                     . '" to be used';
                                $this->_errors->E_WARNING[] =
                                    new PEAR2_Pyrus_PackageFile_Exception($msg);
                            }
                        }
                    }
                    $this->_errors->E_ERROR[] = 
                        new PEAR2_Pyrus_PackageFile_Exception(
                        'File "' . $name . '" has invalid role "' .
                        $file->role . '", should be one of ' . implode(', ', 
                        PEAR2_Pyrus_Installer_Role::getValidRoles($this->_pf->getPackageType())));
                }
                if (count($file->tasks) && $this->_curState != PEAR2_Pyrus_Validate::DOWNLOADING) { // has tasks
                    foreach ($file->tasks as $task => $value) {
                        if ($tagClass = $this->_pf->getTask($task)) {
                            if (!is_array($value) || !isset($value[0])) {
                                $value = array($value);
                            }
                            foreach ($value as $v) {
                                $ret = call_user_func(array($tagClass, 'validateXml'),
                                    $this->_pf, $v, $this->_pf->_config, $save);
                                if (is_array($ret)) {
                                    $this->_errors->E_ERROR[] = 
                                        new PEAR2_Pyrus_PackageFile_Exception(
                                            $this->_invalidTask($task, $ret, isset($save['name']) ?
                                        $save['name'] : ''));
                                }
                            }
                        } else {
                            if (isset($this->_packageInfo['usestask'])) {
                                $roles = $this->_packageInfo['usestask'];
                                if (!isset($roles[0])) {
                                    $roles = array($roles);
                                }
                                foreach ($roles as $role) {
                                    if ($role['task'] = $task) {
                                        if (isset($role['uri'])) {
                                            $package = $role['uri'];
                                        } else {
                                            $package = PEAR2_Pyrus_ChannelRegistry::
                                                parsedPackageNameToString(array('package' =>
                                                    $role['package'], 'channel' => $role['channel']),
                                                    true);
                                        }
                                        $msg = 'This package contains task "' . $task .
                                            '" and requires package "' . $package
                                             . '" to be used';
                                        $this->_errors->E_WARNING[] =
                                            new PEAR2_Pyrus_PackageFile_Exception($msg);
                                    }
                                }
                            }
                            $this->_errors->E_ERROR[] =
                                new PEAR2_Pyrus_PackageFile_Exception(
                                'Unknown task "' . $task . '" passed in file <file name="' .
                                $name . '">');
                        }
                    }
                }
                $this->_contents[] = $name;
            }
        }
        $this->_validateRelease();
        if (count($this->_errors->E_ERROR)) {
            throw new PEAR2_Pyrus_PackageFile_Exception('Invalid package.xml', $this->_errors);
        }
        try {
            $validator = PEAR2_Pyrus_Config::current()
                ->registry->channel[$this->_pf->channel]
                ->getValidationObject($this->_pf->name);
            $validator->setPackageFile($this->_pf);
            $validator->validate($state);
            // merge in errors from channel-specific validation
            $this->_errors[] = $validator->getFailures();
        } catch (PEAR2_Pyrus_Config_Exception $e) {
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Unable to process channel-specific configuration for channel ' .
                $this->_pf->getChannel(), $e);
        } catch (Exception $e) {
            $valpack = PEAR2_Pyrus_Config::current()
                ->registry->channel[$this->_pf->channel]->getValidationPackage();
            $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                'Unknown channel ' . $this->_pf->channel);
            $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                'package "' . $chan->getName() . '/' . $this->_pf->name .
                '" cannot be properly validated without validation package "' .
                $chan->getName() . '/' . $valpack['name'] . '-' . $valpack['version'] . '"');
        }
        if (count($this->_errors->E_ERROR)) {
            throw new PEAR2_Pyrus_PackageFile_Exception('Invalid package.xml',
                $this->_errors);
        }
        if ($state == PEAR2_Pyrus_Validate::PACKAGING && !$this->_filesValid) {
            if ($this->_pf->type == 'bundle') {
                if (!$this->_analyzeBundledPackages()) {
                    throw new PEAR2_Pyrus_PackageFile_Exception('Invalid package.xml',
                        $this->_errors);
                }
            } else {
                if (!$this->_analyzePhpFiles()) {
                    throw new PEAR2_Pyrus_PackageFile_Exception('Invalid package.xml',
                        $this->_errors);
                }
            }
        }
        return $state;
    }

    function _validateFilelist($list)
    {
        $ignored_or_installed = array();
        $filelist = $this->_contents;
        if (isset($list['install'])) {
            if (!isset($list['install'][0])) {
                $list['install'] = array($list['install']);
            }
            foreach ($list['install'] as $file) {
                if (array_key_exists($file['attribs']['name'], $ignored_or_installed)) {
                    $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                        'Only one <install> tag is allowed for file "' .
                        $file['attribs']['name'] . '"');
                }
                if (!isset($ignored_or_installed[$file['attribs']['name']])) {
                    $ignored_or_installed[$file['attribs']['name']] = array();
                }
                $ignored_or_installed[$file['attribs']['name']][] = 1;
            }
        }
        if (isset($list['ignore'])) {
            if (!isset($list['ignore'][0])) {
                $list['ignore'] = array($list['ignore']);
            }
            foreach ($list['ignore'] as $file) {
                if (array_key_exists($file['attribs']['name'], $ignored_or_installed)) {
                    $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                        'Cannot have both <ignore> and <install> tags for file "' . 
                        $file['attribs']['name'] . '"');
                }
            }
        }
    }

    function _validateRelease()
    {
        if (isset($this->_packageInfo['phprelease'])) {
            $release = 'phprelease';
            $releases = $this->_packageInfo['phprelease'];
            if (!is_array($releases)) {
                return true;
            }
            if (!isset($releases[0])) {
                $releases = array($releases);
            }
        }
        foreach (array('', 'zend') as $prefix) {
            $releasetype = $prefix . 'extsrcrelease';
            if (isset($this->_packageInfo[$releasetype])) {
                $release = $releasetype;
                $releases = $this->_packageInfo[$releasetype];
                if (!is_array($releases)) {
                    return true;
                }
                if (!isset($releases[0])) {
                    $releases = array($releases);
                }
            }
            $releasetype = 'extbinrelease';
            if (isset($this->_packageInfo[$releasetype])) {
                $release = $releasetype;
                $releases = $this->_packageInfo[$releasetype];
                if (!is_array($releases)) {
                    return true;
                }
                if (!isset($releases[0])) {
                    $releases = array($releases);
                }
            }
        }
        if (isset($this->_packageInfo['bundle'])) {
            $release = 'bundle';
            $releases = $this->_packageInfo['bundle'];
            if (!is_array($releases) || !isset($releases[0])) {
                $releases = array($releases);
            }
        }
        foreach ($releases as $rel) {
            if (is_array($rel) && array_key_exists('filelist', $rel)) {
                if ($rel['filelist']) {
                    
                    $this->_validateFilelist($rel['filelist']);
                }
            }
        }
    }

    /**
     * This is here to allow role extension through plugins
     * @param string
     */
    function _validateRole($role)
    {
        return in_array($role, PEAR2_Pyrus_Installer_Role::getValidRoles($this->_pf->getPackageType()));
    }

    function _invalidTask($task, $ret, $file)
    {
        switch ($ret[0]) {
            case PEAR2_PYRUS_TASK_ERROR_MISSING_ATTRIB :
                return 'task <' . $task . '> is missing attribute "' . $ret[1] .
                    '" in file ' . $file;
            case PEAR2_PYRUS_TASK_ERROR_NOATTRIBS :
                return 'task <' . $task . '> has no attributes in file ' . $file;
            case PEAR2_PYRUS_TASK_ERROR_WRONG_ATTRIB_VALUE :
                return 'task <' . $task . '> attribute "' . $ret[1] .
                    '" has the wrong value "' . $ret[2] . '" '.
                    'in file ' . $file . ', expecting one of "' . implode (', ', $ret[3]) . '"';
            case PEAR2_PYRUS_TASK_ERROR_INVALID :
                return 'task <' . $task . '> in file ' . $file .
                    ' is invalid because of "' . $ret[1] . '"';
        }
    }

    function _analyzeBundledPackages()
    {
        if (!$this->_pf->type == 'bundle') {
            return false;
        }
        if (!$this->_pf->packagefile) {
            return false;
        }
        $dir_prefix = $this->_pf->filepath;
        foreach ($this->_pf->bundledpackage as $package) {
            if (!file_exists($dir_prefix . DIRECTORY_SEPARATOR . $package)) {
                $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                    'File "' . $dir_prefix . DIRECTORY_SEPARATOR . $package .
                    '" in package.xml does not exist');
                continue;
            }
            PEAR2_Pyrus_Log::log(1, "Analyzing bundled package $package");
            try {
                $ret = new PEAR2_Pyrus_Package_Tar($dir_prefix . DIRECTORY_SEPARATOR .
                    $package);
            } catch (Exception $e) {
                $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                    'File "' . $dir_prefix . DIRECTORY_SEPARATOR . $package .
                    '" in package.xml is not valid', $e);
                continue;
            }
        }
        return true;
    }

    function _analyzePhpFiles()
    {
        if (!$this->_pf->packagefile) {
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Cannot validate files, no path to package file is set (use setPackageFile())');
        }
        $dir_prefix = $this->_pf->filepath;
        $info = $this->_pf->getContents();
        $info = $info['dir']['file'];
        if (isset($info['attribs'])) {
            $info = array($info);
        }
        $provides = array();
        foreach ($this->_pf->contents as $fa) {
            $file = $fa->name;
            if (!file_exists($dir_prefix . DIRECTORY_SEPARATOR . $file)) {
                $this->_errors->E_ERROR[] = new PEAR2_Pyrus_PackageFile_Exception(
                    'File "' . $dir_prefix . DIRECTORY_SEPARATOR . $file .
                    '" in package.xml does not exist');
                continue;
            }
            if (in_array($fa['role'], PEAR2_Pyrus_Installer_Role::getPhpRoles()) && $dir_prefix) {
                PEAR2_Pyrus_Log::log(1, "Analyzing $file");
                $srcinfo = $this->analyzeSourceCode($dir_prefix . DIRECTORY_SEPARATOR .
                    $file);
                if ($srcinfo) {
                    $provides = array_merge($provides, $this->_buildProvidesArray($srcinfo));
                }
            }
        }
        $this->_packageName = $pn = $this->_pf->package;
        $pnl = strlen($pn);
        foreach ($provides as $key => $what) {
            if (isset($what['explicit']) || !$what) {
                // skip conformance checks if the provides entry is
                // specified in the package.xml file
                continue;
            }
            extract($what);
            if ($type == 'class') {
                if (!strncasecmp($name, $pn, $pnl)) {
                    continue;
                }
                $this->_errors->E_WARNING[] = new PEAR2_Pyrus_PackageFile_Exception(
                    'in ' . $file . ': ' . $type . ' "' . $name . '" not prefixed ' .
                    'with package name "' . $package . '"'
                );
            } elseif ($type == 'function') {
                if (strstr($name, '::') || !strncasecmp($name, $pn, $pnl)) {
                    continue;
                }
                $this->_errors->E_WARNING[] = new PEAR2_Pyrus_PackageFile_Exception(
                    'in ' . $file . ': ' . $type . ' "' . $name . '" not prefixed ' .
                    'with package name "' . $package . '"'
                );
            }
        }
        return count($this->_errors->E_ERROR);
    }

    /**
     * Analyze the source code of the given PHP file
     *
     * @param  string Filename of the PHP file
     * @param  boolean whether to analyze $file as the file contents
     * @return mixed
     */
    function analyzeSourceCode($file, $string = false)
    {
        if (!function_exists("token_get_all")) {
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Parser error: token_get_all() function must exist to analyze source code');
        }
        if ($string) {
            $contents = $file;
        } else {
            if (!$fp = @fopen($file, "r")) {
                return false;
            }
            fclose($fp);
            $contents = file_get_contents($file);
        }
        $tokens = token_get_all($contents);
/*
        for ($i = 0; $i < sizeof($tokens); $i++) {
            @list($token, $data) = $tokens[$i];
            if (is_string($token)) {
                var_dump($token);
            } else {
                print token_name($token) . ' ';
                var_dump(rtrim($data));
            }
        }
*/
        $look_for = 0;
        $paren_level = 0;
        $bracket_level = 0;
        $brace_level = 0;
        $lastphpdoc = '';
        $current_class = '';
        $current_interface = '';
        $current_class_level = -1;
        $current_function = '';
        $current_function_level = -1;
        $declared_classes = array();
        $declared_interfaces = array();
        $declared_functions = array();
        $declared_methods = array();
        $used_classes = array();
        $used_functions = array();
        $extends = array();
        $implements = array();
        $nodeps = array();
        $inquote = false;
        $interface = false;
        for ($i = 0; $i < sizeof($tokens); $i++) {
            if (is_array($tokens[$i])) {
                list($token, $data) = $tokens[$i];
            } else {
                $token = $tokens[$i];
                $data = '';
            }
            if ($inquote) {
                if ($token != '"' && $token != T_END_HEREDOC) {
                    continue;
                } else {
                    $inquote = false;
                    continue;
                }
            }
            switch ($token) {
                case T_WHITESPACE :
                    continue;
                case ';':
                    if ($interface) {
                        $current_function = '';
                        $current_function_level = -1;
                    }
                    break;
                case '"':
                case T_START_HEREDOC:
                    $inquote = true;
                    break;
                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                case '{': $brace_level++; continue 2;
                case '}':
                    $brace_level--;
                    if ($current_class_level == $brace_level) {
                        $current_class = '';
                        $current_class_level = -1;
                    }
                    if ($current_function_level == $brace_level) {
                        $current_function = '';
                        $current_function_level = -1;
                    }
                    continue 2;
                case '[': $bracket_level++; continue 2;
                case ']': $bracket_level--; continue 2;
                case '(': $paren_level++;   continue 2;
                case ')': $paren_level--;   continue 2;
                case T_INTERFACE:
                    $interface = true;
                case T_CLASS:
                    if (($current_class_level != -1) || ($current_function_level != -1)) {
                        $this->_stack->push(__FUNCTION__, 'error', array('file' => $file),
                        'Parser error: invalid PHP found in file "%file%"');
                        return false;
                    }
                case T_FUNCTION:
                case T_NEW:
                case T_EXTENDS:
                case T_IMPLEMENTS:
                    $look_for = $token;
                    continue 2;
                case T_STRING:
                    if ($look_for == T_CLASS) {
                        $current_class = $data;
                        $current_class_level = $brace_level;
                        $declared_classes[] = $current_class;
                    } elseif ($look_for == T_INTERFACE) {
                        $current_interface = $data;
                        $current_class_level = $brace_level;
                        $declared_interfaces[] = $current_interface;
                    } elseif ($look_for == T_IMPLEMENTS) {
                        $implements[$current_class] = $data;
                    } elseif ($look_for == T_EXTENDS) {
                        $extends[$current_class] = $data;
                    } elseif ($look_for == T_FUNCTION) {
                        if ($current_class) {
                            $current_function = "$current_class::$data";
                            $declared_methods[$current_class][] = $data;
                        } elseif ($current_interface) {
                            $current_function = "$current_interface::$data";
                            $declared_methods[$current_interface][] = $data;
                        } else {
                            $current_function = $data;
                            $declared_functions[] = $current_function;
                        }
                        $current_function_level = $brace_level;
                        $m = array();
                    } elseif ($look_for == T_NEW) {
                        $used_classes[$data] = true;
                    }
                    $look_for = 0;
                    continue 2;
                case T_VARIABLE:
                    $look_for = 0;
                    continue 2;
                case T_DOC_COMMENT:
                case T_COMMENT:
                    if (preg_match('!^/\*\*\s!', $data)) {
                        $lastphpdoc = $data;
                        if (preg_match_all('/@nodep\s+(\S+)/', $lastphpdoc, $m)) {
                            $nodeps = array_merge($nodeps, $m[1]);
                        }
                    }
                    continue 2;
                case T_DOUBLE_COLON:
                    if (!($tokens[$i - 1][0] == T_WHITESPACE || $tokens[$i - 1][0] == T_STRING)) {
                        $this->_errors->E_WARNING[] =
                            new PEAR2_Pyrus_PackageFile_Exception(
                            'Parser error: invalid PHP found in file "' . $file . '"');
                        return false;
                    }
                    $class = $tokens[$i - 1][1];
                    if (strtolower($class) != 'parent') {
                        $used_classes[$class] = true;
                    }
                    continue 2;
            }
        }
        return array(
            "source_file" => $file,
            "declared_classes" => $declared_classes,
            "declared_interfaces" => $declared_interfaces,
            "declared_methods" => $declared_methods,
            "declared_functions" => $declared_functions,
            "used_classes" => array_diff(array_keys($used_classes), $nodeps),
            "inheritance" => $extends,
            "implements" => $implements,
            );
    }

    /**
     * Build a "provides" array from data returned by
     * analyzeSourceCode().  The format of the built array is like
     * this:
     *
     *  array(
     *    'class;MyClass' => 'array('type' => 'class', 'name' => 'MyClass'),
     *    ...
     *  )
     *
     *
     * @param array $srcinfo array with information about a source file
     * as returned by the analyzeSourceCode() method.
     *
     * @return void
     *
     * @access private
     *
     */
    function _buildProvidesArray($srcinfo)
    {
        if (!$this->_isValid) {
            return array();
        }
        $providesret = array();
        $file = basename($srcinfo['source_file']);
        $pn = $this->_pf->package;
        $pnl = strlen($pn);
        foreach ($srcinfo['declared_classes'] as $class) {
            $key = "class;$class";
            if (isset($providesret[$key])) {
                continue;
            }
            $providesret[$key] =
                array('file'=> $file, 'type' => 'class', 'name' => $class);
            if (isset($srcinfo['inheritance'][$class])) {
                $providesret[$key]['extends'] =
                    $srcinfo['inheritance'][$class];
            }
        }
        foreach ($srcinfo['declared_methods'] as $class => $methods) {
            foreach ($methods as $method) {
                $function = "$class::$method";
                $key = "function;$function";
                if ($method{0} == '_' || !strcasecmp($method, $class) ||
                    isset($providesret[$key])) {
                    continue;
                }
                $providesret[$key] =
                    array('file'=> $file, 'type' => 'function', 'name' => $function);
            }
        }

        foreach ($srcinfo['declared_functions'] as $function) {
            $key = "function;$function";
            if ($function{0} == '_' || isset($providesret[$key])) {
                continue;
            }
            if (!strstr($function, '::') && strncasecmp($function, $pn, $pnl)) {
                $warnings[] = "in1 " . $file . ": function \"$function\" not prefixed with package name \"$pn\"";
            }
            $providesret[$key] =
                array('file'=> $file, 'type' => 'function', 'name' => $function);
        }
        return $providesret;
    }
}
?>