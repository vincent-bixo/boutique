{*
* 2013-2024 2N Technologies
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@2n-tech.com so we can send you a copy immediately.
*
* @author    2N Technologies <contact@2n-tech.com>
* @copyright 2013-2024 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="panel-heading">
    <i class="fas fa-question-circle"></i>
    &nbsp;{l s='FAQ' mod='ntstats'}
</div>
<div class="panel-group" id="accordion-faq">
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-2">
                {l s='Are the prices with or without taxes?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-2" class="collapse">
            <div class="panel-body">
                {l s='The prices are with taxes included' mod='ntstats'}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-3">
                {l s='How to open exported CSV files?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-3" class="collapse">
            <div class="panel-body">
                {l s='CSV files can be opened with any kind of spreadsheet programs' mod='ntstats'} (Office Excel, LibreOffice...)
                <br/>
                {l s='CSV files are in UTF-8 and values are separated by semicolons' mod='ntstats'} ( ; )
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-4">
                {l s='Weird characters appear when openning CSV file?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-4" class="collapse">
            <div class="panel-body">
                {l s='The file is encoded in UTF8 (International unicode)' mod='ntstats'}
                <br/>
                {l s='If you use Excel, open an new worksheet, go to the Data tab et click on "From Text". Once you have chosen your file, in File origin choose Unicode (UTF-8) (at the bottom of the list).' mod='ntstats'}
                <br/>
                {l s='If you use LibreOffice, when you open the file, it will directly ask the information. In Character Set, choose Unicode (UTF-8)' mod='ntstats'}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-5">
                {l s='What does the Need column mean?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-5" class="collapse">
            <div class="panel-body">
                {l s='This is a very simple hint for controlling stocks based on sales. It corresponds to the formula: (current quantity - quantity sold) * -1.' mod='ntstats'}
                <br/>
                {l s='A positive number indicates the quantity that you need to buy in order to be able to sell the same quantity in the same period, you do not have enough stock for that product.' mod='ntstats'}
                <br/>
                {l s='A negative number indicates the quantity that you should get rid of (via discounts for example) because you have too much stock for this product if you sell the same quantity over the same period.' mod='ntstats'}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-6">
                {l s='How to update the module?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-6" class="collapse">
            <div class="panel-body">
                {l s='You need to download the last version of the module from whre you purchased it.' mod='ntstats'}
                <br/>
                {l s='Then in the backoffice of your shop, in Modules > Modules Manager, click on the Install module button and choose the module zip file.' mod='ntstats'}
                <br/>
                {l s='Prestashop will detect your current version and update automatically your module.' mod='ntstats'}
                <br/>
                {l s='There is no need to uninstall the module first.' mod='ntstats'}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-7">
                {l s='What does the "Stock duration" column mean?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-7" class="collapse">
            <div class="panel-body">
                {l s='The "Stock duration" column indicate how many days of stock you have, based on the quantity of products sold during the chosen period.' mod='ntstats'}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-8">
                {l s='Why is the number display in the module different than the number of orders in the Prestashop Orders tab for the same day?' mod='ntstats'}
            </a>
        </div>
        <div id="faq-8" class="collapse">
            <div class="panel-body">
                {l s='By default, the module display orders depending of their invoice dates, not their creation dates.' mod='ntstats'}
                <br/>
                {l s='Also, the orders status must be valid.' mod='ntstats'}
                <br/>
                {l s='You can choose, in the module configuration, to display orders depending on their creation dates instead of invoice dates.' mod='ntstats'}
            </div>
        </div>
    </div>
</div>