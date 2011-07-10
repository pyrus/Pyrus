<?php
/**
 * Create a phar with PHP_Archive embedded
 */
namespace Pyrus\Developer\Creator\Phar;
class PHPArchive extends \Pyrus\Developer\Creator\Phar
{
    /**
     * @var Phar
     */
    protected $phar;
    protected $path;
    protected $stub;
    protected $startup;
    function __construct($path, $startupfile = false, $fileformat = Phar::PHAR, $compression = Phar::NONE,
                         array $others = null)
    {
        parent::__construct($path, false, $fileformat, $compression, $others);
        $phparchive = @file_get_contents('PHP/Archive.php', true);
        if (!$phparchive) {
            throw new \Pyrus\Developer\Creator\Exception('Could not locate' .
                ' PHP_Archive class for phar creation');
        }
        $phparchive = '?>' . $phparchive . '<?php';
        $template = @file_get_contents(__DIR__ . '/../../../../../../data/pear2.php.net/\Pyrus\Developer/phartemplate.php');
        if (!$template) {
            $template = file_get_contents(__DIR__ . '/../../../../../data/phartemplate.php');
        }
        $this->stub = str_replace('@PHPARCHIVE@', $phparchive, $template);
        if ($startupfile === false) {
            $startupfile = '<?php
$extract = getcwd();
$loc = __DIR__;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__)) as $path => $file) {
    if ($file->getFileName() === \'__index.php\') {
        continue;
    }
    $newpath = str_replace(\'/\', DIRECTORY_SEPARATOR, $extract . str_replace($loc, \'\', $path));
    if (!file_exists(dirname($newpath))) {
        mkdir(dirname($newpath), 0755, true);
    }
    file_put_contents($newpath, file_get_contents($path));
}
echo "Extracted files available in current directory\n";';
        }
        $this->startup = $startupfile;
    }

    /**
     * Initialize the package creator
     */
    function init()
    {
        parent::init();
        $this->phar['__index.php'] = $this->startup;
    }
}
