{*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Frederic Moreau
 *  @copyright 2020 BeComWeb
 *  @license   LICENSE.txt
 *}

<h2>{l s='Change product creation date' mod='changeproductcreationdate'}</h2>
<div class="row">
    <div class="col-md-8">
        <!-- Instructions -->
        <div class="alert expandable-alert alert-info mt-3" role="alert">
            <p class="alert-text">{l s='Use the form below to update this product\'s creation date in database' mod='changeproductcreationdate'}</p>
        </div>
    </div>
</div>
<div class="row" id="changeproductcreationdate_result" style="display:none">
    <div class="col-md-8">
        <!-- Result -->
        <div class="alert expandable-alert mt-3" role="alert"><p class="alert-text"></p></div>
    </div>
</div>
<div class="row">
    <!-- Form -->
    <div class="col-md-4 ">
        <label class="form-control-label">{l s='Product creation date :' mod='changeproductcreationdate'}</label>
        <div class="input-group datepicker">
            <input type="text" class="form-control" id="product_creation_date" value="{$product_creation_date}" name="cpcd[changeproductcreationdate][product_creation_date]">
            <div class="input-group-append">
                <div class="input-group-text"><i class="material-icons">date_range</i></div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mt-3">
        <button id="submit_creation_date" class="btn btn-outline-secondary add">
            <i class="material-icons">update</i> {l s='Save new date' mod='changeproductcreationdate'}
        </button>
    </div>
    <input type="hidden" name="cpcd_product_id" value="{$product_id}">
    <input type="hidden" name="cpcd_module_token" value="{$module_token}">
</div>

<script type="text/javascript">
    var ajax_error_text = "{$ajax_error_text}";
</script>