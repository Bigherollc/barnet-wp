<?php

class BarnetFileManager
{
    public function unzip($file, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($file) === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            echo 'ok';
        } else {
            echo 'failed';
        }
    }
}