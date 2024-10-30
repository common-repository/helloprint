<?php

/**
 * PHP view.
 *
 */
?>
<div class="wrap">
    <h1><?php echo esc_html(_translate_helloprint('Link Pitchprint Design', "helloprint")); ?></h1>
    <form name="wphp-pitch-print" method="POST" action="">
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>" />
        <table class="form-table" role="presentation">
            <tbody>
                <tr class="example-class">
                    <th scope="row">
                        <label for="helloprint_pitch_print_name"><?php echo esc_html(_translate_helloprint('Name', "helloprint")) ?> *</label>
                    </th>
                    <td>
                        <div class="example-class">
                            <input required="true" type="text" class="regular-text" id="helloprint_pitch_print_name" name="name" value="<?php echo esc_attr($name); ?>">
                        </div>
                    </td>
                </tr>

                <tr class="example-class">
                    <th scope="row">
                        <label for="helloprint_pitch_print_design"><?php echo esc_html(_translate_helloprint('Pitchprint Design Id', "helloprint")) ?> *</label>
                    </th>
                    <td>
                        <div class="example-class">
                            <input required="true" type="text" class="regular-text" id="helloprint_pitch_print_design" name="design_id" value="<?php echo esc_attr($design_id); ?>">
                        </div>
                    </td>
                </tr>

                <tr class="example-class">
                    <th scope="row">
                        <label for="helloprint_variant_key"><?php echo esc_html(_translate_helloprint('Helloprint Variant Key', "helloprint")) ?> *</label>
                    </th>
                    <td>
                        <div class="example-class">
                            <input required="true" type="text" class="regular-text" id="helloprint_variant_key" name="variant_key" value="<?php echo esc_attr($variant_key); ?>">
                        </div>
                    </td>
                </tr>


            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr(_translate_helloprint('Save', "helloprint")) ?>"> <?php echo esc_html(_translate_helloprint('* required', "helloprint")) ?></p>
    </form>
</div>