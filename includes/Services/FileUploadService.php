<?php



namespace HelloPrint\Inc\Services;

use DateTime;

class FileUploadService
{
    public function storeFile($files, $plugin_path, $path = '')
    {
        $uploaded_files = [];
        $path ? $plugin_upload_path = $path : $plugin_upload_path = 'uploads/print-tmp/';
        $basePath = $plugin_path . '../../' . $plugin_upload_path;
        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }
        if ($path == '') {
            $this->noIndexOnSearchEngine($basePath);
        }
        for ($i = 0; $i < count($files['name']); $i++) {
            $file_name = date("YmdHis") . '-' . basename(str_replace(' ', '-', sanitize_text_field($files['name'][$i])));
            $target = $basePath . $file_name;
            if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                $fp = fopen($target, "r");
            }
            array_push($uploaded_files, [
                'file_name' => sanitize_text_field($files['name'][$i]),
                'file_path' => '/wp-content' . '/' . $plugin_upload_path . $file_name
            ]);
        }
        return $uploaded_files;
    }

    public function noIndexOnSearchEngine($path)
    {
        try {
            $logger = wc_get_logger();
            $context = array('source' => 'helloprint');
            $path = $path . '.htaccess';
            if (file_exists($path)) {
                $logger->notice('htacces file already exists', $context);
                return;
            }

            $htaccessContent = 'Options -Indexes';
            if (!($htaccessFile = fopen($path, 'w'))) {
                $logger->error('htacces file cannot be created', $context);
                return;
            }
            $logger->info('htacces file opened for writing', $context);

            fwrite($htaccessFile, $htaccessContent);
            $logger->info('htacces rule added', $context);

            fclose($htaccessFile);
            $logger->info('htacces file closed', $context);

            chmod($path, 0644);
            return;
        } catch (\Exception $e) {
            error_log($e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
        }
    }
}
