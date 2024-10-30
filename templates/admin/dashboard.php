<?php
/**
 * PHP view.
 *
 */
?>
<div class="wrap">
    <h1><?php echo wp_kses(_translate_helloprint('Settings', 'helloprint'), true);?></h1>
    <?php settings_errors(); ?>
    <form name="wphp-settings" method="POST" action="options.php">
        <?php
        settings_fields('helloprint_setting_options_group');
        do_settings_sections('helloprint');
        submit_button(wp_kses(_translate_helloprint('Save Changes', 'helloprint'), false));

         ?>
    </form>
    <?php echo wp_kses(_translate_helloprint("Don't have your Api Keys?", "helloprint"), true);?> <a href="https://developers.helloprint.com/reference/wphp-api" target="_blank"><?php echo wp_kses(_translate_helloprint("Click here", "helloprint"), true);?></a> <?php echo wp_kses(_translate_helloprint("to get one", "helloprint"), true);?>
</div>