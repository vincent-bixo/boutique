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
<div class="form-wrapper">
    <div class="alert alert-info">
        <ul style="list-style:decimal; padding-left:15px">
            <li>{l s='Indicate the quantity you wish to increase/decrease' mod='wkwarehouses'}.</li>
            <li>{l s='The indicated quantity will be applied to the checked products (including combinations) and [1]selected target warehouse(s)[/1]' tags=['<strong>'] mod='wkwarehouses'}.</li>
            <li>{l s='Only products that [1]use advanced stock management[/1] system are concerned' tags=['<strong>'] mod='wkwarehouses'}.</li>
            <li style="list-style-type:none">{l s='For this, use the filter above the buttons to look for only products that use advanced stock management' mod='wkwarehouses'}.</li>
            <li>{l s='Negative value is allowed' mod='wkwarehouses'}.</li>
            <li>{l s='Click on Apply button to submit' mod='wkwarehouses'}.</li>
        </ul>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-5 text-right" style="padding-top:7px">{l s='Quantity' mod='wkwarehouses'}</label>
        <div class="col-lg-7">
            <input type="text" class="fixed-width-md asm_qty" required="required" value="1">
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<br />
<a class="button btn btn-success pull-right" id="submit-asmqty-btn" href="javascript:void(0);">
    <i class="icon-send"></i> {l s='Apply' mod='wkwarehouses'}
</a>
