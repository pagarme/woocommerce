<?php

namespace Pagarme\Core\Maintenance\Services\InstallDataSource;

use Pagarme\Core\Maintenance\Interfaces\InstallDataSourceInterface;

abstract class AbstractInstallDataSource implements InstallDataSourceInterface
{

    //@todo $ignoreVendor should be an array of directories to ignore instead of boolean
    protected function scanDirs($dirs, $ignoreVendor = false)
    {
        $files = [];
        foreach($dirs as $dir) {
            if (is_file($dir)) {
                $files[$dir] = $dir;
                continue;
            }
            $foundFiles = scandir($dir);
            if ($foundFiles !== false) {
                foreach ($foundFiles as $foundFile) {
                    if (
                        $this->shouldIgnoreFile(
                            $foundFile,
                            $dir,
                            $ignoreVendor
                        )
                    ) {
                        continue;
                    }

                    $foundFile = $dir . DIRECTORY_SEPARATOR . $foundFile;
                    $foundFile = preg_replace(
                        '/\\' .DIRECTORY_SEPARATOR. '{2,}/',
                        DIRECTORY_SEPARATOR,
                        $foundFile ?? ''
                    );

                    if (is_dir($foundFile)) {
                        $files = array_merge(
                            $files,
                            $this->scanDirs([$foundFile])
                        );
                        continue;
                    }

                    $files[$foundFile] = $foundFile;
                }
            }
        }
        return array_values($files);
    }

    protected function shouldIgnoreFile($file, $path, $ignoreVendor)
    {
        if (strlen($file) < 3) {
            return true;
        }
        $dir = str_replace($this->getModuleRoot(),'', $path ?? '');
        return
            $ignoreVendor
            && (
                strpos($dir, 'vendor') !== false
                || strpos($dir, 'lib') !== false
            );
    }

    abstract protected function getInstallDirs();
    abstract protected function getModuleRoot();
}