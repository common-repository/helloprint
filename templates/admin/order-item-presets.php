<?php
/**
* PHP view.
*
*/
?>


<div  class="wphp-order-item-preset-div ">
	<form class="wphp-preset-submit-form">
		<div class="wphp-preset-message-div"></div>
		<table class="wphp-list-table">
			<input type="hidden" name="item_id_preset" class="helloprint_order_hidden_itemid" value="<?php echo esc_attr($args['item_id']);?>" />
			<input type="hidden" name="order_id_preset" class="helloprint_order_hidden_order_id" value="<?php echo esc_attr($args['order_id']);?>" />
			<input type="hidden" name="is_hp_product" class="helloprint_order_hidden_is_hp_product" value="<?php echo esc_attr($args['is_hp_product']);?>" />
			<input type="hidden" name="order_item_quantity" class="helloprint_order_item_quantity" value="<?php echo esc_attr($args['order_item_quantity']);?>" />

			
			<input type="hidden" class="helloprint_old_preset_id" value="<?php echo !empty($args['line_item_preset']) ? (esc_attr($args['line_item_preset']->preset_id)) ?? '' : '';?>" />
			<input type="hidden" class="helloprint_old_service_level" value="<?php echo !empty($args['line_item_preset']) ? (esc_attr($args['line_item_preset']->service_level)) ?? '' : '';?>" />
			<input type="hidden" class="helloprint_old_preset_quantity" value="<?php echo !empty($args['line_item_preset']) ? (esc_attr($args['line_item_preset']->quantity)) ?? '' : '';?>" />
			<input type="hidden" name="available_option_type" class="helloprint_preset_option_type" value="" />
			
			<tbody>
				<?php if (!empty($args['all_available_presets'])): ?>
				<tr>
					<td>
						<label>
							<?php echo wp_kses(_translate_helloprint('Helloprint Preset', "helloprint"), true) ?>
						</label>
					</td>
					<td>
						<div class="wphp-select2-div">
							<select  id="helloprint_select_o_preset_<?php echo esc_attr($args['item_id']);?>" style=""  name="order_item_preset" class="select wphp-select2 helloprint_preset_order_item_preset" >
								<option data-variantkey="" value=""><?php echo wp_kses(_translate_helloprint('Select Order Preset', "helloprint"), false) ?></option>
								<?php foreach($args['all_available_presets'] as $preset):?>
									<option value="<?php echo esc_attr($preset->id);?>"
										data-variantkey="<?php echo esc_attr($preset->helloprint_variant_key);?>"
										<?php if(!empty($args['line_item_preset']->preset_id) && $args['line_item_preset']->preset_id === $preset->id):?>
											selected="selected"
										<?php elseif(!empty($args['sku']) && $args['sku'] === $preset->helloprint_item_sku):?>
											selected="selected"
										<?php endif;?>
										><?php echo esc_attr($preset->order_preset_name);?></option>
									<?php endforeach;?>
								</select>
							</div>
						</td>
						<td>
							<div class="wphp-select2-div">
								<select style="" class="helloprint_preset_order_item_service_level select wphp-select2" name="order_item_preset_service_level"  >
									<option value=""><?php echo wp_kses(_translate_helloprint('Select Service Level', "helloprint"), false) ?></option>

								</select>
							</div>
						</td>

						<td class="helloprint_preset_quantity_select_td">
							<div class="wphp-select2-div">
								<select style=""  name="order_item_preset_quantity" class="select  wphp-select2 helloprint_preset_order_item_quantity" >
									<option value=""><?php echo wp_kses(_translate_helloprint('Select Quantity', "helloprint"), false) ?></option>

								</select>
							</div>
						</td>
						<td>
							<div class="wphp-artworks-lists" >

							</div>
						</td>

					</tr>
					
					<tr>
						<td colspan="5">
							<strong><?php echo wp_kses(_translate_helloprint('Variant Key', "helloprint"), true) ?></strong>
							 : <span class="wphp-load-selected-variantkey-div"></span>
						</td>
					</tr>
					<?php if(!empty($args["all_hp_presets"])): ?>
					<tr id="helloprint_order_details_hp_preset_artworks" >
						<td colspan="5">
							<span class="helloprint_prefer_files-div helloprint_prefer_file-artwork-div" >
								<input id="helloprint_preset_prefer_preset_artwork" type="radio" name="helloprint_preset_prefer_files" value="hp_preset_artwork" <?php if ($args["helloprint_prefer_file"] == "hp_preset_artwork") echo 'checked="checked"'; ?> <?php if (!empty($args['helloprint_order_id'])) { echo " readonly='readonly' disabled='disabled' "; } ?> />
							</span>
							<strong><?php echo wp_kses(_translate_helloprint('Preset File', "helloprint"), true) ?></strong>
							<div >
							<?php foreach($args['all_hp_presets'] as $pfile):
										$file_arr = explode('/', $pfile->file_url);
										$file_name = end($file_arr );
										if (strpos($pfile->file_url, "//") === FALSE) {
											$file_name = substr($file_name, strpos($file_name, "-") + 1); 
										}
										if (!empty($file_name)):
										?>
										<div class="wphp-single-hp-preset-file" >
											<span>
												<?php
												
												echo esc_attr($file_name);
												$file_full_url = (strpos($pfile->file_url, "//") === false) ? esc_url_raw(get_site_url() . $pfile->file_url) : $pfile->file_url;
												?>
											</span><br>
											<a target="_blank" href="<?php echo esc_url($file_full_url);?>" class="btn btn-sm btn-outline-primary"> <?php echo wp_kses(_translate_helloprint('Download', 'helloprint'), true);?> </a>
										</div>
										<?php
										endif; 
									endforeach;?>
							</div>
						</td>

					</tr>
					<?php endif;?>
					<tr class="helloprint_order_item_preset_custom_options">
					</tr>
					<?php endif;?>
					<tr class="wphp-preset-file-uplaoder hp-preset-file-related-class">
						<td colspan="5">
							<input type="hidden" class="helloprint_item_id" value="<?php echo esc_attr($args['item_id']);?>"/>
							<?php if(count($args['preset_files']) > 0 || empty($args['helloprint_order_id'])):?>
							<?php if(!empty($args["all_hp_presets"])): ?>
								<span class="helloprint_prefer_files-div helloprint_prefer_file-upload-div" >
									<input type="radio" id="helloprint_preset_prefer_upload_file" name="helloprint_preset_prefer_files" value="upload_files" <?php if ($args["helloprint_prefer_file"] == "upload_files") echo 'checked="checked"'; ?> <?php if (!empty($args['helloprint_order_id'])) { echo " readonly='readonly' disabled='disabled' "; } ?> />
								</span>
							<?php endif;?>
							<strong><?php echo wp_kses(_translate_helloprint("Files:", "helloprint"), true);?></strong>
							
							<?php endif; ?>
							<div class="wphp-old-artworks">
								<?php 
									if(count($args['preset_files']) > 0):
									foreach($args['preset_files'] as $pfile):
										$file_arr = explode('/', $pfile->file_url);
										$file_name = end($file_arr );
										if (strpos($pfile->file_url, "//") === FALSE) {
											$file_name = substr($file_name, strpos($file_name, "-") + 1); 
										}
										if (!empty($file_name)):
										?>
										<div class="wphp-single-preset-file" >
											<input type="hidden" name="helloprint_order_preset_file_upload[]" value="<?php echo esc_attr($pfile->file_url);?>" />
											<span>
												<?php
												
												echo esc_attr($file_name);
												$file_full_url = (strpos($pfile->file_url, "//") === false) ? esc_url_raw(get_site_url() . $pfile->file_url) : $pfile->file_url;
												?>
											</span><br>
											<a target="_blank" href="<?php echo esc_url($file_full_url);?>" class="btn btn-sm btn-outline-primary"> <?php echo wp_kses(_translate_helloprint('Download', 'helloprint'), true);?> </a>
											<?php if (empty($args['helloprint_order_id'])):?>
												&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
											<a href="#" class="wphp-admin-itemline-preset-remove btn btn-sm btn-outline-danger"><?php echo wp_kses(_translate_helloprint('Remove', 'helloprint'), true);?></a>
											<?php endif;?>
										</div>
										<?php
										endif; 
									endforeach;
								endif; 
								?>
							</div>
							
							
							<?php if (empty($args['helloprint_order_id'])):?>
							<div class="example-class hp-preset-file-related-class">
								<div class="wphp-order-preset-file-upload">         
									<div id="helloprint_order_preset_<?php echo esc_attr($args['item_id']);?>"></div>
									<input type="file" data-id="helloprint_order_preset_<?php echo esc_attr($args['item_id']);?>" class="helloprint_order_preset_file_each helloprint_order_preset_<?php echo esc_attr($args['item_id']);?>" name="helloprint_order_preset_file_upload[]" multiple="multiple" />
								</div> 
							</div>

							<div class="wphp-example-class hp-preset-file-related-class">
								<h4><?php echo wp_kses(_translate_helloprint("or, enter public URL", 'helloprint'), true);?></h4>
								<input type="url" name="helloprint_artwork_external_url" class="wphp-full-width-input helloprint_artwork_external_url" 
								value="<?php echo (esc_attr($args['external_file_url'])) ?? '';?>"
								id="helloprint_artwork_external_url-<?php echo esc_attr($args['item_id']);?>"
								/>
								<br/>
								<span style="display: none;" class="text-danger wphp-invalid-external-url"><?php echo wp_kses(_translate_helloprint("Invalid Url", 'helloprint'), true);?></span>
							</div>
							<?php endif;?>

						</td>

					</tr>
					<?php if (empty($args['helloprint_order_id'])):?>
					<tr class="wphp-preset-file-uplaoder-btn">
						<td colspan="5">
							<button class="button button-primary wphp-submit-order-item-preset"><?php echo wp_kses(_translate_helloprint("Save", 'helloprint'), true);?></button>
						</td>
					</tr>
					<?php endif;?>
				</tbody>
			</table>
		</form>
	</div>
	<script>
		jQuery(document).ready(function ($) {
			
			if(jQuery("#helloprint_select_o_preset_<?php echo esc_attr($args['item_id']);?>").length > 0) {
				var $this = jQuery("#helloprint_select_o_preset_<?php echo esc_attr($args['item_id']);?>");
            	var parentDiv = $this.closest(".wphp-order-item-preset-div");
            	var old_preset_id = parentDiv.find(".helloprint_old_preset_id").val();
            	var old_service_level = parentDiv.find(".helloprint_old_service_level").val();
            	var old_quantity = parentDiv.find(".helloprint_old_preset_quantity").val();
            	helloprint_lists_from_presets($this, old_service_level, old_quantity);
				parentDiv.find("select").each(function(){
					jQuery(this).css("width", "100%");
				});

				if (parentDiv.find(".filepond--browser").length <= 0) {
					createFilePond('.helloprint_order_preset_<?php echo esc_attr($args['item_id']);?>');
				}
			}
		});
	</script>
