{*
* Module My Addons
* 
*  @author    My Addons - support@myaddons.io
*  @uses Prestashop modules
*  @since 1.0
*  @copyright Copyright &copy; 2017, My Addons
*  @license   My Addons
*}

{if isset($errorPrint)}
    <!-- Modal content -->
    <div id="content-modal">
        <div class="modal fade" id="bt-modal" tabindex="-1" role="dialog" aria-labelledby="bt-modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span><span class="sr-only">{l s='Close' mod='btprintinvoicedeliveryslip'}</span>
                        </button>
                        <h2 class="modal-title" id="bt-modalLabel">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-my_addons-1.png" height="45"/>&nbsp;{l s='Print invoices / delivery Slip in mass' mod='btprintinvoicedeliveryslip'} <small>v{$version|escape:'htmlall':'UTF-8'}</small>
                        </h2>
                    </div>
                    <div class="modal-body">
                        <h3 class="modal-title">{l s='Error during process' mod='btprintinvoicedeliveryslip'}</h3>
                        <div class="alert alert-danger" role="alert">{$errorPrint|escape:'htmlall':'UTF-8'}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='btprintinvoicedeliveryslip'}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
<div id="PrintInMass">
    <li class="divider"></li>
    <li>
        <a href="#" onclick="sendBulkAction($(this).closest('form').get(0), 'submitBulkupdateOrderPrintInvoices');refresh_action();">
        <i class="icon-file-text"></i>&nbsp;{l s='Print invoices' mod='btprintinvoicedeliveryslip'}
        </a>
    </li>
    <li>
        <a href="#" onclick="sendBulkAction($(this).closest('form').get(0), 'submitBulkupdateOrderPrintDeliverySlip');refresh_action();">
        <i class="icon-truck"></i>&nbsp;{l s='Print delivery slip' mod='btprintinvoicedeliveryslip'}
        </a>
    </li>
    <li>
        <a href="#" onclick="sendBulkAction($(this).closest('form').get(0), 'submitBulkupdateOrderPrintAll');refresh_action();">
        <i class="icon-file-text"></i>&nbsp;<i class="icon-truck"></i>&nbsp;{l s='Print invoices and delivery slip' mod='btprintinvoicedeliveryslip'}
        </a>
    </li>
</div>

<script type="text/javascript">
    $(document).ready(function() 
    {
        $(".bulk-actions > ul.dropdown-menu").append($("#PrintInMass").html());
       
        {if isset($errorPrint)}
            $("#main .bootstrap").prepend($("#content-modal").html());
            $("#bt-modal").modal();     
        {/if}
    });
    
    function refresh_action() {
      var action = ($("#form-order").attr('action'));
      action = action.replace("&submitBulkupdateOrderPrintInvoices", "");
      action = action.replace("&submitBulkupdateOrderPrintDeliverySlip", "");
      action = action.replace("&submitBulkupdateOrderPrintAll", "");
      $("#form-order").attr('action', action);
    }
</script>