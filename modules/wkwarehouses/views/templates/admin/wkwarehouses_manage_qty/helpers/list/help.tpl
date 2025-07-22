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
<div class="height-transition height-transition-hidden container-header" id="help-header">
    <div class="panel">
        <div class="panel-heading"><i class="icon-info-circle"></i> {l s='What does this section do?' mod='wkwarehouses'}</div>
        <div class="row col-lg-12">
            <img src="{$this_path|escape:'html':'UTF-8'}/views/img/warehouses.png" class="pull-left"/>
            <ul class="col-lg-10 pull-left">
                <li>{l s='From this interface, you can manage [1]quickly and instantly[/1] for each product/combination' tags=['<strong>'] mod='wkwarehouses'}:</li>
                <li class="help-subtitle">{l s='Warehouses associations and locations' mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='Click on the following button' mod='wkwarehouses'} <a class="button btn btn-default btn-xs"><i class="icon-home"></i></a> {l s='in front of a product/combination in list to [1]manage their warehouses associations and locations[/1]' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='Click on the following button' mod='wkwarehouses'} <a class="button btn btn-danger btn-xs"><i class="icon-home"></i></a> {l s='to manage [1]all warehouses associations and locations for a product including all its combinations[/1]' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="help-subtitle">{l s='The available quantities in your stock' mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='You can [1]manage and inventory directly your stock[/1] from this interface. Don\'t spend hours browsing between each product to manage its stock' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='If you use [1]the advanced stock management system[/1] for a product, the available quantities are based on the stock in your warehouses' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='Click on the following button' mod='wkwarehouses'} <a class="button btn btn-default btn-xs"><i class="icon-archive"></i></a> {l s='in front of each product/combination using the advanced stock management system to manage [1]physical stock[/1] for each warehouse' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="help-subtitle">{l s='Bulk actions' mod='wkwarehouses'}:</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='You can [1]switch products[/1] in bulk to the advanced stock management system' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='You can [1]switch off[/1] in bulk the using of advanced stock management system from products' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='If there is [1]gap[/1] between Prestashop physical quantity and warehouses quantity, our module let you fixing this gap in bulk' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-minus small"></i> {l s='Fix if there is a gap between the warehouses reserved quantity and Prestashop reserved quantity for a selection of products' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><div class="alert alert-warning">{l s='Note: if you switch a product to be handled by the advanced stock management system and you don\'t assign it to any warehouse, [1]it will not be available for sale[/1]' tags=['<strong>'] mod='wkwarehouses'}.</div></li>
                <li>{l s='Use the filters from the panel below to' mod='wkwarehouses'}:</li>
                <li class="no-style"><i class="icon-search"></i> {l s='list products [1]by warehouses or/and suppliers[/1]' tags=['<strong>'] mod='wkwarehouses'}.</li>
                <li class="no-style"><i class="icon-search"></i> {l s='look for products/combinations whose [1]sum of warehouses quantities does not match the physical quantity[/1]' tags=['<strong>'] mod='wkwarehouses'} <a class="button btn btn-danger btn-xs">{l s='lines with red background' mod='wkwarehouses'}</a>. {l s='Thus, it will be easy to correct quantities gaps' mod='wkwarehouses'}.</li>
            </ul>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
