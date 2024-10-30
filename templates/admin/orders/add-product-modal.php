<div id="wc-order-modal-add-wphp-products" class="wphp-modal">
	<div class="wphp-modal-content">
		<span class="wphp-close">&times;</span>
		<h3><?php echo wp_kses(_translate_helloprint('Add Helloprint Product', 'helloprint'), true) ?></h3>
		<form action="#" method="post">
			<div class="wphp-modal-body">
				<div class="wphp-product-select-for-order">
					<?php
					helloprint_wp_select(array(
						'id' => 'add-wphp-product-item',
						'label' => wp_kses(_translate_helloprint('Select Product', 'helloprint'), false),
						'options' => $args['selectProducts']
					));
					?>
				</div>
				<div class="wphp-product-custom-size"></div>
				<div class="wphp-order-product-attributes"></div>
				<div class="wphp-product-custom-quantity"></div>
				<div class="wphp-order-service-quantity">
					<div class="wphp-default-quantity">
						<label for="helloprint_product_quantity"><?php echo wp_kses(_translate_helloprint('Quantity', 'helloprint'), true) ?> </label>
						<select name="helloprint_product_quantity" id="helloprint_product_quantity" class="wphp-width-100 wphp-options">
						</select>
					</div>
					<div class="wphp-delivery-time">
						<label for="helloprint_service_level"><?php echo wp_kses(_translate_helloprint('Delivery Time', 'helloprint'), true) ?></label>
						<select name="helloprint_service_level" id="helloprint_service_level" class="wphp-width-100 wphp-options">
						</select>
					</div>
				</div>
				<div class="wphp-product-file-upload hidden">
					<hr>
					<div>
						<legend>
							<?php echo wp_kses(_translate_helloprint('Upload artwork files(s)', 'helloprint'), true) ?>
							<input type="file" class="helloprint_filepond_order_item_product_file_upload" name="helloprint_filepond_order_item_product_file_upload[]" multiple="multiple">
						</legend>
					</div>
				</div>
				<div id="helloprint_product_prices">
					<?php if (wc_tax_enabled()) : ?>
						<h4><?php echo wp_kses(_translate_helloprint('Purchase Price', 'helloprint'), true) ?></h4>
					<?php endif ?>
					<div class="wphp-grid">
						<div class="price_excl_tax_label">
							<?php if (wc_tax_enabled()) : ?>
								<?php echo wp_kses(_translate_helloprint('Price Excluding Tax', 'helloprint'), true) ?>:
								<strong><span class="helloprint_product_price_exclude_tax_without_margin"></span></strong>
							<?php else : ?>
								<h4><?php echo wp_kses(_translate_helloprint('Purchase Price', 'helloprint'), true) ?>: <span class="helloprint_product_price_exclude_tax_without_margin"></span></h4>
							<?php endif ?>
						</div>
					</div>
					<?php if (wc_tax_enabled()) : ?>
						<div class="wphp-grid">
							<div class="price_incl_tax_label">
								<?php echo wp_kses(_translate_helloprint('Price Including Tax', 'helloprint'), true) ?>:
								<strong> <span class="helloprint_product_price_without_margin"></span> </strong>
							</div>
						</div>
					<?php endif ?>
				</div>
			</div>
			<div class="wphp-admin-offer-price hidden">
				<label for="helloprint_product_excl_tax_price">
					<?php if (wc_tax_enabled()) : ?>
						<?php echo wp_kses(_translate_helloprint('Offer Price Excluding Tax', 'helloprint'), true) ?>
					<?php else : ?>
						<?php echo wp_kses(_translate_helloprint('Offer Price', 'helloprint'), true) ?>
					<?php endif ?>
				</label>
				<input type="number" onClick="this.select();" id="helloprint_product_excl_tax_price_input" novalidate>
			</div>
			<input type="hidden" id="helloprint_attributes_count" value="0">
			<input type="hidden" id="helloprint_product_price_input">
			<input type="hidden" id="helloprint_product_incl_tax_price_input">
			<input type="hidden" id="helloprint_product_id">
			<input type="hidden" id="helloprint_product_sku" value="">
			<input type="hidden" id="helloprint_product_variant_key" value="">
			<input type="hidden" id="helloprint_product_options">
			<input type="hidden" id="helloprint_product_options_labels">
			<input type="hidden" id="helloprint_external_product_id">
			<input type="hidden" id="helloprint_external_product_text">
			<input type="hidden" id="helloprint_product_tax_rate_input">
			<br>
			<button type="button" id="wphp-add-product-to-order-btn" class="button button-primary button-large" disabled><?php echo wp_kses(_translate_helloprint('Add', 'helloprint'), true); ?></button>
		</form>
	</div>
</div>