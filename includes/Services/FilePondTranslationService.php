<?php



namespace HelloPrint\Inc\Services;

class FilePondTranslationService
{
    public $translationService;
    private $allLabels;

    public function __construct()
    {
        $this->translationService = new TranslationService();
        $this->allLabels = $this->getAllLabels();
    }

    public function getAllTextsForFilePond()
    {
        $translated = [
            'labelIdle' => $this->getLabelIdle()
        ];
        foreach ($this->allLabels as $key => $label) {
            $translated[$key] = $this->translationService->translate($label);
        }
        return $translated;
    }

    public function getLabelIdle()
    {
        $firstText = $this->translationService->translate("Drag & Drop files here or click to");
        $secondText = $this->translationService->translate("Browse");
        $finalText = $firstText . ' ' . '<span class="filepond--label-action">' . $secondText . '</span>';
        return $finalText;
    }

    private function getAllLabels()
    {
        return [
            'labelInvalidField' => 'Field contains invalid files',
            'labelFileWaitingForSize' => 'Waiting for size',
            'labelFileSizeNotAvailable' => 'Size not available',
            'labelFileLoading' => 'Loading',
            'labelFileLoadError' => 'Error during load',
            'labelFileProcessing' => 'Uploading',
            'labelFileProcessingComplete' => 'Upload complete',
            'labelFileProcessingAborted' => 'Upload cancelled',
            'labelFileProcessingError' => 'Error during upload',
            'labelFileProcessingRevertError' => 'Error during revert',
            'labelFileRemoveError' => 'Error during remove',
            'labelTapToCancel' => 'tap to cancel',
            'labelTapToRetry' => 'tap to retry',
            'labelTapToUndo' => 'tap to undo',
            'labelButtonRemoveItem' => 'Remove',
            'labelButtonAbortItemLoad' => 'Abort',
            'labelButtonRetryItemLoad' => 'Retry',
            'labelButtonAbortItemProcessing' => 'Cancel',
            'labelButtonUndoItemProcessing' => 'Undo',
            'labelButtonRetryItemProcessing' => 'Retry',
            'labelButtonProcessItem' => 'Upload',
            'labelMaxFileSize' => "Maximum file size is " . helloprint_get_max_file_upload_size()
        ];
    }
}
