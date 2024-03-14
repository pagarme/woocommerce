<?php

namespace Pagarme\Core\Maintenance\Services;

use Pagarme\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;
use ZipArchive;

class LogDownloadInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $logInfoRetrievierService = new LogInfoRetrieverService();
        $logInfo = $logInfoRetrievierService->retrieveInfo('');
        $validLogFiles = $logInfo->files;

        $params = explode(':', $value ?? '');

        try {
            $extension = $params[0];
            $file = base64_decode($params[1]);
        } catch (\Throwable $e) {
            return null;
        }

        if (!in_array($file, $validLogFiles)) {
            return null;
        }

        return $this->handleDownload($extension, $file);
    }


    private function handleDownload($extension, $file)
    {
        $downloadFileName = str_replace(DIRECTORY_SEPARATOR, "_", $file ?? '');

        if ($extension != "zip") {
            return $this->downloadLog($downloadFileName, $file);
        }

        $zip = new ZipArchive;
        $zipFileName = tempnam(sys_get_temp_dir(), 'MP_');
        $zipSuccess = false;
        if ($zip->open($zipFileName) === true) {
            $zip->addFile($file, $downloadFileName);
            $zip->close();
            $zipSuccess = true;
        }

        if ($zipSuccess) {
            return $this->downloadLog($downloadFileName.'.zip', $zipFileName);
        }

        return null;
    }

    private function downloadLog($filename, $file)
    {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        readfile($file);
        die(0);
    }

}