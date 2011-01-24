<?php
/**
 * Create a brand new package.xml from the subversion layout of a PEAR2 package
 *
 * This class assumes:
 *
 *  1. the source layout is:
 *     <pre>
 *     PackageName/src           [role="php"]
 *                /examples      [role="doc"]
 *                /doc           [role="doc"]
 *                /data          [role="data"]
 *                /customrole    [role="customrole"]
 *                /customtask    [role="customtask"]
 *                /customcommand [role="customcommand"]
 *                /tests         [role="test"]
 *                /www           [role="www"]
 *                /scripts       [role="script"]
 *     </pre>
 *  2. if file PackageName/README exists, it contains the summary as the first line,
 *     and the description as the rest of the file
 *  3. if file PackageName/CREDITS exists, it contains the maintainers in this format:
 *     ; comment ignored
 *     Name [handle] <email> (role)
 *     Name2 [handle2] <email> (role/inactive)
 */
namespace PEAR2\Pyrus\Developer\PackageFile;
class PEAR2SVN
{
    protected $path;
    protected $package;
    protected $pxml;
    /**
     * package.xml that will be added as the compatible package.xml for PEAR 1.x
     */
    protected $pxml_compatible;
    protected $doCompatible = false;
    /**
     * Create or update a package.xml from CVS
     *
     * @param string $path full path to the SVN checkout
     * @param string $packagename Package name (PEAR2_Pyrus, for example)
     * @param string $channel Channel (pear2.php.net)
     * @param bool $return if true, creation is not attempted in the constructor,
     *                     otherwise, the constructor writes package.xml to disk
     *                     if possible
     * @param bool $fullpathsused if true, for package PEAR2_Package_Name it is
     *                            assumed that src/PEAR2/Package/ exists in SVN,
     *                            otherwise, we assume src/ is used and baseinstalldir
     *                            should be PEAR2/Package for "/" directory
     */
    function __construct($path, $packagename = '##set me##', $channel = 'pear2.php.net',
                         $return = false, $fullpathsused = true, $doCompatible = true)
    {
        $this->doCompatible = $doCompatible;
        if (file_exists($path . DIRECTORY_SEPARATOR . 'package.xml')) {
            try {
                $this->pxml = new \PEAR2\Pyrus\PackageFile(
                    $path . DIRECTORY_SEPARATOR . 'package.xml',
                    'PEAR2\Pyrus\Developer\PackageFile\v2');
                $this->pxml = $this->pxml->info;
                $this->pxml->setFilelist(array());
            } catch (Exception $e) {
                $this->pxml = new \PEAR2\Pyrus\Developer\PackageFile\v2;
                $this->pxml->name = $packagename;
                $this->pxml->channel = $channel;
            }
        } else {
            $this->pxml = new \PEAR2\Pyrus\Developer\PackageFile\v2;
            $this->pxml->name = $packagename;
            $this->pxml->channel = $channel;
        }
        if ($doCompatible) {
            $this->pxml_compatible = new \PEAR2\Pyrus\Developer\PackageFile\v2;
            $this->pxml_compatible->name = $packagename;
            $this->pxml_compatible->channel = $channel;
        }
        $this->path = $path;
        
        $this->parseREADME();
        $this->parseCREDITS();
        $this->parseRELEASE();
        $this->parseAPI();
        
        $packagepath = explode('_', $packagename);

        if ($fullpathsused) {
            if ($this->pxml->channel == 'pear2.php.net') {
                $packagepath = array('PEAR2');
            } else {
                $packagepath = array('/');
            }
        } else {
            array_pop($packagepath);
        }

        $this->scanFiles($packagepath);

        $this->validate();
        try {
            if (!$return) {
                $this->save();
            }
        } catch (Exception $e) {
            // ignore - we'll let the user do this business
            echo 'WARNING: validation failed in constructor, you must fix the package.xml ' .
                'manually:', $e;
        }
    }
    
