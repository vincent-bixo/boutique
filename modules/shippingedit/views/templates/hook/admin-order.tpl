{*
* Shipping Edit
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}

{if Tools::version_compare('1.7', $smarty.const._PS_VERSION_)}
<script type="text/javascript">
    (function($) {
        $(function() {
            var template = function(tpl_id, data) {
                return $('#' + tpl_id).html().replace(/\[([^\]]+)?\]/g, function($1, $2) { return data[$2]; });
            }
            
            var shipping_cost = $('#shipping_table .shipping_number_show').parent().prev().text();
            
            var item = {
                'value': parseFloat(shipping_cost.replace(/[^0-9,\.]/g, '').replace(',', '.')) 
            };
            
            $('#modal-shipping .modal-body .container-fluid').prepend(template('shipping_price_tpl', item))
        });
    })(jQuery);
</script>

<script id="shipping_price_tpl" type="text/template">
    <div class="form-group">
        <div class="col-lg-5">{l s='Shipping cost' mod='shippingedit'}</div>
        <div class="col-lg-7">
            <input type="text" id="update_shipping" class="form-control-static" name="update_shipping" value="[value]" onchange="noComma('update_shipping');" />
        </div>
    </div>
</script>
{else}
<script type="text/javascript">
    (function ($) {
        $(function () { 
            $('#shipping_table .edit_shipping_number_link').each(function() {
                var el = $(this);
                
                el.unbind('click')
                .popover({
                    placement: 'top',
                    html: true,
                    content: $('#shipping-edit-form').html()
                })
                .on('click', function(e) {
                    e.preventDefault();
                })
                .on('shown.bs.popover', function (e) {
                    var el = $(this).closest('tr');

                    var values = {
                        id_order_carrier: $('[name=id_order_carrier]', el).val(),
                        update_shipping: parseFloat($.trim($('td:eq(4)', el).text()).replace(/[^0-9,\.]/g, '').replace(',', '.')),
                        tracking_number: $.trim($('#shipping_number_show, .shipping_number_show', el).text())
                    };
                    
                    var form = $('.popover form', $(this).parent());
                    
                    $.each(values, function(k, v) {
                        $('[name=' + k + ']', form).val(v);
                    });
                }).parent().on('click', '.popover-close', function() {
                    el.popover('hide');
                });
            });
        });
    })(jQuery);
</script>

<script type="text/template" id="shipping-edit-form">
    <form action="{$link->getAdminLink('AdminOrders')|escape:'html':'UTF-8'}&amp;vieworder&amp;id_order={$order->id|intval}" method="post">
        <div class="form-group">
            <label>{l s='Shipping cost' mod='shippingedit'}</label>
            <input type="text" name="update_shipping" class="input-small" />
        </div>
        <div class="form-group">
            <label>{l s='Tracking number' mod='shippingedit'}</label>
            <input type="text" name="tracking_number" class="input-small" />
        </div>
        <div>
            <input type="hidden" name="id_order_carrier" />
            <button type="submit" name="submitShippingNumber" class="btn btn-primary btn-sm"><i class="icon-check"></i></button>
            <button type="button" class="btn btn-default btn-sm popover-close"><i class="icon-remove"></i></button>
        </div>
    </form>
</script>
{/if}