<?php
/**
 * PHP view.
 *
 */
?>
<div>
    <div id="setting-error-settings_updated" style="display:none" class="notice notice-success settings-error is-dismissible">
        <p><strong id="success-message"></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo wp_kses(_translate_helloprint('Dismiss this notice', 'helloprint'), true);?>.</span></button>
    </div>
    <div id="setting-error-401" style="display: none;" class="notice notice-error settings-error is-dismissible">
        <p><strong id="error-mesage"></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo wp_kses(_translate_helloprint('Dismiss this notice', 'helloprint'), true);?>.</span></button>
    </div>
</div>