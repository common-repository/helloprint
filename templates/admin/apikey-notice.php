<?php
/**
 * PHP view.
 *
 */
?>
<div>
        <div id="setting-error-settings_updated"  class="notice notice-warning settings-error is-dismissible">
                <p>
                        <strong id="warning-message">
                                <?php echo wp_kses(_translate_helloprint("Please", 'helloprint'), true); ?> <a href="admin.php?page=helloprint"><?php echo wp_kses(_translate_helloprint("Add an API key", 'helloprint'), true); ?></a> <?php echo wp_kses(_translate_helloprint("to HelloPrint to get started using HelloPrint products", 'helloprint'), true); ?> 
                        </strong>
                </p>

        </div>
</div>