    /**
     * Scan the directories top populate the package file contents.
     *
     * @param string $packagepath
     */
    function scanFiles($packagepath)
    {
        $this->pxml->setBaseInstallDirs(array(
            'src'     => implode('/', $packagepath),
            'customrole'    => '/',
            'customtask'    => '/',
            'customcommand' => '/',
            'data'    => '/',
            'doc'     => '/',
            'tests'   => '/',
            'scripts' => '/',
            'www'     => '/',
        ));
        $this->pxml_compatible->setBaseInstallDirs(array(
            'src'     => implode('/', $packagepath),
            'customrole'    => '/',
            'customtask'    => '/',
            'customcommand' => '/',
            'data'    => '/',
            'doc'     => '/',
            'tests'   => '/',
            'scripts' => '/',
            'www'     => '/',
        ));

        $rolemap = array(
            'src'           => 'php',
            'data'          => 'data',
            'customrole'    => 'customrole',
            'customtask'    => 'customtask',
            'customcommand' => 'customcommand',
            'doc'           => 'doc',
            'tests'         => 'test',
            'examples'      => 'doc',
            'scripts'       => 'script',
            'www'           => 'www',);

        foreach ($rolemap as $dir => $role) {
            if (file_exists($this->path . DIRECTORY_SEPARATOR . $dir)) {
                $basepath = ($dir === 'examples') ? 'examples' : '';
                foreach (new \PEAR2\Pyrus\Developer\PackageFile\PEAR2SVN\Filter(
                            $this->path . DIRECTORY_SEPARATOR . $dir,
                         new \RecursiveIteratorIterator(
                         new \RecursiveDirectoryIterator($this->path . DIRECTORY_SEPARATOR . $dir),
                         \RecursiveIteratorIterator::LEAVES_ONLY), $role) as $file) {
                    $curpath = str_replace($this->path . DIRECTORY_SEPARATOR . $dir, '',
                        $file->getPathName());
                    if ($curpath && $curpath[0] === DIRECTORY_SEPARATOR) {
                        $curpath = substr($curpath, 1);
                    }
                    $curpath = $dir . '/' . $curpath;
                    $curpath = str_replace('\\', '/', $curpath);
                    $curpath = str_replace('//', '/', $curpath);
                    $this->pxml->files[$curpath] =
                        array(
                            'attribs' => array('role' => $role)
                        );

                    $roleobject = \PEAR2\Pyrus\Installer\Role::factory($this->pxml->type, $role);
                    if ($role == 'customcommand' || $role == 'customrole' || $role == 'customtask') {
                        $compatiblerole = 'data';
                    } else {
                        $compatiblerole = $role;
                    }

                    $attribs = array('name' => $curpath, 'role' => $compatiblerole);
                    $baseinstalldir = $this->pxml_compatible->getBaseinstallDir($curpath);
                    if ($baseinstalldir && $baseinstalldir != '/') {
                        $attribs['baseinstalldir'] = $baseinstalldir;
                    }

                    $curpath = $roleobject->getPackagingLocation($this->pxml_compatible,
                                                                 $attribs);
                    $packagepath = $roleobject->getCompatibleInstallAs($this->pxml_compatible,
                                                                       $attribs);
                    $this->pxml_compatible->files[$curpath] =
                        array(
                            'attribs' => array('role' => $compatiblerole)
                        );
                    $this->pxml_compatible->release[0]->installAs($curpath, $packagepath);
                }
            }
        }
        $this->pxml_compatible->dependencies['required']->pearinstaller->min = '1.4.8';
    }
    
    /**
     * Parse the README file to populate the package summary and description.
     *
     */
    function parseREADME()
    {
        $description = '';
        if (file_exists($this->path . DIRECTORY_SEPARATOR . 'README')) {
            $a = new \SplFileInfo($this->path . DIRECTORY_SEPARATOR . 'README');
            foreach ($a->openFile('r') as $num => $line) {
                if (!$num) {
                    $this->pxml->summary = $line;
                    if ($this->doCompatible) {
                        $this->pxml_compatible->summary = $line;
                    }
                    continue;
                }
                $description .= $line;
            }
            $this->pxml->description = $description;
            if ($this->doCompatible) {
                $this->pxml_compatible->description = $description;
            }
        }
    }
    
    /**
     * Parse the CREDITS file to populate the package developers, roles and email.
     *
     */
    function parseCREDITS()
    {
        if (file_exists($this->path . DIRECTORY_SEPARATOR . 'CREDITS')) {
            $a = new \SplFileInfo($this->path . DIRECTORY_SEPARATOR . 'CREDITS');
            foreach ($a->openFile('r') as $line) {
                if ($line && $line[0] === ';') {
                    continue;
                }
                if (preg_match('/^(.+) \[(.+)\] \<(.+)\> \((.+?)(\/inactive)?\)/', $line, $match)) {
                    $this->pxml->maintainer[$match[2]]
                        ->role($match[4])
                        ->name($match[1])
                        ->email($match[3])
                        ->active(isset($match[5]) ? 'no' : 'yes');
                    if ($this->doCompatible) {
                        $this->pxml_compatible->maintainer[$match[2]]
                            ->role($match[4])
                            ->name($match[1])
                            ->email($match[3])
                            ->active(isset($match[5]) ? 'no' : 'yes');
                    }
                }
            }
        }
    }

