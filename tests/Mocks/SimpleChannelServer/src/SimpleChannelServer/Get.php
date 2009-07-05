<?php

class PEAR2_SimpleChannelServer_Get
{
    protected $get;

    protected $pyruspath;

    function __construct($savepath, $pyruspath)
    {
        $this->get = $savepath;
        $this->pyruspath = $pyruspath;
        if (!file_exists($savepath)) {
            if (!@mkdir($savepath, 0777, true)) {
                throw new PEAR2_SimpleChannelServer_Exception('Could not initialize' .
                    'GET storage directory "' . $savepath . '"');
            }
        }
    }

    function saveRelease(\pear2\Pyrus\Package $new)
    {
        $outfile = $this->get.'/'.$new->name.'-'.$new->version['release'];
        if (!$new->isNewPackage()) {
            // this is a PEAR 1.x package
            $internal = $new->getInternalPackage();
            if ($internal instanceof \pear2\Pyrus\Package\Tar || $internal instanceof \pear2\Pyrus\Package\Phar) {
                $path = $internal->getTarballPath();
                $fp = fopen($path, 'rb');
                if (fread($fp, 3) == "\x1f\x8b\x08") {
                    // tgz
                    fclose($fp);
                    copy($path, $outfile . '.tgz');
                    $gp = gzopen($path, 'rb');
                    $fp = fopen($outfile . 'tar', 'wb');
                    stream_copy_to_stream($gp, $fp);
                    fclose($gp);
                    fclose($fp);
                } else {
                    // tar
                    copy($path, $outfile . '.tar');
                    $gp = gzopen($outfile . 'tgz', 'wb');
                    rewind($fp);
                    stream_copy_to_stream($fp, $gp);
                    fclose($gp);
                    fclose($fp);
                }
                return;
            }
        }
        $a = new \pear2\Pyrus\Package\Creator(array(
                    new \pear2\Pyrus\Developer\Creator\Phar($outfile.'.tar', false, Phar::TAR, Phar::NONE,
                                                           array(
                                                                 array('tgz', Phar::TAR, Phar::GZ),
                                                                 array('zip', Phar::ZIP, Phar::NONE),
                                                                ))),
                    $this->pyruspath,
                    $this->pyruspath,
                    $this->pyruspath);
        return $a->render($new);
    }

    function deleteRelease(\pear2\Pyrus\Package $release)
    {

    }
}
