<?php
/**
 * PHP view.
 *
 */
?>
<div class="wphp-not-available">
    <h3 class="wphp-step">
        <span id="wphp-outofstock" class="wphp-step-not-available"></span>
        <?php echo wp_kses(_translate_helloprint("Temporarily unavailable", 'helloprint'), true);?>
    </h3>
    <p>
        <?php echo wp_kses(_translate_helloprint("We're sorry, but this product is temporary unavailable to order.Please come back soon or contact our customer service.", 'helloprint'), true);?>  
    </p>
</div>
<style>
    .wphp-not-available {
        border: 1px solid #dadada;
        margin: 10px 0 30px 0;
        padding: 10px 20px;
    }
</style>