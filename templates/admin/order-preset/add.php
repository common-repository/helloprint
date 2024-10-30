<?php
/**
* PHP view.
*
*/
?>
<div class="wrap">
    <h1><?php echo wp_kses(_translate_helloprint('Add Print-on-Demand Preset', "helloprint"), true); ?></h1>
    <form enctype="multipart/form-data" id="helloprint_add_edit_preset_form" name="wphp-add-pod" method="POST" action="">
        <input type="hidden" name="action" value="add">
        <?php include("includes/common.php");?>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo wp_kses(_translate_helloprint('Save', "helloprint"), false) ?>"> <?php echo wp_kses(_translate_helloprint('* required', "helloprint"), true) ?></p>    
     </form>
</div>