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
<div id="warehouses-container">
{if !$warehouses}
	<div class="alert alert-danger">{l s='You must have at least one warehouse' mod='wkwarehouses'}!</div>
{else}
	<script type="text/javascript">
        {include file="./translations.tpl"}
		var lang_datatable = '{$lang_datatable|escape:'html':'UTF-8'}';
		var mod_path = '{$module_folder|escape:'html':'UTF-8'}';
    </script>
	{************** F I R S T   P A N E L *************}
	<div class="col-lg-6 leftPanel">
		<div class="panel">
			<div class="panel-heading"><i class="icon-link"></i> {l s='Search Products To Manage' mod='wkwarehouses'}</div>
            <table width="100%" class="panel-search">
              <tr>
                <td style="width:61%">
                    <select id="id_warehouse" class="chosen">
                        <option value="">{l s='Select Warehouse' mod='wkwarehouses'}</option>
                        <optgroup label="&nbsp;-&nbsp;{l s='Search products' mod='wkwarehouses'}:">
                            <option value="without_warehouses">{l s='with no warehouses associations' mod='wkwarehouses'}</option>
                            <option value="normal_stock">{l s='using normal stock managment' mod='wkwarehouses'}</option>
                            <option value="depends_on_stock">{l s='using advanced stock managment' mod='wkwarehouses'}</option>
                        </optgroup>
                        <optgroup label="&nbsp;-&nbsp;{l s='Search products that are in' mod='wkwarehouses'}:">
                            {foreach from=$warehouses item=warehouse}
                            <option value="{$warehouse.id_warehouse|intval}">{$warehouse.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </td>
                <td>
                    <select id="id_cat" class="chosen">
                        <option value="">{l s='Select Category' mod='wkwarehouses'}</option>
                        {foreach from=$options_cats item=option}
                        <option value="{$option['id_category']|intval}">{$option['name']|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </td>
              </tr>
              <tr>
                <td>
                    <select id="ids_attributes" multiple="multiple">
                    {if is_array($attributes) && count($attributes)}
                        {foreach from=$attributes item=groups}
                            <optgroup label="{$groups.name|escape:'html':'UTF-8'}">
                            {foreach from=$groups.attributes item=attributes}
                                <option value="{$attributes.id_attribute|intval}">{$attributes.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                            </optgroup>
                        {/foreach}
                    {/if}
                    </select>
                </td>
                <td>
                <select id="id_manufacturer" class="chosen">
                    <option value="">{l s='Select Manufacturer' mod='wkwarehouses'}</option>
                    {foreach from=$manufacturers item=manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
                </td>
              </tr>
              <tr>
                  <td>
                    <div class="suppliers-select">
                        <select id="id_supplier" class="chosen">
                            <option value="">{l s='Select Supplier' mod='wkwarehouses'}</option>
                            {foreach from=$suppliers item=supplier}
                            <option value="{$supplier.id_supplier|intval}">{$supplier.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                  </td>
                  <td>
                  <div class="searchMode">
                  <select name="searchMode" id="searchMode" class="chosen">
                    <option value="" selected="selected">{l s='Search mode' mod='wkwarehouses'}</option>
                    <option value="AND">{l s='All the words' mod='wkwarehouses'}</option>
                    <option value="OR">{l s='Any words' mod='wkwarehouses'}</option>
                    <option value="EXACT">{l s='Exacte phrase' mod='wkwarehouses'}</option>
                  </select></div>
                  <div class="searchactive">
                  <select name="searchactive" id="searchactive" class="chosen">
                    <option value="" selected="selected">{l s='Status' mod='wkwarehouses'}</option>
                    <option value="both">{l s='Both' mod='wkwarehouses'}</option>
                    <option value="active">{l s='Activated' mod='wkwarehouses'}</option>
                    <option value="disable">{l s='Deactivated' mod='wkwarehouses'}</option>
                  </select></div>
                  </td>
              </tr>
            </table>
			{***** Products list area *****}
            <table class="display" id="productsFromFilters">
                <thead>
                    <tr>
                        <th></th>
                        <th><span class="title_box">ID</span></th>
                        <th></th>
                        <th><span class="title_box">{l s='Name' mod='wkwarehouses'}</span></th>
                        <th class="sub-line-height">
                            <span class="title_box">{l s='Quantities' mod='wkwarehouses'}</span>
                        </th>
                        <th><span class="title_box">{l s='Associations' mod='wkwarehouses'}</span></th>
                        <th><span class="title_box">{l s='Use A.S.M' mod='wkwarehouses'}</span></th>
                        <th class="sub-line-height"><span class="title_box">{l s='Product' mod='wkwarehouses'}</span><br /><span class="sub-title-small">{l s='edit page' mod='wkwarehouses'}</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="dataTables_empty">{l s='Loading data' mod='wkwarehouses'}...</td>
                    </tr>
                </tbody>
            </table>
            <div class="default-option" align="center">
                <table>
                  <tr>
                    <td><label class="control-label"><i class="icon-plus"></i> <span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{$tooltip_move|escape:'html':'UTF-8'}">{l s='Move' mod='wkwarehouses'}?</span>&nbsp;&nbsp;</label></td>
                    <td><span class="switch prestashop-switch fixed-width-large">
                      <input type="radio" name="move" id="move_on" disabled="disabled" />
                      <label for="move_on">{l s='Yes' mod='wkwarehouses'}</label>
                      <input type="radio" name="move" id="move_off" checked="checked" disabled="disabled" />
                      <label for="move_off">{l s='No' mod='wkwarehouses'}</label>
                      <a class="slide-button btn"></a></span></td>
                    <td><label class="control-label"><i class="icon-plus"></i> <span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{'<br/>'|implode:$tooltip_transfer_qty|escape:'html':'UTF-8'}">{l s='Transfer also quantities' mod='wkwarehouses'}?</span>&nbsp;&nbsp;</label></td>
                    <td><span class="switch prestashop-switch fixed-width-large">
                      <input type="radio" name="transfer_qty" id="transfer_qty_on" disabled="disabled" />
                      <label for="transfer_qty_on">{l s='Yes' mod='wkwarehouses'}</label>
                      <input type="radio" name="transfer_qty" id="transfer_qty_off" checked="checked" disabled="disabled" />
                      <label for="transfer_qty_off">{l s='No' mod='wkwarehouses'}</label>
                      <a class="slide-button btn"></a></span></td>
                  </tr>
                </table>
            </div>
        </div>
        <div class="button-actions">
            <a href="javascript:void(0);" id="btn-submit-panel" onclick="associateItems()"><i class="icon-link"></i> {l s='Associate' mod='wkwarehouses'}</a>
            <a href="javascript:void(0);" id="btn-submit-panel" onclick="resetAsmStock()"><i class="icon-remove"></i> {l s='Reset Stock' mod='wkwarehouses'}</a>
            <a href="javascript:void(0);" id="btn-reset-panel" onclick="refreshLeftPanel()"><i class="icon-refresh"></i> {l s='Reset' mod='wkwarehouses'}</a>
        </div>
    </div>
    {************** S E C O N D   P A N E L **************}
    <div class="col-lg-6 rightPanel">
		<div class="panel">
            <div class="panel-heading"><i class="icon-link"></i> {l s='Select Target Warehouse(s)' mod='wkwarehouses'}</div>
            <table width="100%" class="panel-search">
              <tr>
                <td style="width:70%">
                    <div class="multiple-select-chosen">
                        <select id="ids_target" multiple="multiple">
                            <option value="ALL">{l s='All Warehouses' mod='wkwarehouses'}</option>
                            {foreach from=$warehouses item=warehouse}
                            <option value="{$warehouse.id_warehouse|intval}">{$warehouse.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
              </td>
              <td>
                <select id="id_brand" class="chosen">
                    <option value="">{l s='Select Manufacturer' mod='wkwarehouses'}</option>
                    {foreach from=$manufacturers item=manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
              </td>
             </tr>
            </table>
            <table class="display" id="productsFromWarehouses">
                <thead>
                    <tr>
                        <th></th>
                        <th><span class="title_box">ID</span></th>
                        <th></th>
                        <th><span class="title_box">{l s='Name' mod='wkwarehouses'}</span></th>
                        <th class="sub-line-height">
                            <span class="title_box">{l s='Quantities' mod='wkwarehouses'}</span>
                        </th>
                        <th><span class="title_box">{l s='Associations' mod='wkwarehouses'}</span></th>
                        <th><span class="title_box">{l s='Use A.S.M' mod='wkwarehouses'}</span></th>
                        <th><span class="title_box">{l s='Actions' mod='wkwarehouses'}</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="dataTables_empty">{l s='Loading data' mod='wkwarehouses'}...</td>
                    </tr>
                </tbody>
            </table>
            <div class="default-option" align="center">
                <table>
                    <tr>
                        <td>{l s='Use only advanced stock management' mod='wkwarehouses'}?</td>
                        <td><span class="switch prestashop-switch fixed-width-large">
                              <input type="radio" name="use_asm" id="use_asm_on" />
                              <label for="use_asm_on">{l s='Yes' mod='wkwarehouses'}</label>
                              <input type="radio" name="use_asm" id="use_asm_off" checked="checked" />
                              <label for="use_asm_off">{l s='No' mod='wkwarehouses'}</label>
                              <a class="slide-button btn"></a></span>
                          </td>
                    </tr>
                </table>
            </div>
		</div>
        <div class="button-actions">
       		<a href="javascript:void(0);" onclick="removeAssociations()"><i class="icon-unlink"></i> {l s='Remove Associations' mod='wkwarehouses'}</a>
            <a href="javascript:void(0);" class="asmStockForm"><i class="icon-edit"></i> {l s='Increase/decrease stock' mod='wkwarehouses'}</a>
            <a href="javascript:void(0);" class="addLoactionForm"><i class="icon-edit"></i> {l s='Add location' mod='wkwarehouses'}</a>
            <a href="javascript:void(0);" onclick="refreshRightPanel()" title="{l s='Reset panel' mod='wkwarehouses'}"><i class="icon-refresh"></i></a>
        </div>
    </div>
    <div id="openmsgbox"></div>
    <div style="clear:both"></div>
</div>
{/if}
{************************ LIGHBOX POPUP CONTAINERS ********************}
{if !empty($warehouses) && count($warehouses)}
<div class="bootstrap" id="modal-wkassignwarehouses-container">
    <div class="div-location"></div>
    <div class="form-location">
		<div style="text-align: right;">
			<a class="button btn btn-danger close-form-location" href="#"><i class="icon-remove-sign"></i></a>
		</div>
        <div class="content_form panel"></div>
    </div>
</div>
{/if}