    /**
     * Parse the RELEASE file to populate the release notes.
     *
     */
    function parseRELEASE()
    {
        $files = array();
        foreach (new \RegexIterator(
                                   new \DirectoryIterator($this->path),
                                   '/^RELEASE\-(.+)$/', \RegexIterator::GET_MATCH) as $file) {
            $files[$file[1]] = $file;
        }
        if (count($files)) {
            uksort($files, 'version_compare');
            list($releasenotesfile, $releaseversion) = array_pop($files);
            $stability = $this->guessStabilityFromVersion($releaseversion);

            $this->pxml->version['release']   = $releaseversion;
            $this->pxml->stability['release'] = $stability;
            $this->pxml->notes                = file_get_contents($this->path . DIRECTORY_SEPARATOR . $releasenotesfile);

            $apistability = $stability;
            $apiversion   = $this->pxml->version['api'];

            if ($stability == 'beta') {
                if ($apiversion == '0.1.0') {
                    $apiversion = '1.0.0';
                }
                $apistability = 'stable';
            }

            $this->pxml->version['api']   = $apiversion;
            $this->pxml->stability['api'] = $apistability;

            if ($this->doCompatible) {
                $this->pxml_compatible->version['release']   = $releaseversion;
                $this->pxml_compatible->stability['release'] = $stability;
                $this->pxml_compatible->version['api']       = $apiversion;
                $this->pxml_compatible->stability['api']     = $apistability;
                $this->pxml_compatible->notes                = $this->pxml->notes;
            }
        }
    }

    /**
     * Parse the API file to populate the API version (if present).
     *
     */
    function parseAPI()
    {
        $files = array();
        foreach (new \RegexIterator(
                                   new \DirectoryIterator($this->path),
                                   '/^API\-(.+)$/', \RegexIterator::GET_MATCH) as $file) {
            $files[$file[1]] = $file;
        }
        if (count($files)) {
            uksort($files, 'version_compare');
            list($apinotesfile, $apiversion) = array_pop($files);
            $stability = $this->guessStabilityFromVersion($apiversion);

            $this->pxml->version['api']   = $apiversion;
            $this->pxml->stability['api'] = $stability;

            $this->pxml->notes = $this->pxml->notes .
                "\n\n" . file_get_contents($this->path . DIRECTORY_SEPARATOR . $apinotesfile);

            if ($this->doCompatible) {
                $this->pxml_compatible->version['api']   = $apiversion;
                $this->pxml_compatible->stability['api'] = $stability;
                $this->pxml_compatible->notes            = $this->pxml->notes;
            }
        }
    }

    function guessStabilityFromVersion($version)
    {
        if (false !== strpos($version, 'beta')) {
            return 'beta';
        }
        if (false !== strpos($version, 'RC')) {
            return 'beta';
        }
        if (false !== strpos($version, 'b')) {
            return 'beta';
        }
        if (false !== strpos($version, 'alpha')) {
            return 'alpha';
        }
        if (false !== strpos($version, 'a')) {
            return 'alpha';
        }
        if (false !== strpos($version, 'devel')) {
            return 'devel';
        }
        if (false !== strpos($version, 'dev')) {
            return 'devel';
        }
        if (false !== strpos($version, 'devel')) {
            return 'devel';
        }
        $components = explode('.', $version);
        if ('0' === $components[0]) {
            return 'alpha';
        }
        return 'stable';
    }

    function validate()
    {
        $package = new \PEAR2\Pyrus\Package(false);
        $xmlcontainer = new \PEAR2\Pyrus\PackageFile($this->pxml);
        $xml = new \PEAR2\Pyrus\Package\Xml($this->path . '/package.xml', $package, $xmlcontainer);
        $package->setInternalPackage($xml);

        $this->pxml->getValidator()->validate($package);
    }

    function __toString()
    {
        return (string) $this->pxml;
    }

    function save()
    {
        file_put_contents($this->path . '/package.xml', $this->pxml);
        if ($this->doCompatible) {
            $this->pxml_compatible->date = date('Y-m-d');
            $this->pxml_compatible->time = date('H:i:s');
            $info = $this->pxml_compatible->toArray(true);
            $stuff = new \PEAR2\Pyrus\XMLWriter($info);
            file_put_contents($this->path . '/package_compatible.xml', $stuff);
        }
    }

    function __set($var, $value)
    {
        $this->pxml->$var = $value;
        if ($this->doCompatible) {
            $this->pxml_compatible->$var = $value;
        }
    }

    function __get($var)
    {
        if ($this->doCompatible) {
            if ($var == 'compatiblepackagefile') {
                return $this->pxml_compatible;
            }
        } else {
            if ($var == 'compatiblepackagefile') {
                return null;
            }
        }
        if ($var == 'packagefile') {
            return $this->pxml;
        }
        if ($var == 'path') {
            return $this->path;
        }
        return $this->pxml->$var;
    }
}