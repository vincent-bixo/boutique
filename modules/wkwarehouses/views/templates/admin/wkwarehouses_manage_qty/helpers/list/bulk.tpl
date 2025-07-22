{*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="height-transition height-transition-hidden container-header" id="bulk-header">
    <div class="panel">
        <div class="panel-heading"><i class="icon-info-circle"></i> {l s='Choose your appropriate action' mod='wkwarehouses'}</div>
        <div class="row col-lg-12">
            <div class="form-group">
                <label class="control-label col-lg-1 text-right" style="padding-top:6px;">{l s='Action' mod='wkwarehouses'}</label>
                <div class="col-lg-6">
                    <select class="bulk_action">
                        <option value="switchBulkProductsToAsm">{l s='Switch to Advanced Stock Management system' mod='wkwarehouses'}</option>
                        <option value="disableBulkAsmFromProducts">{l s='Disable Advanced Stock Management system' mod='wkwarehouses'}</option>
                        <option value="alignQtiesToPrestashop">{l s='If there is gap, fix Warehouses quantities to be the same as the Prestashop physical quantities' mod='wkwarehouses'}</option>
                        <option value="alignQtiesToWarehouses">{l s='If there is gap, fix Prestashop quantities to be the same as the Warehouses quantities' mod='wkwarehouses'}</option>
                        <option value="alignReservedQties" {if !isset($smarty.request.productFilter_product_type) || (isset($smarty.request.productFilter_product_type) && $smarty.request.productFilter_product_type != 4)}disabled="disabled" style="color:#bbb"{/if}>{l s='Fix if there is a gap between the warehouses reserved quantity and Prestashop reserved quantity' mod='wkwarehouses'}</option>
                    </select>
                </div>
            </div>
            <div class="clearfix">&nbsp;</div>
            <div class="form-group">
                <label class="control-label col-lg-1 text-right" style="padding-top:6px;">{l s='For' mod='wkwarehouses'}</label>
                <div class="col-lg-6">
                    <select class="bulk_for">
                        <option value="sel">{l s='Selected products' mod='wkwarehouses'}</option>
                        <option value="all">{l s='All products' mod='wkwarehouses'}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="clearfix">&nbsp;</div>
        <hr />
        <a class="button btn btn-success pull-left col-lg-7" style="margin-left:5px; letter-spacing:2px; font-size:14px" id="submit-bulk-btn" href="javascript:void(0);">
            <i class="icon-send"></i> {l s='APPLY' mod='wkwarehouses'}
        </a>
        <div style="clear:both"></div>
    </div>
</div>
