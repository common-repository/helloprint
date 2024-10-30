<?php



namespace HelloPrint\Inc\Services;

class TranslationService
{
    public function getAllTexts()
    {
        $dropzone = [
            'dropzone_upload_text' => wp_kses(_translate_helloprint("Drag & Drop files here or click to browse", 'helloprint'), false)
        ];
        $filePond = (new FilePondTranslationService())->getAllTextsForFilePond();
        $finalArr = array_merge($dropzone, $filePond);
        return $finalArr;
    }

    public function getAllTextsForAdmin()
    {
        $translateString =  [
            'select_one' => _translate_helloprint("Select One", 'helloprint'),
            'something_went_wrong' => _translate_helloprint("Something went wrong", 'helloprint'),
            'temporarily_unavailable' => _translate_helloprint("Temporarily unavailable", 'helloprint'),
            'invalid_product_please_select_another_product' => _translate_helloprint("Invalid product, Please select another product", 'helloprint'),
            'insure_order_has_calculated' => _translate_helloprint("Please ensure order has been recalculated prior to submitting.", 'helloprint'),
        ];
        $filePond = (new FilePondTranslationService())->getAllTextsForFilePond();
        $finalArr = array_merge($translateString, $filePond);
        return $finalArr;
    }

    public function translate($string)
    {
        return wp_kses(_translate_helloprint($string, 'helloprint'), false);
    }
}
