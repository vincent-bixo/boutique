{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{if isset($product->id)}
<div id="" class="panel product-tab">
  {if is_array($declinations) && count($declinations)}
  <div class="form-group row">
    <label class="control-label col-lg-3 form-control-label">
      {l s='Consider the variations as a single product' mod='opartlimitquantity'}
    </label>
    <div class="col-lg-9 col-sm">
      <div class="input-group">
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio" name="opartsingleproduct" id="opartsingleproduct_1" value="1" {if is_numeric($product->opart_max_qty) && is_numeric($product->opart_max_qty)}checked{/if}>
          <label for="opartsingleproduct_1">{l s='Yes' mod='opartlimitquantity'}</label>
          <input type="radio" name="opartsingleproduct" id="opartsingleproduct_0" value="0" {if !is_numeric($product->opart_max_qty) && !is_numeric($product->opart_max_qty)}checked{/if}>
          <label for="opartsingleproduct_0">{l s='No' mod='opartlimitquantity'}</label>
          <a class="slide-button btn"></a>
        </span>
      </div>
    </div>
  </div>
  <br><br><br>
  {/if}

  <div id="opartproductqty" {if (is_array($declinations) && count($declinations)) && (!is_numeric($product->opart_max_qty) && !is_numeric($product->opart_max_qty))}style="display: none;"{/if}>
    <h3 class="tab">{l s='LIMIT QUANTITY' mod='opartlimitquantity'}</h3>
    <div class="form-group">
      <div class="col-lg-9 col-lg-offset-3">
        <label class="control-label col-lg-3" for="reference">
            <span class="label-tooltip" data-toggle="tooltip" title=""
                  data-original-title=" {l s='Enter here the maximum permitted quantity to buy this product. 0=no limit' mod='opartlimitquantity'}">
               {l s='Max quantity' mod='opartlimitquantity'}
            </span>
        </label>
        <input type="text" name="opartMaxQuantity" class="fixed-width-sm" value="{$product->opart_max_qty|escape:'html':'UTF-8'}">
      </div>
      <div class="col-lg-9 col-lg-offset-3">
        <label class="control-label col-lg-3" for="reference">
            <span class="label-tooltip" data-toggle="tooltip" title=""
                  data-original-title=" {l s='Enter here the minimum needed quantity to buy this product. 0=no limit' mod='opartlimitquantity'}">
               {l s='Min quantity' mod='opartlimitquantity'}
            </span>
        </label>
        <input type="text" name="opartMinQuantity" class="fixed-width-sm" value="{$product->opart_min_qty|escape:'html':'UTF-8'}">
      </div>
      <div style="clear:both"></div>
    </div>

    <div class="batches">
      <h3 class="tab">
        <span class="label-tooltip" data-toggle="tooltip" title=""
              data-original-title="{l s='Choose a batch type then indicate the corresponding value.' mod='opartlimitquantity'}
{l s='If you choose the "multiple of" type and indicate 3 as the value, your customers will then be able to order this product in quantities of 3, 6, 9, etc.' mod='opartlimitquantity'}
{l s='If you choose the "fixed quantity" type and indicate as value 3, your customers will then only be able to order 3 quantities of this product.' mod='opartlimitquantity'}
{l s='You can add as many sets as you want to create different combinations' mod='opartlimitquantity'}">
        {l s='Batch management' mod='opartlimitquantity'} <i class="icon process-icon-help"></i>
        </span>
      </h3>
      <div class="batch_list">
        {if count($batches[0])}
          {foreach from=$batches[0] item='batch'}
          <div class="form-group">
            <div class="col-lg-3 col-lg-offset-3">
              <div style="display: flex; justify-content: flex-end;">
                <select name="batches[0][type][]" id="" class="form-control" style="width: 200px;">
                  <option value="multiple"{if $batch.batch_type == 'multiple'} selected="selected"{/if}>{l s='Multiple of' mod='opartlimitquantity'}</option>
                  <option value="fixed"{if $batch.batch_type == 'fixed'} selected="selected"{/if}>{l s='Fixed quantity' mod='opartlimitquantity'}</option>
                </select>
              </div>
            </div>
            <div class="col-lg-6 col-sm" style="display: flex;">
              <input type="text" name="batches[0][quantity][]" class="fixed-width-sm form-control" value="{$batch.quantity|intval}">
              <a href="" class="removeBatch"><i class="icon-trash"></i></a>
            </div>
          </div>
          {/foreach}
        {/if}
      </div>
      <div class="batchAction">
        <a href="" class="addBatch" data-id="0"><i class="icon process-icon-new" style="font-size: 20px;"></i>&nbsp;{l s='add batch' mod='opartlimitquantity'}</a>
      </div>
    </div>
  </div>

  {if is_array($declinations) && count($declinations)}
    <div id="opartdeclinationqty" {if is_numeric($product->opart_max_qty) && is_numeric($product->opart_max_qty)}style="display: none;"{/if}>
      {foreach from=$declinations item='declination'}
        <div class="form-group">
          <h3 class="tab">{l s='LIMIT QUANTITY' mod='opartlimitquantity'} pour la déclinaison {$declination.reference|escape:'html':'UTF-8'}
            : {$declination.attribute_designation|escape:'html':'UTF-8'}</h3>
          <div class="col-lg-9 col-lg-offset-3">
            <label class="control-label col-lg-3" for="opartMaxQuantity_attr_{$declination.id_product_attribute|escape:'html':'UTF-8'}">
              <span class="label-tooltip" data-toggle="tooltip" title=""
          data-original-title=" {l s='Enter here the maximum permitted quantity to buy this product. 0=no limit' mod='opartlimitquantity'}">
                   {l s='Max quantity' mod='opartlimitquantity'}
              </span>
            </label>
            <input type="text" id="opartMaxQuantity_attr_{$declination.id_product_attribute|escape:'html':'UTF-8'}" name="opartMaxQuantity_attr[{$declination.id_product_attribute|escape:'html':'UTF-8'}]" class="fixed-width-sm" value="{$declination.opart_max_qty|intval}">
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <label class="control-label col-lg-3" for="opartMinQuantity_attr_{$declination.id_product_attribute|escape:'html':'UTF-8'}">
              <span class="label-tooltip" data-toggle="tooltip" title=""
          data-original-title=" {l s='Enter here the minimum needed quantity to buy this product. 0=no limit' mod='opartlimitquantity'}">
                   {l s='Min quantity' mod='opartlimitquantity'}
              </span>
            </label>
            <input type="text" id="opartMinQuantity_attr_{$declination.id_product_attribute|escape:'html':'UTF-8'}" name="opartMinQuantity_attr[{$declination.id_product_attribute|escape:'html':'UTF-8'}]" class="fixed-width-sm" value="{$declination.opart_min_qty|intval}">
          </div>
        </div>

        <div class="batches">
          <h4 class="tab">
            <span class="label-tooltip" data-toggle="tooltip" title=""
                  data-original-title="{l s='Choose a batch type then indicate the corresponding value.' mod='opartlimitquantity'}
{l s='If you choose the "multiple of" type and indicate 3 as the value, your customers will then be able to order this product in quantities of 3, 6, 9, etc.' mod='opartlimitquantity'}
{l s='If you choose the "fixed quantity" type and indicate as value 3, your customers will then only be able to order 3 quantities of this product.' mod='opartlimitquantity'}
{l s='You can add as many sets as you want to create different combinations' mod='opartlimitquantity'}">
              {l s='Batch management' mod='opartlimitquantity'} {l s='for declination' mod='opartlimitquantity'} {$declination.reference|escape:'html':'UTF-8'}
            : {$declination.attribute_designation|escape:'html':'UTF-8'}
            </span>
          </h4>
          <div class="batch_list">
            {if isset($batches[$declination.id_product_attribute]) && count($batches[$declination.id_product_attribute])}
              {foreach from=$batches[$declination.id_product_attribute] item='batch'}
                <div class="form-group">
                  <div class="col-lg-3 col-lg-offset-3">
                    <div style="display: flex; justify-content: flex-end;">
                      <select name="batches[{$declination.id_product_attribute|intval}][type][]" id="" class="form-control" style="width: 200px;">
                        <option value="multiple"{if $batch.batch_type == 'multiple'} selected="selected"{/if}>{l s='Multiple of' mod='opartlimitquantity'}</option>
                        <option value="fixed"{if $batch.batch_type == 'fixed'} selected="selected"{/if}>{l s='Fixed quantity' mod='opartlimitquantity'}</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-6 col-sm" style="display: flex;">
                    <input type="text" name="batches[{$declination.id_product_attribute|intval}][quantity][]" class="fixed-width-sm form-control" value="{$batch.quantity|intval}">
                    <a href="" class="removeBatch"><i class="icon-trash"></i></a>
                  </div>
                </div>
              {/foreach}
            {/if}
          </div>
          <div class="batchAction">
            <a href="" class="addBatch" data-id="{$declination.id_product_attribute|intval}"><i class="material-icons">add_circle</i> {l s='add batch' mod='opartlimitquantity'}</a>
          </div>
        </div>
        <hr><br><br>
      {/foreach}
    </div>
  {/if}

  {if $hideBottomSaveButton!=true}
    <div class="panel-footer">
      <a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}" class="btn btn-default"><i
          class="process-icon-cancel"></i> {l s='Cancel' mod='opartlimitquantity'}</a>
      <button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i
          class="process-icon-save"></i> {l s='Save' mod='opartlimitquantity'}</button>
      <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i
          class="process-icon-save"></i> {l s='Save and stay' mod='opartlimitquantity'}</button>
    </div>
  {/if}
{/if}
