<?php
/**
* PHP view.
*
*/
?>
<script>
    jQuery('tr[data-order_item_id="<?php echo $args['item_id'];?>"] .quantity .edit').removeClass("edit");
    //jQuery('tr[data-order_item_id="<?php echo $args['item_id'];?>"] .wc-order-edit-line-item .wc-order-edit-line-item-actions .edit-order-item').remove();
</script>