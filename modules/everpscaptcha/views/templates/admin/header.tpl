{*
* Project : everpscaptcha
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

<div class="panel row">
    <h3><i class="icon icon-smile"></i> {l s='Ever Câptcha' mod='everpscaptcha'}</h3>
    <div class="col-md-6">
        <img id="everlogo" src="{$everpscaptcha_dir|escape:'htmlall':'UTF-8'}/logo.png" style="max-width: 120px;">
        <p>
            <strong>{l s='Please set module\'s configuration' mod='everpscaptcha'}</strong><br />
            {l s='Thanks for using Team Ever\'s modules' mod='everpscaptcha'}.<br />
        </p>
        <p>{l s='This module insert a Google ReCAPTCHA on the Prestashop contact form. In order to use this module, you\'ll need a secured key pair.' mod='everpscaptcha'}</p>
        <p>
            {l s='To generate your secured key pair, follow this' mod='everpscaptcha'}
            <a href="https://www.google.com/recaptcha/admin" target="_blank">
                {l s='Google ReCAPTCHA link' mod='everpscaptcha'}
            </a>
        </p>
        <h4>{l s='How to be first on Google pages ?' mod='everpscaptcha'}</h4>
        <p>{l s='We have created the best SEO module, by working with huge websites and SEO societies' mod='everpscaptcha'}</p>
        <p>
            <a href="https://addons.prestashop.com/fr/seo-referencement-naturel/39489-ever-ultimate-seo.html" target="_blank">{l s='See the best SEO module on Prestashop Addons' mod='everpscaptcha'}</a>
        </p>
    </div>
    <div class="col-md-6">
        <p class="alert alert-warning">
            {l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' mod='everpsquotation'}
        </p>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick" />
        <input type="hidden" name="hosted_button_id" value="3LE8ABFYJKP98" />
        <input type="image" src="https://www.team-ever.com/wp-content/uploads/2019/06/appel_a_dons-1.jpg" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Bouton Faites un don avec PayPal" />
        <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
        </form>
    </div>
</div>