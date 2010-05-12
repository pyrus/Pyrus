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
 *
 * All maintainers are assumed to be lead maintainers in CREDITS.
 */
namespace PEAR2\Pyrus\Developer\PackageFile;
class PEAR2SVN
{
    protected $path;
    protected $package;
    protected $pxml;
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
                         $return = false, $fullpathsused = true)
    {
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
        $this->path = $path;
        
        $this->parseREADME();
        $this->parseCREDITS();
        $this->parseRELEASE();
        
        $packagepath = explode('_', $packagename);

        if ($fullpathsused) {
            $packagepath = array('PEAR2');
        } else {
            array_pop($packagepath);
        }

        $this->scanFiles($packagepath);
        
        try {
            if (!$return) {
                file_put_contents($path . DIRECTORY_SEPARATOR . 'package.xml', $this->pxml);
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
            'data'    => '/',
            'doc'     => '/',
            'tests'   => '/',
            'scripts' => '/',
            'www'     => '/',
        ));
        
        $rolemap = array(
            'src'           => 'php',
            'data'          => 'data',
            'customrole'    => 'data',
            'customtask'    => 'data',
            'customcommand' => 'data',
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
                }
            }
        }
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
                    continue;
                }
                $description .= $line;
            }
            $this->pxml->description = $description;
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
        if (file_exists($this->path . DIRECTORY_SEPARATOR . 'RELEASE-' . $this->pxml->version['release'])) {
            $this->pxml->notes = file_get_contents(
                $this->path . DIRECTORY_SEPARATOR . 'RELEASE-' . $this->pxml->version['release']);
        }
    }

    function __toString()
    {
        echo $this->pxml;
    }

    function __set($var, $value)
    {
        $this->pxml->$var = $value;
    }

    function __get($var)
    {
        return $this->pxml->$var;
    }
}