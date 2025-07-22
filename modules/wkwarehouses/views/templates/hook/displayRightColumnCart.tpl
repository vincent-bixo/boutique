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
<div id="delivery-address-cart" style="display:{if $delivery_address}block{else}none{/if}">
	<div class="card-block">
        <strong>{l s='Your delivery address' mod='wkwarehouses'} <i class="material-icons">chevron_right</i></strong><br />
        <span>{$delivery_address nofilter}{* HTML CONTENT *}</span>
    </div>
</div>
