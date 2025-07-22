{*
* Shipping Edit
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}                    
<script type="text/javascript">
    (function($) {
        $(function() {            
            var displaySummaryOriginal = displaySummary;

            displaySummary = function(jsonSummary) {
                $('#shipping_price_edit').val(jsonSummary.summary.total_shipping);
                $('#shipping_price_edit + .input-group-addon').text(jsonSummary.currency.sign);
                
                displaySummaryOriginal(jsonSummary);
            };
            
            $('#shipping_price').replaceWith($('#shipping_price_tpl').html());
            
            $('#shipping_price_edit').on('change', function() {
                var free_shipping = $('input[name=free_shipping]:checked').val();
                
                $.ajax({
                        type:"POST",
                        url: "{$link->getAdminLink('AdminCarts')|escape:'quotes':'UTF-8'}",
                        async: true,
                        dataType: "json",
                        data : {
                                ajax: "1",
                                token: "{getAdminToken tab='AdminCarts'}",
                                tab: "AdminCarts",
                                action: "UpdateDeliveryOption",
                                update_shipping: $('#shipping_price_edit').val(),
                                id_cart: id_cart,
                                id_customer: id_customer,
                                free_shipping: free_shipping
                                },
                        success : function(res)
                        {
                                displaySummary(res);
                        }
                });
            });
        });
    })(jQuery);
</script>

<script id="shipping_price_tpl" type="text/template">
    <div class="input-group col-lg-2">
            <input type="text" id="shipping_price_edit" class="form-control-static" name="shipping_price" onchange="noComma('shipping_price_edit');" />
            <span class="input-group-addon">
                
            </span>
    </div>
</script>