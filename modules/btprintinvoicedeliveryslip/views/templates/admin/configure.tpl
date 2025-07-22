{*
* Module My Addons
* 
*  @author    My Addons - support@myaddons.io
*  @uses Prestashop modules
*  @since 1.0
*  @copyright Copyright &copy; 2017, My Addons
*  @license   My Addons
*}

<!-- Module content -->
<div id="modulecontent" class="clearfix">
    <!-- Nav tabs -->
    <div class="col-lg-2">
        <div class="list-group">
            <a href="#config" class="list-group-item active" data-toggle="tab">
                <i class="icon-briefcase"></i> {l s='Configuration' mod='btprintinvoicedeliveryslip'}
            </a>
            <a href="#support" class="list-group-item" data-toggle="tab"><i class="icon-envelope"></i> {l s='Support' mod='btprintinvoicedeliveryslip'}</a>
        </div>
        <div class="list-group">
            <a href="#" class="list-group-item" data-toggle="tab"><i class="icon-info-sign"></i> {l s='Version' mod='btprintinvoicedeliveryslip'} {$version|escape:'htmlall':'UTF-8'}</a>
        </div>
        <div style="text-align:center; margin-right: 5px;">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-my_addons-1.png" alt="My Addons" height="70" />
        </div>
    </div>
    <!-- Tab panes -->
    <div class="tab-content col-lg-10">
            <div class="tab-pane active panel" id="config">
                {include file="./tabs/configuration.tpl"}
            </div>
            <div class="tab-pane panel" id="support">
                {include file="./tabs/support.tpl"}
            </div>
    </div>
</div>
                