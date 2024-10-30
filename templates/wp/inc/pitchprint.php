<?php 
if(\hp_is_pitchprint_plugin_active()):
?>
<div class="wphp-hidden-div" >
      <input type="hidden" id="_w2p_set_option" name="_w2p_set_option" value="">
      <div id="pp_main_btn_sec" class="ppc-main-btn-sec"> </div>
      <input type="hidden" id="wphp_hidden_product_id" value="<?php echo (esc_attr($args['product_id'])) ?? '';?>" />  
      <input type="hidden" id="wphp_hidden_product_name" value="<?php echo (esc_attr($args['product_slug'])) ?? '';?>" />
      <input type="hidden" id="wphp_old_pitchprint_design_id" />
      <input type="hidden" id="wphp_old_w2p_set_option" />
      <div style="display:none;" id="wphp_original_product_image" ></div>
      <div style="display:none;" id="wphp_pitchprint_product_image" ></div>
</div>
<?php endif;?>