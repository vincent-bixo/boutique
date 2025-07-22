<?php
/**
*  NOTICE OF LICENSE
* 
*  Module for Prestashop
*  100% Swiss development
* 
*  @author    Webbax <contact@webbax.ch>
*  @copyright -
*  @license   -
*/

/*
 V1.7.20 - 19.06.17
 - correction prix d'achat (export des produits)
 V1.7.19 - 13.12.16
 - correction $base_url_ajax (HTTPS)
 - correction warning rate (produits / déclinaisons)
 V1.7.18 - 06.10.16
 - correction sur TVA % exportation des produits
 V1.7.17 - 08.09.16
 - ajout contrôle validateFields / détail commandes
 V1.7.16 - 22.06.16
 - ajout champ UPC (export produits)
 - contrôle supplémentaire $Language->validateFields
 V1.7.15 - 22.03.16
 - ajout langue (export client)
 - optimisation icône module
 V1.7.14 - 18.03.16
 - optimisation sur la déformation de colonnes (point virgule)
 V1.7.13 - 02.03.16 
 - ajout de la référence de la commande
 - correction requête export détail commandes (déclinaisons)
 - correction sur les traductions manquantes
 - correction token "move_up" & "move_down" 
 V1.7.12 - 26.02.16 
 - correction détection HTTPS requêtes Ajax
 V1.7.11 - 15.12.15 
 - optimisation pour Prestashop Addons
 V1.7.10 - 03.12.15
 - échappement alerte @$address (customer.php)
 V1.7.9 - 24.11.15
 - retrait warning ob_end_clean / ob_clean
 V1.7.8 - 20.11.15
 - ajout champs id_customer + CA ht/ttc (customer.php)
 - correction requête date d'ajout produit
 V1.7.7 - 17.11.15
 - optimisation export order / order_detail ($Customer->validateFields)
 V1.7.6 - 30.10.15
 - retrait header dans ajax.php (évite les mauvaises redirections)
 V1.7.5 - 26.10.15
 - correction requête export produits actifs
 V1.7.4 - 09.07.15
  - contrôle validateFields "Address" sur order & order_detail
 V1.7.3 - 03.07.15
 - contrôle de l'accès à la boutique (pour appel Ajax)
 V1.7.2 - 30.06.15
 - ajout option encodage
 - correction getHttpHost "product"
 - contrôle validateFields "product"
 V1.7.1 - 26.06.15
 - contrôle validateFields "customer"
 V1.7.0 - 17.06.15
 - ajout nouveaux champs (order_detail)
 + total_discounts
 + reduction_amount_tax_incl 
 + reduction_amount_tax_excl
 - retrait "update.php"
 V1.6.9 - 09.06.15
 - ajout "date_livraison" dans "order_detail"
 V1.6.8 - 02.06.15
 - correction customer.php "civilités"
 V1.6.7 - 12.05.15
 - ajout méthode getCurrency (limite les erreurs fatales)
 V1.6.6 - 13.03.15
 - correction de l'url d'export du fichier
 V1.6.5 - 06.02.15
 - correction prix de vente avec réduction (order_detail.php)
 V1.6.4 - 16.01.15
 - correction adresse client (customer.php)
 V1.6.3 - 12.01.15
 - correction inversion label (nom/prénom)
 V1.6.2 - 05.12.14
 - correction sur l'export du prix d'achat (product.php)
 V1.6.1 - 01.12.14
 - ajout "Access-Control-Allow-Origin"
 - modification automatique nom du fichier à télécharger
 V1.6.0 - 25.08.14 
 - compatibilité Prestashop 1.6
 - correction stockage catégories cochées
 */

class Customexporter extends Module{

    private $_html = '';
    private $_postErrors = array();

    private $line_header = '';
    private $file = array();
    private $separator = '';
    private $format = '';
    
    private $cats_checked;
    
    public function __construct(){
        
        $this->name = 'customexporter';
        $this->tab = 'administration';
        $this->author = 'Webbax';
        $this->version = '1.7.20';
        $this->module_key = 'c2171e0f04074c40476f3f4faf1c5dcb';
        
        /* PS 1.6 */
        $this->bootstrap = true;
        $this->ps_version  = Tools::substr(_PS_VERSION_,0,3);
        
        parent::__construct();
        $this->displayName = $this->l('Custom Exporter');
        $this->description = $this->l('Exporter les informations de votre boutique');
        $this->confirmUninstall = $this->l('Etes-vous sûr de vouloir supprimer le module ?');
    }

    /*
     * Installe le module
     * @param   -
     * @return  -
    */
    public function install(){
        $this->init_db();
        Configuration::updateValue('CUSTOMEXPORTER_CHAR_UTF8',1);
        Configuration::updateValue('CUSTOMEXPORTER_CHAR_SPEC',1);
        if(!parent::install())
            return false;
        return true;
    }

    /*
     * Désinstalle le module
     * @param   -
     * @return  -
    */
    public function uninstall(){
        if(!parent::uninstall())
            return false;
        Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'customexporter`');
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "%CUSTOMEXPORTER%"');
        return true;
    }

    /*
     * Valide le formulaire
     * @param   -
     * @return  -
    */
    private function _postValidation(){}


    /*
     * Exporte les articles
     * @param   -
     * @return  -
    */
    private function _postProcess(){
        
        @ini_set('display_errors','on');
        $ShopUrl = new ShopUrl($this->context->shop->id);
        // var $_POST
        $export_type = Tools::getValue('export_type');
        @$fields_no = Tools::getValue('fields_no');
        $line_header = Tools::getValue('line_header');
        $this->format = Tools::getValue('format');
        $this->separator = Tools::getValue('separator');
   
        // garde checké la sélection pour la prochaine fois
        // remet à 0 tout les champs
        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customexporter` 
                                    SET `checked`="" 
                                    WHERE `export_type`="'.pSQL($export_type).'" 
                                    AND `id_shop`="'.pSQL($this->context->shop->id).'"');
        if(is_array($fields_no)){
            foreach($fields_no as $field_no){
                // check les champs sélectionnés
                Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customexporter` 
                                            SET `checked` = "1" 
                                            WHERE `field_no`="'.pSQL($field_no).'"
                                            AND `export_type`="'.pSQL($export_type).'"
                                            AND `id_shop`="'.pSQL($this->context->shop->id).'"');
            }
        }
        // crée une ligne de header si coché
        if($line_header){
            $fields_header = array();
            if(is_array($fields_no)){
                foreach($fields_no as $field_no){
                    $req = Db::getInstance()->ExecuteS('SELECT `field_name`,`place` 
                                                        FROM '._DB_PREFIX_.'customexporter 
                                                        WHERE `field_no`="'.pSQL($field_no).'" 
                                                        AND `export_type`="'.pSQL($export_type).'"
                                                        AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                    $field_name = $req[0]['field_name'];
                    $place = $req[0]['place'];
                    $fields_header[$place] = $field_name;
                }
            }
            ksort($fields_header);
            $this->file[] = $fields_header;
        }

        // traitement selon le type d'export
        switch($export_type){
            case 'order':
                $this->exportOrder();
                break;
            case 'order_detail':
                $this->exportOrderDetail();
                break;
            case 'customer':
                $this->exportCustomer();
                break;
            case 'product':
                $this->exportProduct();
                break;
        }

        // crée le fichier
        $this->create_file($export_type);
        
        if(count($this->file)>1){
            // lien téléchargement
            $filename = $export_type.'_id_shop_'.$this->context->shop->id.'.'.$this->format;
            $message = $this->l('Export effectué').'<br/>
                        <a href="'.$ShopUrl->getURL().'modules/customexporter/downloads/download.php?file='.$filename.'"><img src="../modules/customexporter/views/img/save.png"/>'.$this->l('Télécharger le fichier').'</a>';
            $this->_html .= $this->displayConfirmation($message);
        }else{
            $this->_html .= $this->displayError($this->l('Aucun élément à exporter'));
        }
    }

    /*
     * Affiche le formulaire
     * @param   -
     * @return  -
    */
    private function _displayFormMain(){
           
        if($this->ps_version=='1.6'){
            $this->_html .= '<link rel="stylesheet" type="text/css" href="'.$this->_path.'views/css/styles_1.6.css">';
        }
        
        $this->_html .= '
        
        <style>
            hr{
                color:#CCCCCC;
                background-color:#CCCCCC;
                height: 1px;
                border: 0;
            }
            .sub_title{
                font-size:15px;
                text-align:center;
                color:#fa8700;
            }
        </style>
        
        <div id="customexporter_bo" class="panel">
        <fieldset>
        <legend>'.$this->displayName.'</legend>
        <b>'.$this->l('Ce module vous permet d\'exporter des informations de votre choix.').'</b><br />';
        
        $this->context->controller->addJqueryUI('ui.datepicker');

        $script = '
        <script type="text/javascript" src="'.__PS_BASE_URI__.'modules/customexporter/libs/checkboxtree/jquery.min.js"></script>
        <script type="text/javascript" src="'.__PS_BASE_URI__.'modules/customexporter/libs/checkboxtree/jquery-ui.min.js"></script>
        <script type="text/javascript" src="'.__PS_BASE_URI__.'modules/customexporter/views/js/customexporter.js"></script>
        <link rel="stylesheet" type="text/css" href="'.__PS_BASE_URI__.'modules/customexporter/libs/checkboxtree/jquery.checkboxtree.min.css">
        <script type="text/javascript" src="'.__PS_BASE_URI__.'modules/customexporter/libs/checkboxtree/jquery.checkboxtree.min.js"></script>
            
        <script type="text/javascript" src="'.__PS_BASE_URI__.'modules/customexporter/libs/jsdatepick/jsDatePick.min.1.3.js"></script>
        <link rel="stylesheet" type="text/css" href="'.__PS_BASE_URI__.'modules/customexporter/libs/jsdatepick/jsDatePick_ltr.min.css">

        <script type="text/javascript">
            $(document).ready(function(){
                // tree dynamique
                $(".tree").checkboxTree({
                    collapseImage: "'.__PS_BASE_URI__.'modules/customexporter/libs/checkboxtree/images/minus.png",
                    expandImage: "'.__PS_BASE_URI__.'modules/customexporter/libs/checkboxtree/images/plus.png",
                    collapsed:true
                });     
                // cocher - décocher
                $(".notchText").click(function() { // clic sur la case cocher/decocher
                    var cases = $(".tree").find(":checkbox"); // on cherche les checkbox
                    if(this.checked){ // si "notchText" est coché
                        cases.attr("checked", true); // on coche les cases
                        $(".notchText").html("'.$this->l('Tout décocher').'"); // mise à jour du texte de notchText
                    }else{ // si on décoche "notchText"
                        cases.attr("checked", false);// on coche les cases
                        $(".notchText").html("'.$this->l('Cocher tout').'");// mise à jour du texte de notchText
                    }
                });
                // jsdatepick
                new JsDatePick({useMode:2,target:"date_start_order",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_end_order",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_start_order_detail",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_end_order_detail",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_start_customer",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_end_customer",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_start_customer_ca",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_end_customer_ca",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_start_product",dateFormat:"%Y-%m-%d"});
                new JsDatePick({useMode:2,target:"date_end_product",dateFormat:"%Y-%m-%d"});
           });
        </script>';
        
        $this->_html.= $script;
        
        // current shop
        $Shop = new Shop($this->context->shop->id);
        $ssl = Configuration::get('PS_SSL_ENABLED');
        $base_url_ajax = $Shop->getBaseURL();
        if($ssl && Tools::strpos('https',$base_url_ajax)===false){
            $base_url_ajax = str_replace('http','https',$base_url_ajax);
        }

        // Liste les languages
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages();
        $form_languages = '';
        foreach($languages as $language){
            if($id_lang_default==$language['id_lang']){$checked='checked';}else{$checked='';}
            $form_languages .= ' <img src="../img/l/'.$language['id_lang'].'.jpg"/> <input type="radio" name="languages[]" value="'.$language['id_lang'].'" '.$checked.' />';
        }

        // Crée l'arbre des catégories
        $this->cats_checked = unserialize(Configuration::get('CUSTOMEXPORTER_CATS'));
        if(empty($this->cats_checked)){$this->cats_checked=array();}

        $depth = 0;
        $categTree = Category::getRootCategory()->recurseLiteCategTree($depth);
        $ulTree = '<br/><input type="checkbox" class="notchText"/> <i><span class="notchText">'.$this->l('Cocher tout').'</span></i><br/><br/>';
        $ulTree .= '<div class="tree-top">' . $categTree['name'] . '</div>'."\n";
        $ulTree .=  '<ul class="tree">'."\n";
        foreach ($categTree['children'] AS $child)
                $ulTree .= $this->constructTreeNode($child);
        $ulTree .=  '</ul>'."\n";

        // Tous
        // sélection type d'export
        $form_lst_tables = '<select id="export_type" name="export_type">';
        $form_lst_tables .= '<option value="" selected>'.$this->l('Effectuez une sélection');
        $form_lst_tables .= '<option value="order">'.$this->l('Commandes');
        $form_lst_tables .= '<option value="order_detail">'.$this->l('Commandes (détail / contenu)');
        $form_lst_tables .= '<option value="customer">'.$this->l('Clients');
        $form_lst_tables .= '<option value="product">'.$this->l('Produits');
        $form_lst_tables .= '</select>';
        // entête
        $form_line_header = '<select name="line_header">';
        $form_line_header .= '<option value="1" selected>'.$this->l('Oui');
        $form_line_header .= '<option value="0">'.$this->l('Non');
        $form_line_header .= '</select>';
        // format de l'export
        $form_format = '<select name="format">';
        $form_format .= '<option value="csv" selected>CSV';
        $form_format .= '<option value="txt">TXT';
        $form_format .= '</select>';
        // séparateur
        $form_separator = '<input type="text" value=";" name="separator" />';
        // statut commande
        $form_order_state = '
        <select name="id_order_state" style="width:293px">';
            $form_order_state.='<option value="" selected="selected" />'.$this->l('Tous');
            $states = OrderState::getOrderStates($this->context->cookie->id_lang);
            foreach($states as $s){
                $form_order_state.='<option value="'.$s['id_order_state'].'" />'.$s['name'];
            }
            $form_order_state.='
        </select>';

        // Export commande
        // type d'adresse à exporter
        $form_address_choice = '<select id="address_choice" name="address_choice">';
        $form_address_choice .= '<option value="delivery" selected>'.$this->l('Adresse de livraison');
        $form_address_choice .= '<option value="billing">'.$this->l('Adresse de facturation');
        $form_address_choice .= '</select>';
        // Export des produits
        // déclinaisons
        $form_combination = '<select name="export_combination">';
        $form_combination .= '<option value="1">'.$this->l('Oui');
        $form_combination .= '<option value="0" selected>'.$this->l('Non');
        $form_combination .= '</select>';
        // actif
        $form_p_active  = '<select name="p_active">';
        $form_p_active .= '<option value="1" selected>'.$this->l('Oui');
        $form_p_active .= '<option value="0">'.$this->l('Non');
        $form_p_active .= '</select>
        <br>';
        
        $this->_html .= 
        '<br/>'.$this->l('Type d\'export').' : '. 
        $form_lst_tables.'
        <div id="order" class="export_form" style="display:none;">
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <input type="hidden" name="base_url_ajax" id="base_url_ajax" value="'.$base_url_ajax.'"/>
            <input type="hidden" name="id_shop" id="id_shop" value="'.$this->context->shop->id.'"/>
            <input type="hidden" name="export_type" value="order"/>
            <input type="hidden" name="token_module" id="token_module" value="'._COOKIE_IV_.'"/>
            <table>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><th colspan="2"><div class="sub_title"><img src="'._MODULE_DIR_.'/customexporter/views/img/cart.png" /> '.$this->l('Export des commandes').'</div></th></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Préférence de l\'adresse').'</td><td>'.$form_address_choice.'</td></tr>
                <tr>
                    <td>'.$this->l('Date entre').'</td>
                    <td>
                       <input type="text" value="'.date("Y").'-01-01" name="date_start" id="date_start_order" class="date" /> '.
                       $this->l('à').'&nbsp; 
                       <input type="text" value="'.date('Y-m-d').'" name="date_end" id="date_end_order" class="date" />
                    </td>
                </tr>
                <tr><td>'.$this->l('Satut courant').'</td><td>'.$form_order_state.'</td></tr>   
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Avec colonnes d\'entêtes').'</td><td>'.$form_line_header.'</td></tr>
                <tr><td>'.$this->l('Format du fichier').'</td><td>'.$form_format.'</td></tr>
                <tr><td>'.$this->l('Séparateur (entre les champs)').'</td><td>'.$form_separator.'</td></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>
                    <div id="loading_order"><img src="../modules/customexporter/views/img/loading.gif"/></div>
                    <div id="fields_order"></div>
                </td><td>
                </td></tr>
            </table>
            <hr/>
            <input class="button btn btn-default" name="btnSubmit" value="'.$this->l('Exporter').'" type="submit" />
            </form>
        </div>

        <div id="order_detail" class="export_form" style="display:none;">
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <input type="hidden" name="base_url_ajax" id="base_url_ajax" value="'.$base_url_ajax.'"/>
            <input type="hidden" name="id_shop" id="id_shop" value="'.$this->context->shop->id.'"/>
            <input type="hidden" name="export_type" value="order_detail"/>
            <input type="hidden" name="token_module" id="token_module" value="'._COOKIE_IV_.'"/>
            <table>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><th colspan="2"><div class="sub_title"><img src="'._MODULE_DIR_.'/customexporter/views/img/cart.png" /> '.$this->l('Export des commandes (par ligne de détail)').'</div></th></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Préférence de l\'adresse').'</td><td>'.$form_address_choice.'</td></tr>
                <tr>
                    <td>'.$this->l('Date entre').'</td>                    
                    <td>
                       <input type="text" value="'.date("Y").'-01-01" name="date_start" id="date_start_order_detail" class="date" /> '.
                       $this->l('à').'&nbsp; 
                       <input type="text" value="'.date('Y-m-d').'" name="date_end" id="date_end_order_detail" class="date" />
                    </td>
                </tr>
                <tr><td>'.$this->l('Satut courant').'</td><td>'.$form_order_state.'</td></tr>   
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Avec colonnes d\'entêtes').'</td><td>'.$form_line_header.'</td></tr>
                <tr><td>'.$this->l('Format du fichier').'</td><td>'.$form_format.'</td></tr>
                <tr><td>'.$this->l('Séparateur (entre les champs)').'</td><td>'.$form_separator.'</td></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>
                    <div id="loading_order_detail"><img src="../modules/customexporter/views/img/loading.gif"/></div>
                    <div id="fields_order_detail"></div>
                </td><td>
                </td></tr>
                <tr><td colspan="2"><hr/></td></tr>
            </table>
            <input class="button btn btn-default" name="btnSubmit" value="'.$this->l('Exporter').'" type="submit" />
            </form>
        </div>

        <div id="customer" class="export_form" style="display:none;">
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <input type="hidden" name="base_url_ajax" id="base_url_ajax" value="'.$base_url_ajax.'"/>
            <input type="hidden" name="id_shop" id="id_shop" value="'.$this->context->shop->id.'"/>
            <input type="hidden" name="export_type" value="customer"/>
            <input type="hidden" name="token_module" id="token_module" value="'._COOKIE_IV_.'"/>
            <table>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><th colspan="2"><div class="sub_title"><img src="'._MODULE_DIR_.'/customexporter/views/img/user.png" /> '.$this->l('Export des clients').'</div></th></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr>
                    <td>'.$this->l('Date création').'</td>
                    <td>
                       <input type="text" value="'.date("Y").'-01-01" name="date_start" id="date_start_customer" class="date" /> '.
                       $this->l('à').'&nbsp; 
                       <input type="text" value="'.date('Y-m-d').'" name="date_end" id="date_end_customer" class="date" />
                    </td>
                </tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr>
                    <td>'.$this->l('Période chiffre d\'affaire').'</td>
                    <td>
                       <input type="text" value="'.date("Y").'-01-01" name="date_start_customer_ca" id="date_start_customer_ca" class="date" /> '.
                       $this->l('à').'&nbsp; 
                       <input type="text" value="'.date('Y-m-d').'" name="date_end_customer_ca" id="date_end_customer_ca" class="date" />
                    </td>
                </tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Avec colonnes d\'entêtes').'</td><td>'.$form_line_header.'</td></tr>
                <tr><td>'.$this->l('Format du fichier').'</td><td>'.$form_format.'</td></tr>
                <tr><td>'.$this->l('Séparateur (entre les champs)').'&nbsp;</td><td>'.$form_separator.'</td></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>
                    <div id="loading_customer"><img src="../modules/customexporter/views/img/loading.gif"/></div>
                    <div id="fields_customer"></div>
                </td><td>
                </td></tr>
            </table>
            <hr/>
            <input class="button btn btn-default" name="btnSubmit" value="'.$this->l('Exporter').'" type="submit" />
            </form>
        </div>

        <div id="product" class="export_form" style="display:none;">
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <input type="hidden" name="base_url_ajax" id="base_url_ajax" value="'.$base_url_ajax.'"/>
            <input type="hidden" name="id_shop" id="id_shop" value="'.$this->context->shop->id.'"/>
            <input type="hidden" name="export_type" value="product"/>
            <input type="hidden" name="token_module" id="token_module" value="'._COOKIE_IV_.'"/>
            <table>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><th colspan="2"><div class="sub_title"><img src="'._MODULE_DIR_.'/customexporter/views/img/product.png" /> '.$this->l('Export des produits').'</div></th></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Exporter en').'</td><td>'.$form_languages.'</td></tr>
                <tr><td>'.$this->l('Produits actifs').'</td><td>'.$form_p_active.'</td></tr>
                <tr><td>'.$this->l('Moins de').'</td><td><input type="text" name="stock_min" value="9999999999999999"/> '.$this->l('en stock').'</td></tr>
                <tr><td>'.$this->l('Exporter les déclinaisons').'</td><td>'.$form_combination.'</td></tr>
                <tr>
                    <td>'.$this->l('Date création').'</td>
                    <td>
                       <input type="text" value="1900-01-01" name="date_start" id="date_start_product" class="date" /> '.
                       $this->l('à').'&nbsp; 
                       <input type="text" value="'.date('Y-m-d').'" name="date_end" id="date_end_product" class="date" />
                    </td>
                </tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>'.$this->l('Avec colonnes d\'entêtes').'</td><td>'.$form_line_header.'</td></tr>
                <tr><td>'.$this->l('Format du fichier').'</td><td>'.$form_format.'</td></tr>
                <tr><td>'.$this->l('Séparateur (entre les champs)').'</td><td>'.$form_separator.'</td></tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr>
                    <td colspan="2">
                    <span id="title_categories">'.$this->l('Catégories').'</span>
                    <br/>
                    '.$ulTree.'
                    </td>
                </tr>
                <tr><td colspan="2"><hr/></td></tr>
                <tr><td>
                    <div id="loading_product"><img src="../modules/customexporter/views/img/loading.gif"/></div>
                    <div id="fields_product"></div>
                </td><td>
                </td></tr>
                <tr><td colspan="2"><hr/></td></tr>
            </table>
            <input class="button btn btn-default" name="btnSubmit" value="'.$this->l('Exporter').'" type="submit" />
            </form>
        </div> 
        
        </fieldset>
        </div>
        ';
    }
    
    /*
     * Affiche le formulaire de configuration
     * @param   -
     * @return  -
     */
    public function _displayFormConf(){
        
        if(Tools::isSubmit('btnSubmitEncode')){
            Configuration::updateValue('CUSTOMEXPORTER_CHAR_UTF8',Tools::getValue('char_utf8'));
            Configuration::updateValue('CUSTOMEXPORTER_CHAR_SPEC',Tools::getValue('char_spec'));
            Configuration::updateValue('CUSTOMEXPORTER_CHAR_UTF8_WRITE',Tools::getValue('char_utf8_write'));
            $this->_html .= $this->msgConf($this->l('Modifications sauvées'));
        }
           
        $char_utf8 = Configuration::get('CUSTOMEXPORTER_CHAR_UTF8');
        if($char_utf8){$char_utf8_checked='checked="checked"';}else{$char_utf8_checked='';}
        $char_spec = Configuration::get('CUSTOMEXPORTER_CHAR_SPEC');
        if($char_spec){$char_spec_checked='checked="checked"';}else{$char_spec_checked='';}
        $char_utf8_write = Configuration::get('CUSTOMEXPORTER_CHAR_UTF8_WRITE');
        if($char_utf8_write){$char_utf8_write_checked='checked="checked"';}else{$char_utf8_write_checked='';}

        $this->_html.='
        <div class="panel">  
        <fieldset>
        <legend>'.$this->l('Encodage').'</legend>
        '.$this->l('Options pour l\'optimisation de la conversion des caractères.').'<br/><br/>
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <input type="checkbox" value="1" name="char_utf8" '.$char_utf8_checked.'> '.$this->l('Décodage UTF-8').'<br/>
            <input type="checkbox" value="1" name="char_spec" '.$char_spec_checked.'> '.$this->l('Remplacement caractères spéciaux').'<br/>
            <input type="checkbox" value="1" name="char_utf8_write" '.$char_utf8_write_checked.'> '.$this->l('Forcer le décodage UTF-8 avant écriture').'<br/>
            <br/>
            <input type="submit" class="button btn btn-default" name="btnSubmitEncode" value="'.$this->l('Sauver').'" />
        </form>
        </fieldset>
        </div>';
        
    }
    
    /* 
     * Arbre des catégories (export produits)
     */
    private function constructTreeNode($node){
           $ret = '<li>'."\n";
           if(in_array($node['id'],$this->cats_checked)){$checked='checked';}else{$checked='';}
           $ret .= '<input type="checkbox" name="categories[]" value="'.$node['id'].'" '.$checked.' /> '.$node['name']."\n";

           if(!empty($node['children']))
           {
               $ret .= '<ul style="padding-left:20px">'."\n";
               foreach ($node['children'] AS $child)
                       $ret .= $this->constructTreeNode($child);
               $ret .= '</ul>'."\n";
           }
           $ret .= '</li>'."\n";
           return $ret;
       }

    /*
     * Lance l'affichage du module
     * @param   -
     * @return  -
    */
    public function getContent(){
        
        if(Tools::isSubmit('btnSubmit')){
            $this->_postValidation();
            if(!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                $this->_html .= '<div class="alert error">'. $err .'</div>';
        }
        else
            $this->_html .= '';

        $access=true;
        if(Configuration::get('PS_SHOP_ENABLE')==0){
            if(Tools::strpos(Configuration::get('PS_MAINTENANCE_IP'),Tools::getRemoteAddr())===false){$access=false;}
        }
        if($access){   
            $this->_displayFormMain();
            $this->_displayFormConf();
        }else{
            $this->_html.= $this->msgAlert($this->l('Actuellement votre boutique est en maintenance. Veuillez ajoutez votre IP dans la liste des exceptions sous (Préférences -> Maintenance), afin que le module puisse fonctionner correctement.'));
        }
        
        return $this->_html;
    }
    
    /*
     * Exporte les commandes
     * @param -
     * @return -
     */
    private function exportOrder(){
        
        $date_start = Tools::getValue('date_start').' 00:00:00';
        $date_end = Tools::getValue('date_end').' 23:59:59';
        $export_type = Tools::getValue('export_type');
        $fields_no = Tools::getValue('fields_no');
        $address_choice = Tools::getValue('address_choice');

        // parcours les commandes
        $id_order_state = Tools::getValue('id_order_state');
        if(!empty($id_order_state)){
            $AND = 'AND o.`current_state`="'.pSQL($id_order_state).'"';
        }else{
            $AND = '';
        }
        $orders = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'orders o
                                               WHERE `date_add` >= "'.pSQL($date_start).'" 
                                               AND `date_add` <= "'.pSQL($date_end).'"
                                               AND `id_shop`="'.pSQL($this->context->shop->id).'" '.
                                               $AND);
        foreach($orders as $order){

           $Customer = new Customer($order['id_customer']);
           if(Customer::customerIdExistsStatic($order['id_customer']) && $Customer->validateFields(false)){

               $f_no = array();
               $f_no[1] = $order['id_order'];
               $f_no[2] = $order['reference'];
               
               // info client
               $customer = $Customer->getFields();
               if($customer['id_gender']==1){$gender=$this->l('Monsieur','order');}else{$gender=$this->l('Madame','order');}
               // info adresse
               if($address_choice=='delivery'){
                   $id_address = $order['id_address_delivery'];
               }else{
                   $id_address = $order['id_address_invoice'];
               }
               $Address = new Address($id_address);
               if(!empty($id_address) && $Address->validateFields(false)){
                  $address = $Address->getFields(); 
               }
               $f_no[3] = date("d.m.Y", strtotime($order['date_add']));
               $f_no[4] = @$address['company'];
               $f_no[5] = $gender;
               $f_no[6] = @$address['firstname'];
               $f_no[7] = @$address['lastname'];
               $f_no[8] = @$address['address1'];
               $f_no[9] = @$address['address2'];
               $f_no[10] = @$address['postcode'];
               $f_no[11] = @$address['city']; 
               $country = Country::getNameById($order['id_lang'],@$address['id_country']);   
               $f_no[12] = $country; 
               $f_no[13] = @$address['phone'];
               $f_no[14] = @$address['phone_mobile'];
               $f_no[15] = $customer['email'];
               $f_no[16] = $customer['birthday'];
               if($customer['newsletter']){
                   $newsletter=$this->l('Oui','order');
               }else{
                   $newsletter=$this->l('Non','order');
               }
               $f_no[17] = $newsletter;
               $Group = new Group($customer['id_default_group']);
               $group = $Group->name[$order['id_lang']];
               $f_no[18] = $group;
               $f_no[19] = $order['total_discounts'];
               $f_no[20] = $order['total_paid'];
               $f_no[21] = $order['total_paid_real'];
               $f_no[22] = $order['total_shipping'];
               $f_no[23] = $order['total_products'];
               $total_products_wt = '';
               if(isset($order['total_products_wt'])){
                   $total_products_wt = $order['total_products_wt'];
               }
               $f_no[24] = $total_products_wt;
               $f_no[25] = $order['total_wrapping'];
                   $currency = $this->getCurrency($order['id_currency']);
               $f_no[26] = $currency['iso_code'];
               $f_no[27] = $order['payment'];
               $f_no[28] = $order['invoice_number'];
               $f_no[29] = $order['delivery_number'];
               $f_no[30] = $order['gift'];
               $f_no[31] = $order['gift_message'];
               if($order['id_carrier']>0){
               $Carrier = new Carrier($order['id_carrier']);
               $carrier = $Carrier->getFields();
                   $carrier_name = $carrier['name'];
               }else{
                    $carrier_name = '';
               }
               $f_no[32] = $carrier_name;
               $Language = new Language($order['id_lang']);
               if(!$Language->validateFields(false)){
                   $Language = new Language(Configuration::get('PS_LANG_DEFAULT'));
               }
                       
               $language = $Language->getFields();
               $f_no[33] = $language['name'];
               $f_no[34] = $order['delivery_date'];
               $f_no[35] = $order['invoice_date'];
               $f_no[36] = $order['date_upd'];

               $cookie = $this->context->cookie;   
               $Order = new Order($order['id_order']);
               $o_products = $Order->getProductsDetail();
               $cat_list = array();
               foreach($o_products as $p){
                   $Product = new Product($p['product_id'],false,$cookie->id_lang);
                   $Category = new Category($Product->id_category_default,$cookie->id_lang);
                   if(!in_array($Category->name,$cat_list)){$cat_list[]=$Category->name;}
               }
               $cell_cats = '';
               foreach($cat_list as $cat){
                   $cell_cats.=$cat.', ';
               }
               $cell_cats = Tools::substr($cell_cats,0,-2);
               $f_no[37] = $cell_cats;
               $OrderState = new OrderState($order['current_state']);
               $f_no[38] = $OrderState->name[$this->context->cookie->id_lang];

               // Ordonne les cellules selon la base de donnée
               $f_place = array();
               if(is_array($fields_no)){
                   foreach($fields_no as $field_no){
                       $res = Db::getInstance()->ExecuteS('SELECT `place` FROM '._DB_PREFIX_.'customexporter 
                                                           WHERE `field_no`="'.pSQL($field_no).'" 
                                                           AND `export_type`="'.pSQL($export_type).'"
                                                           AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                       $place = $res[0]['place'];
                       $f_place[$place] = $f_no[$field_no];
                   }
               }
               $this->file[] = $f_place; // ajoute nouvelle ligne triée au fichier
           } // end if customer exist + valid
        } // end foreach
        
    }
    
    /*
     * Exporte le détail des commandes
     * @param -
     * @return -
     */
    private function exportOrderDetail(){

        $date_start = Tools::getValue('date_start').' 00:00:00';
        $date_end = Tools::getValue('date_end').' 23:59:59';
        $export_type = Tools::getValue('export_type');
        $fields_no = Tools::getValue('fields_no');
        $address_choice = Tools::getValue('address_choice');

        // parcours le détail des commandes
        $id_order_state = Tools::getValue('id_order_state');
        if(!empty($id_order_state)){
            $AND = 'AND o.`current_state`="'.pSQL($id_order_state).'"';
        }else{
            $AND = '';
        }
        $orders_detail = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'order_detail` od
                                                      LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = od.`id_order`)
                                                      WHERE `date_add` >= "'.pSQL($date_start).'"
                                                      AND `date_add` <= "'.pSQL($date_end).'"
                                                      AND o.`id_shop`="'.pSQL($this->context->shop->id).'" '.
                                                      $AND);
        foreach($orders_detail as $order_detail){

            $Order = new Order($order_detail['id_order']);
            $Customer = new Customer($Order->id_customer);

            if(Customer::customerIdExistsStatic($Customer->id) && $Customer->validateFields(false)){

                $f_no = array();
                
                // si la ligne de détail n'est pas valide, on passe à la suivante
                if(!$Order->validateFields(false)){continue;}
                $order = $Order->getFields();

                $f_no[1] = $order_detail['id_order'];
                $f_no[2] = $Order->reference;
                $f_no[3] = $order_detail['id_order_detail'];
                $f_no[4] = date("d.m.Y", strtotime($order['date_add']));
                // info client
                $customer = $Customer->getFields();
                if($customer['id_gender']==1){$gender=$this->l('Monsieur','order_detail');}else{$gender=$this->l('Madame','order_detail');}
                // info adresse
                if($address_choice=='delivery'){
                    $id_address = $order['id_address_delivery'];
                }else{
                    $id_address = $order['id_address_invoice'];
                }
                $Address = new Address($id_address);
                if(!empty($id_address) && $Address->validateFields(false)){
                    $address = $Address->getFields();
                }
                $f_no[5] = $gender;
                $f_no[6] = @$address['company'];
                $f_no[7] = @$address['firstname'];
                $f_no[8] = @$address['lastname'];
                $f_no[9] = @$address['address1'];
                $f_no[10] = @$address['address2'];
                $f_no[11] = @$address['postcode'];
                $f_no[12] = @$address['city'];
                    $country = Country::getNameById($order['id_lang'],@$address['id_country']);
                $f_no[13] = $country;
                $f_no[14] = @$address['phone'];
                $f_no[15] = @$address['phone_mobile'];
                $f_no[16] = $customer['email'];
                $f_no[17] = $customer['birthday'];
                if($customer['newsletter']){
                    $newsletter=$this->l('Oui','order_detail');
                }else{
                    $newsletter = $this->l('Non','order_detail');
                }
                $f_no[18] = $newsletter;
                $Group = new Group($customer['id_default_group']);
                $group = $Group->name[$order['id_lang']];
                $f_no[19] = $group;
                $f_no[20] = $order_detail['product_quantity'];
                $f_no[21] = $order_detail['product_name'];
                $f_no[22] = $order_detail['unit_price_tax_incl'];
                $id_tax = Db::getInstance()->getValue('SELECT `id_tax` 
                                                       FROM `'._DB_PREFIX_.'order_detail_tax` 
                                                       WHERE `id_order_detail` = "'.pSQL($order_detail['id_order_detail']).'"
                                                      ');
                $Tax = new Tax($id_tax);
                $f_no[23] = $Tax->rate;

                // récupère l'emplacement du produit + prix achat
                $id_product = $order_detail['product_id'];
                // simple
                if(empty($order_detail['product_attribute_id'])){
                    $product = Db::getInstance()->getRow('SELECT p.`location`, ps.`wholesale_price` 
                                                          FROM `'._DB_PREFIX_.'product` p
                                                          LEFT JOIN `'._DB_PREFIX_.'product_shop` ps ON p.`id_product`=ps.`id_product`
                                                          WHERE p.`id_product`="'.pSQL($id_product).'"
                                                          AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                    $location = $product['location'];
                    $wholesale_price = $product['wholesale_price'];
                // déclinaison
                }else{
                    $id_product_attribute = $order_detail['product_attribute_id'];
                    $productDecl = Db::getInstance()->getRow('SELECT pa.`location`, pas.`wholesale_price` 
                                                              FROM `'._DB_PREFIX_.'product_attribute` pa
                                                              LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` pas ON pa.`id_product_attribute`=pas.`id_product_attribute`
                                                              WHERE pa.`id_product`="'.pSQL($id_product).'" 
                                                              AND pa.`id_product_attribute`="'.pSQL($id_product_attribute).'"
                                                              AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                    $location = $productDecl['location'];
                    $wholesale_price = $productDecl['wholesale_price'];
                }

                $f_no[24] = $wholesale_price;
                $f_no[25] = $order_detail['product_weight'];
                $f_no[26] = $order_detail['product_ean13'];
                $f_no[27] = $order_detail['product_reference'];
                $f_no[28] = $order_detail['product_supplier_reference'];
                $f_no[29] = $order['total_discounts'];
                $f_no[30] = $order_detail['reduction_amount_tax_incl'];
                $f_no[31] = $order_detail['reduction_amount_tax_excl'];
                $f_no[32] = $order['total_paid'];
                $f_no[33] = $order['total_paid_real'];
                $f_no[34] = $order['total_shipping'];
                $currency = $this->getCurrency($order['id_currency']);
                $f_no[35] = $currency['iso_code'];
                $f_no[36] = $order['payment'];
                $f_no[37] = $order['invoice_number'];
                $f_no[38] = $order['delivery_number'];
                if($order['id_carrier']>0){
                    $Carrier = new Carrier($order['id_carrier']);
                    $carrier = $Carrier->getFields();
                    $carrier_name = $carrier['name'];
                }else{
                    $carrier_name = '';
                }
                $f_no[39] = $carrier_name;
                $Language = new Language($order['id_lang']);
                if(!$Language->validateFields(false)){
                   $Language = new Language(Configuration::get('PS_LANG_DEFAULT'));
                }
                $language = $Language->getFields();
                $f_no[40] = $language['name'];
                $f_no[41] = $order['delivery_date'];
                $f_no[42] = $order['invoice_date'];
                $f_no[43] = $location;
                $cookie = $this->context->cookie;
                $Product = new Product($id_product);
                $Category = new Category($Product->id_category_default,$cookie->id_lang);
                $f_no[44] = $Category->name;
                $OrderState = new OrderState($order['current_state']);
                $f_no[45] = $OrderState->name[$this->context->cookie->id_lang];

                // Ordonne les cellules selon la base de donnée
                $f_place = array();
                if(is_array($fields_no)){
                    foreach($fields_no as $field_no){
                       $res = Db::getInstance()->ExecuteS('SELECT `place` FROM '._DB_PREFIX_.'customexporter 
                                                           WHERE `field_no`="'.pSQL($field_no).'" 
                                                           AND `export_type`="'.pSQL($export_type).'"
                                                           AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                       $place = $res[0]['place'];
                       $f_place[$place] = $f_no[$field_no];
                    }
                }
                $this->file[] = $f_place; // ajoute nouvelle ligne triée au fichier
            } // end if customer exist + valid
        }
        
    }
    
    /*
     * Exporte les clients
     * @param -
     * @return -
     */
    private function exportCustomer(){
        
        $date_start = Tools::getValue('date_start').' 00:00:00';
        $date_end = Tools::getValue('date_end').' 23:59:59';
        $export_type = Tools::getValue('export_type');
        $fields_no = Tools::getValue('fields_no');
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        
        // parcours les clients
        $customers = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'customer 
                                                  WHERE `date_add` >= "'.pSQL($date_start).'" AND `date_add` <= "'.pSQL($date_end).'"
                                                  AND `id_shop`="'.pSQL($this->context->shop->id).'"');

        foreach($customers as $customer){

           $f_no = array();
           // info client
           $Customer = new Customer($customer['id_customer']);
           if($Customer->validateFields(false)){

                $customer = $Customer->getFields();
                if($customer['id_gender']==1){
                    $gender=$this->l('Monsieur','customer');
                }elseif($customer['id_gender']==2){
                    $gender=$this->l('Madame','customer');
                }else{
                    $gender='';
                }

                // info adresse
                $res = Db::getInstance()->ExecuteS('SELECT `id_address` 
                                                    FROM '._DB_PREFIX_.'address 
                                                    WHERE `id_customer` = "'.pSQL($customer['id_customer']).'"
                                                    AND `active`=1
                                                    AND `deleted`=0');

                $id_address = @$res[0]['id_address'];
                $Address = new Address($id_address);
                if(!empty($id_address) && $Address->validateFields(false)){
                    $address = $Address->getFields();
                }else{
                    $address_tmp = $customer;
                    $address_tmp['id_country'] = $address['id_country'];
                    $address = $address_tmp;
                }

                $f_no[1] = $customer['id_customer'];
                $f_no[2] = $gender;
                $f_no[3] = $address['company'];
                $f_no[4] = $address['firstname'];
                $f_no[5] = $address['lastname'];

                if(!empty($id_address)){
                    $f_no[6] = @$address['address1'];
                    $f_no[7] = @$address['address2'];
                    $f_no[8] = @$address['postcode'];
                    $f_no[9] = @$address['city'];
                 }else{
                    $f_no[6] = '';
                    $f_no[7] = '';
                    $f_no[8] = '';
                    $f_no[9] = '';
                 }
                     $country = '';
                     $res = Db::getInstance()->ExecuteS('SELECT `id_country` 
                                                         FROM '._DB_PREFIX_.'country 
                                                         WHERE `id_country` = "'.pSQL(@$address['id_country']).'"');
                     if(!empty($res)){
                         $country = Country::getNameById($id_lang_default,@$address['id_country']);
                     }
                $f_no[10] = $country;

                if(!empty($id_address)){
                    $f_no[11] = @$address['phone'];
                    $f_no[12] = @$address['phone_mobile'];
                }else{
                    $f_no[11] = '';
                    $f_no[12] = '';
                }

                $f_no[13] = $customer['email'];
                $f_no[14] = $customer['birthday'];
                     if($customer['newsletter']){
                         $newsletter=$this->l('Oui','customer');
                     }else{
                         $newsletter = $this->l('Non','customer');
                     }
                $f_no[15] = $newsletter;
                     $Group = new Group($customer['id_default_group']);
                     $group = $Group->name[$id_lang_default];
                $f_no[16] = $group;
                     if($customer['active']){
                         $active = $this->l('Oui','customer');
                     }else{
                         $active = $this->l('Non','customer');
                     }
                $f_no[17] = $active;
                     if($customer['deleted']){
                        $deleted = $this->l('Oui','customer');
                     }else{
                        $deleted = $this->l('Non','customer');
                     }
                $f_no[18] = $deleted;

                $date_start_ca = Tools::getValue('date_start_customer_ca').' 00:00:00';
                $date_end_ca = Tools::getValue('date_end_customer_ca').' 23:59:59';
                $sql = 'SELECT SUM(`total_paid_tax_excl`) AS caHT, SUM(`total_paid_tax_incl`) AS caTTC 
                        FROM '._DB_PREFIX_.'orders 
                        WHERE `date_add` BETWEEN "'.pSQL($date_start_ca).'" AND "'.pSQL($date_end_ca).'" 
                        AND `id_customer` = "'.pSQL($Customer->id).'"
                        AND `valid`="1"';
                $res = Db::getInstance()->getRow($sql);

                $f_no[19] = $res['caHT'];
                $f_no[20] = $res['caTTC'];
                
                $Language = new Language($customer['id_lang']);
                $language = $Language->getFields();
                $f_no[21] = $language['name'];     

                // Ordonne les cellules selon la base de donnée
                $f_place = array();
                if(is_array($fields_no)){
                    foreach($fields_no as $field_no){
                        $res = Db::getInstance()->ExecuteS('SELECT `place` 
                                                            FROM '._DB_PREFIX_.'customexporter 
                                                            WHERE `field_no`="'.pSQL($field_no).'" 
                                                            AND `export_type`="'.pSQL($export_type).'"
                                                            AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                        $place = $res[0]['place'];
                        $f_place[$place] = $f_no[$field_no];
                    }
                }
                $this->file[] = $f_place; // ajoute nouvelle ligne triée au fichier
           }

        } // end foreach
        
    }
    
    /*
     * Exporte les produits
     * @param -
     * @return -
     */
    private function exportProduct(){
        
        $date_start = Tools::getValue('date_start').' 00:00:00';
        $date_end = Tools::getValue('date_end').' 23:59:59';
        $export_type = Tools::getValue('export_type');
        $fields_no = Tools::getValue('fields_no');
        
        // Config
        $export_combination = Tools::getValue('export_combination');
        $categories = Tools::getValue('categories');
        $languages = Tools::getValue('languages');
        $active = Tools::getValue('p_active');
        $stock_min = Tools::getValue('stock_min');

        // Autres
        $rewriting_settings = Configuration::get('PS_REWRITING_SETTINGS');
        $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');

        // Monnaie
        $cookie = $this->context->cookie;
        $cookie->id_currency = $id_currency;
        // Code iso monnaie
        $Currency = new Currency($id_currency);
        $currency = $Currency->getFields();
        $currency_iso_code = $currency['iso_code'];

        // Shop
        $Shop = new Shop($this->context->shop->id);

        // Produit déjà exporté
        $products_id_exported = array();

        // Image type large
        $images_type = ImageType::getImagesTypes();
        $img_type = '';
        foreach($images_type as $it){
            if(strpos($it['name'],'large')!==false){
                $img_type = $it['name'];
                break;
            }
        }
        if(empty($img_type)){
            $img_type = $images_type[0]['name'];
        }

        // Conserve les catégories
        Configuration::updateValue('CUSTOMEXPORTER_CATS',serialize($categories));

        // Parcourt la langue
        foreach($languages as $id_lang){

            // Parcourt les catégorie
            if(empty($categories)){$categories=array();}
            foreach($categories as $id_cat){
                $Category = new Category($id_cat,$id_lang);
                $sql = 'SELECT * FROM '._DB_PREFIX_.'product p
                        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                        ON p.`id_product` = pl.`id_product`
                        LEFT JOIN `'._DB_PREFIX_.'product_shop` ps
                        ON p.`id_product` = ps.`id_product`
                        LEFT JOIN `'._DB_PREFIX_.'category_product` cp
                        ON p.`id_product` = cp.`id_product`
                        WHERE cp.`id_category` = '.pSQL($id_cat).'
                        AND pl.`id_lang` = "'.pSQL($id_lang).'"
                        AND ps.`active` = "'.pSQL($active).'"
                        AND ps.`id_shop`="'.pSQL($this->context->shop->id).'"
                        AND p.`date_add` >= "'.pSQL($date_start).'" 
                        AND p.`date_add` <= "'.pSQL($date_end).'"
                        GROUP BY p.`id_product`,cp.`id_product`,pl.`id_product`';
                $products = Db::getInstance()->ExecuteS($sql);

                foreach($products as $product){

                    // lignes de produits
                    $Product = new Product($product['id_product']);
                    if($Product->validateFields(false)){

                        $f_no = array();

                        $product['quantity'] = StockAvailable::getQuantityAvailableByProduct($product['id_product']);
                        if($product['quantity']<$stock_min){
                            $f_no[1] = $product['id_product'];
                                $category_name_and_sub_category = Tools::getPath($Category->id,$Category->name);
                            $f_no[2] = $category_name_and_sub_category;
                            $f_no[3] = $product['name'];
                            $f_no[4] = $product['description_short'];
                            $f_no[5] = $product['description'];
                                // Si l'url rewrite est activé
                                if($rewriting_settings){
                                    $Link = new Link();
                                    $url_product = $Link->getProductLink($Product,null,null,$Product->ean13,$id_lang,$this->context->shop->id);
                                }else{
                                    $url_product = Tools::getHttpHost(true).__PS_BASE_URI__.'product.php?id_product='.$product['id_product'];
                                }
                            $f_no[6] = $url_product;
                                $product_image = $Product->getImages($id_lang);
                                $id_image_prod = @$product_image[0]['id_image'];
                                $image = new Image($id_image_prod);
                                $img_url = $Shop->getBaseURL().'img/p/'.$image->getExistingImgPath().'-'.$img_type.'.jpg';   
                                if(empty($id_image_prod) || $id_image_prod==0){$img_url='';}
                            $f_no[7] = $img_url;
                                $price = round($Product->getPrice(),2);
                            $f_no[8] = $price;

                                $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct($product['id_product']);
                                $res = Db::getInstance()->getRow('SELECT `id_tax` FROM '._DB_PREFIX_.'tax_rule 
                                                                  WHERE `id_tax_rules_group`="'.pSQL($id_tax_rules_group).'"
                                                                  AND `id_country`="'.pSQL(Configuration::get('PS_COUNTRY_DEFAULT')).'"');
                                if(!empty($res)){
                                    $Tax = new Tax($res['id_tax']);
                                    if($Tax->validateFields(false)){
                                        $tax = $Tax->getFields();
                                        $rate = $tax['rate'];
                                    }else{
                                        $rate = '';
                                    }
                                }else{
                                    $rate = '';
                                }

                            $f_no[9] = $rate;
                                $supplier_price = ProductSupplier::getProductSupplierPrice($product['id_product'],0,$product['id_supplier']);   
                            $f_no[10] = ((!empty($supplier_price) && $supplier_price!=0)?$supplier_price:$product['wholesale_price']);
                            $f_no[11] = $product['weight'];
                            $f_no[12] = $product['quantity'];
                            $f_no[13] = $product['reference'];
                                $supplier_name = Supplier::getNameById($product['id_supplier']);
                            $f_no[14] = $supplier_name;
                            $f_no[15] = ProductSupplier::getProductSupplierReference($product['id_product'],0,$product['id_supplier']);
                                $manufacturer_name = Manufacturer::getNameById($product['id_manufacturer']);
                            $f_no[16] = $manufacturer_name;
                            $f_no[17] = $product['ean13'];
                            $f_no[18] = $product['upc'];
                            $f_no[19] = $product['ecotax'];
                            $f_no[20] = '';
                            $f_no[21] = '';
                            $f_no[22] = '0000-00-00 00:00:00';
                            $f_no[23] = '0000-00-00 00:00:00';
                            $specifique_price = SpecificPrice::getByProductId($product['id_product']);
                            if(!empty($specifique_price)){
                                $specifique_price = $specifique_price[0];
                                if($specifique_price['reduction_type']=='amount'){
                                    $f_no[20] = $specifique_price['reduction'];
                                }else{
                                    $f_no[21] = $specifique_price['reduction'];
                                }
                                $f_no[22] = $specifique_price['from'];
                                $f_no[23] = $specifique_price['to'];
                            } 
                            $f_no[24] = $currency_iso_code;
                            $f_no[25] = $product['active'];
                            $f_no[26] = $product['meta_description'];
                            $f_no[27] = $product['meta_keywords'];
                            $f_no[28] = $product['meta_title'];
                            $f_no[29] = $product['date_add'];
                        }

                        // si on ne veut pas des combinaisons
                        if(!$export_combination){$product_has_attributes = 0;}else{$product_has_attributes = $Product->hasAttributes();}
                        // lignes de déclinaison
                        $combArray = array(); // création d'un array avec les combinaisons
                        if($product_has_attributes>0){
                            $combinaisons = @$Product->getAttributeCombinaisons($id_lang);
                            if(is_array($combinaisons)){
                                foreach($combinaisons as $combinaison){
                                    $combArray[$combinaison['id_product_attribute']]['wholesale_price'] = $combinaison['wholesale_price'];
                                    $combArray[$combinaison['id_product_attribute']]['id_product_attribute'] = $combinaison['id_product_attribute'];
                                    $combArray[$combinaison['id_product_attribute']]['price'] = $combinaison['price'];
                                    $combArray[$combinaison['id_product_attribute']]['weight'] = $combinaison['weight'];
                                         $quantity_decl = StockAvailable::getQuantityAvailableByProduct($product['id_product'],$combinaison['id_product_attribute']);
                                    $combArray[$combinaison['id_product_attribute']]['quantity'] = $quantity_decl;
                                    $combArray[$combinaison['id_product_attribute']]['reference'] = $combinaison['reference'];
                                    $combArray[$combinaison['id_product_attribute']]['supplier_reference'] = $combinaison['supplier_reference'];
                                    $combArray[$combinaison['id_product_attribute']]['ean13'] = $combinaison['ean13'];
                                    $combArray[$combinaison['id_product_attribute']]['ecotax'] = $combinaison['ecotax'];
                                    $combArray[$combinaison['id_product_attribute']]['price'] = $combinaison['price'];
                                    $combArray[$combinaison['id_product_attribute']]['attributes'][] = array($combinaison['group_name'], $combinaison['attribute_name'], $combinaison['id_attribute']);
                                    $combArray[$combinaison['id_product_attribute']]['upc'] = $combinaison['upc'];
                                }
                            }
                            if(isset($combArray)){

                                // Crée la description de la déclinaison
                                foreach($combArray as $product_attribute){
                                    $list = '';
                                    foreach($product_attribute['attributes'] AS $attribute){
                                        $list .= addslashes(htmlspecialchars($attribute[0])).' - '.addslashes(htmlspecialchars($attribute[1])).', ';
                                    }
                                    $list = rtrim($list,', '); // description spécifique déclinaison
                                    $f_no = array(); // vide la ligne

                                    if($product_attribute['quantity']<$stock_min){
                                        $f_no[1] = $product['id_product'].'-'.$product_attribute['id_product_attribute'];
                                        $f_no[2] = $category_name_and_sub_category;
                                        $f_no[3] = $product['name'].' '.Tools::stripslashes($list);
                                        $f_no[4] = $product['description_short'];
                                        $f_no[5] = $product['description'];
                                        $f_no[6] = $url_product;
                                            $product_image = $Product->_getAttributeImageAssociations($product_attribute['id_product_attribute']);
                                            if(isset($product_image[0])){$id_image_decl = $product_image[0];}else{$id_image_decl=0;}
                                            if($id_image_decl==0){$id_image_decl=$id_image_prod;}
                                            $image = new Image($id_image_decl);
                                            $img_url = $Shop->getBaseURL().'img/p/'.$image->getExistingImgPath().'-'.$img_type.'.jpg';  
                                            if(empty($id_image_decl) || $id_image_decl==0){$img_url='';}
                                        $f_no[7] = $img_url;
                                        $price = round($Product->getPrice(true,$product_attribute['id_product_attribute']),2);
                                        $f_no[8] = $price;
                                        $f_no[9] = (isset($tax['rate'])?$tax['rate']:'');
                                        $supplier_price = ProductSupplier::getProductSupplierPrice($product['id_product'],$product_attribute['id_product_attribute'],$product['id_supplier']);
                                        if(!empty($supplier_price) && $supplier_price>0){
                                            $f_no[10] = $supplier_price;
                                        }elseif(!empty($product['wholesale_price']) && $product_attribute['wholesale_price']!=0){
                                            $f_no[10] = $product_attribute['wholesale_price'];
                                        }else{
                                            $f_no[10] = $product['wholesale_price'];
                                        }
                                        $f_no[11] = $product['weight']+$product_attribute['weight'];
                                        $f_no[12] = $product_attribute['quantity'];
                                        $f_no[13] = $product_attribute['reference'];
                                        $f_no[14] = $supplier_name;
                                        $f_no[15] = ProductSupplier::getProductSupplierReference($product['id_product'],$product_attribute['id_product_attribute'],$product['id_supplier']);
                                        $f_no[16] = $manufacturer_name;
                                        $f_no[17] = $product_attribute['ean13'];
                                        $f_no[18] = $product_attribute['upc'];
                                        $f_no[19] = $product_attribute['ecotax'];
                                        $f_no[20] = '';
                                        $f_no[21] = '';
                                        $f_no[22] = '0000-00-00 00:00:00';
                                        $f_no[23] = '0000-00-00 00:00:00';
                                        $specifique_price = SpecificPrice::getByProductId($product['id_product']);
                                        if(!empty($specifique_price)){
                                            $specifique_price = $specifique_price[0];
                                            if($specifique_price['reduction_type']=='amount'){
                                                $f_no[20] = $specifique_price['reduction'];
                                            }else{
                                                $f_no[21] = $specifique_price['reduction'];
                                            }
                                            $f_no[22] = $specifique_price['from'];
                                            $f_no[23] = $specifique_price['to'];
                                        }
                                        $f_no[24] = $currency_iso_code;
                                        $f_no[25] = $product['active'];
                                        $f_no[26] = $product['meta_description'];
                                        $f_no[27] = $product['meta_keywords'];
                                        $f_no[28] = $product['meta_title'];
                                        $f_no[29] = $product['date_add'];

                                        // Ordonne les cellules selon la base de donnée
                                        $f_place = array();
                                        if(is_array($fields_no)){
                                            foreach($fields_no as $field_no){
                                               $res = Db::getInstance()->ExecuteS('SELECT `place` 
                                                                                   FROM '._DB_PREFIX_.'customexporter 
                                                                                   WHERE `field_no`="'.pSQL($field_no).'" 
                                                                                   AND `export_type`="'.pSQL($export_type).'"
                                                                                   AND `id_shop`="'.pSQL($this->context->shop->id).'"');
                                               $place = $res[0]['place'];
                                               $f_place[$place] = $f_no[$field_no];
                                            }
                                        }
                                        if(!in_array($product['id_product'].'-'.$product_attribute['id_product_attribute'],$products_id_exported)){
                                            $products_id_exported[] = $product['id_product'].'-'.$product_attribute['id_product_attribute'];
                                            $this->file[] = $f_place; // ajoute nouvelle ligne triée au fichier
                                        }
                                    }
                                }
                            }
                        }else{
                           // Ordonne les cellules selon la base de donnée
                           if($product['quantity']<$stock_min){
                               $f_place = array();
                                   if(is_array($fields_no)){
                                   foreach($fields_no as $field_no){
                                       $res = Db::getInstance()->ExecuteS('SELECT `place` 
                                                                           FROM '._DB_PREFIX_.'customexporter 
                                                                           WHERE `field_no`="'.pSQL($field_no).'"
                                                                           AND `export_type`="'.pSQL($export_type).'"
                                                                           AND `id_shop`="'.pSQL($this->context->shop->id).'"
                                                                           ');
                                       $place = $res[0]['place'];
                                       $f_place[$place] = $f_no[$field_no];
                                   }
                               }
                               if(!in_array($product['id_product'],$products_id_exported)){
                                    $products_id_exported[] = $product['id_product'];
                                    $this->file[] = $f_place; // ajoute nouvelle ligne triée au fichier
                               }
                           }
                        }

                   } // end valid mode

                } // end foreach products
            } // end foreach categories
        } // end foreach languages
        
    }
    
    /*
     * Retourne la monnaie
     * @param   $id_currency
     * @return  array
    */
    public function getCurrency($id_currency){
       $Currency = new Currency($id_currency);
        if(empty($Currency->id)){
            $Currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));     
        }
        return $Currency->getFields();
    }

    /*
     * Crée le fichier avec le contenu
     * @param string (type de fichier/nom fichier)
     * @return -
     */
    private function create_file($file_type){
        
         $file_content = "";
         // parcours file afin d'avoir un fichier avec x lignes
         foreach($this->file as $line){
           $count_field = 0;
           $last_field = count($line);
           foreach($line as $field){
             
             $count_field ++;          
             $field = $this->sanitize($field);        
             
             if($this->format=='csv'){ // On force la conversion pour que ce soit lisible avec excel
                $field = Tools::substr(chr(255).chr(254).mb_convert_encoding($field, "UTF-16LE", "UTF-8"),2);
             }        
             if($count_field==$last_field){
                 $file_content.=$field."\r\n";
             }else{
                 $file_content.=$field.$this->separator;
              }
            }
            
            // force l'encodage
            $char_utf8_write = Configuration::get('CUSTOMEXPORTER_CHAR_UTF8_WRITE');
            if($char_utf8_write){
                $file_content = utf8_decode($file_content);
            }
            
            $f = fopen(dirname(__FILE__).'/downloads/'.$file_type.'_id_shop_'.$this->context->shop->id.'.'.$this->format,'w+');
            fwrite($f,$file_content);
            fclose($f);
            if(!$f){
                $this->_html = $this->msgError($this->l('Erreur de création du fichier. Vérifiez que le répertoire suivant est bien en CHMOD 777').' : "/modules/customexporter/downloads/"');
            }
        }
    }

    /*
     * Nettoie la chaine
     * @param string (chaine)
     * @return string
     */
    public function sanitize($string){
        
        // conversion UTF
        $char_utf8 = Configuration::get('CUSTOMEXPORTER_CHAR_UTF8');
        if($char_utf8){
            $string = Tools::htmlentitiesDecodeUTF8($string);
        }
        
        $string = htmlspecialchars_decode($string);
        $string = html_entity_decode($string);
        $string = strip_tags($string);
        
        // cleanage du contenu en général
        $string = str_replace(';',':',$string);
        $string = str_replace(CHR(13).CHR(10),"",$string); // enlève les retours chariot
        $string = preg_replace('/<br\\s*?\/??>/i','', $string);
        $string = trim($string);
        $string = str_replace("\r",'',$string);
        $string = str_replace("\n",'',$string);
        
        // remplacer des caractère déformé
        $char_spec = Configuration::get('CUSTOMEXPORTER_CHAR_SPEC');
        if($char_spec){
            $string = str_replace('Ã©','é',$string);
            $string = str_replace('à‰','É',$string);
            $string = str_replace('â€™',"'",$string);
            $string = str_replace('Ã','à',$string);
            $string = str_replace('à¨','è',$string);
            $string = str_replace('àª','ê',$string);
            $string = str_replace('à§','ç',$string);
            $string = str_replace('à¢','â',$string);
            $string = str_replace('ï»¿','',$string);
            $string = str_replace('Â','',$string);
            $string = str_replace('â„¢','',$string);
            $string = str_replace("%&",'',$string);
            $string = str_replace('|',' ',$string);
        }
        return $string;
        
    } 

    /*
     * Pour debug var/array
     * @param var/array
     * @return -
     */
    public function debug($var){
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    /*
     * Retourne la version de prestashop en float
     * 1.4.3 == 1.43 pour faire une comparaison sur la grandeur
     * @param  -
     * @return float (1 décimal)
     */
    public function getPsVersion(){
        $mainVersion = Tools::substr(_PS_VERSION_,0,1);
        $subVersion = str_replace('.','',Tools::substr(_PS_VERSION_,2,5));
        $version = $mainVersion.'.'.$subVersion;
        return $version;
    }
    
    /*
     * Affiche un message de confirmation
     * @param $msg
     * @return $msg
     */
    public function msgConf($msg){
        return '<div class="conf module_confirmation confirm alert alert-success">'.$msg.'</div>';
    }
    
    /*
     * Affiche un message d'erreur
     * @param $msg
     * @return $msg
     */
    public function msgError($msg){
        return '<div class="alert error module_error alert-danger">'.$msg.'</div>';
    } 
    
    /*
     * Affiche un message d'alerte
     * @param $msg
     * @return $msg
     */
    public function msgAlert($msg){
        return '<div class="alert alert-warning">'.$msg.'</div>';
    } 
    
    /*
     * Initialise la base de données
     * @param -
     * @return -
     */
    private function init_db(){
        
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'customexporter`');

        $shops = Shop::getShops();
        foreach($shops as $shop){

            if(!Shop::isFeatureActive()){$shop['id_shop']=Configuration::get('PS_SHOP_DEFAULT');} // 1 shop only

            Db::getInstance()->Execute(
                    "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."customexporter` (
                      `id_customexporter` int(11) NOT NULL AUTO_INCREMENT,
                      `field_no` int(11) NOT NULL,
                      `field_name` varchar(50) NOT NULL,
                      `place` int(11) NOT NULL,
                      `export_type` varchar(50) NOT NULL,
                      `checked` smallint(1) NOT NULL,
                      `id_shop` int(11) NOT NULL,
                      PRIMARY KEY (`id_customexporter`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;");

            Db::getInstance()->Execute("
            INSERT INTO `"._DB_PREFIX_."customexporter` (`field_no`,`field_name`,`place`,`export_type`,`checked`,`id_shop`) VALUES
            (1, '".pSQL($this->l('no commande'))."', 1, 'order',1,".pSQL($shop['id_shop'])."),
            (2, '".pSQL($this->l('référence'))."', 2, 'order',1,".pSQL($shop['id_shop'])."),
            (3, '".pSQL($this->l('date commande'))."', 3, 'order',1,".pSQL($shop['id_shop'])."),
            (4, '".pSQL($this->l('société'))."', 4, 'order',1,".pSQL($shop['id_shop'])."),
            (5, '".pSQL($this->l('civilité'))."', 5, 'order',1,".pSQL($shop['id_shop'])."),
            (6, '".pSQL($this->l('prénom'))."', 6, 'order',1,".pSQL($shop['id_shop'])."),
            (7, '".pSQL($this->l('nom'))."', 7, 'order',1,".pSQL($shop['id_shop'])."),
            (8, '".pSQL($this->l('addresse'))." 1', 8, 'order',1,".pSQL($shop['id_shop'])."),
            (9, '".pSQL($this->l('addresse'))." 2', 9, 'order',1,".pSQL($shop['id_shop'])."),
            (10, '".pSQL($this->l('code postal'))."', 10, 'order',1,".pSQL($shop['id_shop'])."),
            (11, '".pSQL($this->l('ville'))."', 11, 'order',1,".pSQL($shop['id_shop'])."),
            (12, '".pSQL($this->l('pays'))."', 12, 'order',1,".pSQL($shop['id_shop'])."),
            (13, '".pSQL($this->l('téléphone'))."', 13, 'order',1,".pSQL($shop['id_shop'])."),
            (14, '".pSQL($this->l('téléphone portable'))."', 14, 'order',1,".pSQL($shop['id_shop'])."),
            (15, '".pSQL($this->l('email'))."', 15, 'order',1,".pSQL($shop['id_shop'])."),
            (16, '".pSQL($this->l('date anniversaire'))."', 16, 'order',1,".pSQL($shop['id_shop'])."),
            (17, '".pSQL($this->l('inscrit à la newsletter'))."', 17, 'order',1,".pSQL($shop['id_shop'])."),
            (18, '".pSQL($this->l('groupe client'))."', 18, 'order',1,".pSQL($shop['id_shop'])."),
            (19, '".pSQL($this->l('total rabais'))."', 19, 'order',1,".pSQL($shop['id_shop'])."),
            (20, '".pSQL($this->l('total commande'))."', 20, 'order',1,".pSQL($shop['id_shop'])."),
            (21, '".pSQL($this->l('total (payé par le client)'))."', 21, 'order',1,".pSQL($shop['id_shop'])."),
            (22, '".pSQL($this->l('total frais de port'))."', 22, 'order',1,".pSQL($shop['id_shop'])."),
            (23, '".pSQL($this->l('total des produits'))."', 23, 'order',1,".pSQL($shop['id_shop'])."),
            (24, '".pSQL($this->l('total des produits avec taxe / TVA'))."', 24, 'order',1,".pSQL($shop['id_shop'])."),
            (25, '".pSQL($this->l('total emballage'))."', 25, 'order',1,".pSQL($shop['id_shop'])."),
            (26, '".pSQL($this->l('monnaie'))."', 26, 'order',1,".pSQL($shop['id_shop'])."),
            (27, '".pSQL($this->l('payé avec'))."', 27, 'order',1,".pSQL($shop['id_shop'])."),
            (28, '".pSQL($this->l('facture no'))."', 28, 'order',1,".pSQL($shop['id_shop'])."),
            (29, '".pSQL($this->l('livraison no'))."', 29, 'order',1,".pSQL($shop['id_shop'])."),
            (30, '".pSQL($this->l('cadeau'))."', 30, 'order',1,".pSQL($shop['id_shop'])."),
            (31, '".pSQL($this->l('message cadeau'))."', 31, 'order',1,".pSQL($shop['id_shop'])."),
            (32, '".pSQL($this->l('transporteur'))."', 32, 'order',1,".pSQL($shop['id_shop'])."),
            (33, '".pSQL($this->l('langue'))."', 33, 'order',1,".pSQL($shop['id_shop'])."),
            (34, '".pSQL($this->l('date livraison'))."', 34, 'order',1,".pSQL($shop['id_shop'])."),
            (35, '".pSQL($this->l('date facture'))."', 35, 'order',1,".pSQL($shop['id_shop'])."),
            (36, '".pSQL($this->l('date modification commande'))."', 36, 'order',1,".pSQL($shop['id_shop'])."),
            (37, '".pSQL($this->l('catégories'))."', 37, 'order',1,".pSQL($shop['id_shop'])."),
            (38, '".pSQL($this->l('statut commande'))."', 38, 'order',1,".pSQL($shop['id_shop'])."),

            (1, '".pSQL($this->l('no de commande'))."', 1, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (2, '".pSQL($this->l('référence'))."', 2, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (3, '".pSQL($this->l('id/no ligne commande'))."', 3, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (4, '".pSQL($this->l('date commande'))."', 4, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (5, '".pSQL($this->l('civilité'))."', 5, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (6, '".pSQL($this->l('société'))."', 6, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (7, '".pSQL($this->l('prénom'))."', 7, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (8, '".pSQL($this->l('nom'))."', 8, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (9, '".pSQL($this->l('adresse'))." 1', 9, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (10, '".pSQL($this->l('adresse'))." 2', 10, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (11, '".pSQL($this->l('code postal'))."', 11, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (12, '".pSQL($this->l('ville'))."', 12, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (13, '".pSQL($this->l('pays'))."', 13, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (14, '".pSQL($this->l('téléphone'))."', 14, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (15, '".pSQL($this->l('téléphone mobile'))."', 15, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (16, '".pSQL($this->l('email'))."', 16, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (17, '".pSQL($this->l('date anniversaire'))."', 17, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (18, '".pSQL($this->l('inscrit à la newsletter'))."', 18, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (19, '".pSQL($this->l('groupe client'))."', 19, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (20, '".pSQL($this->l('quantité'))."', 20, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (21, '".pSQL($this->l('nom produit'))."', 21, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (22, '".pSQL($this->l('prix vente'))."', 22, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (23, '".pSQL($this->l('taux taxe / TVA'))."', 23, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (24, '".pSQL($this->l('prix achat'))."', 24, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (25, '".pSQL($this->l('poids'))."', 25, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (26, '".pSQL($this->l('ean13'))."', 26, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (27, '".pSQL($this->l('référence'))."', 27, 'order_detail',1,".pSQL($shop['id_shop'])."),     
            (28, '".pSQL($this->l('référence fournisseur'))."', 28, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (29, '".pSQL($this->l('total rabais (commande)'))."', 29, 'order_detail',1,".pSQL($shop['id_shop'])."),   
            (30, '".pSQL($this->l('total rabais (ligne produits ttc)'))."', 30, 'order_detail',1,".pSQL($shop['id_shop'])."),  
            (31, '".pSQL($this->l('total rabais (ligne produits ht)'))."', 31, 'order_detail',1,".pSQL($shop['id_shop'])."),  
            (32, '".pSQL($this->l('total commande'))."', 32, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (33, '".pSQL($this->l('total (payé par le client)'))."', 33, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (34, '".pSQL($this->l('total frais de port'))."', 34, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (35, '".pSQL($this->l('monnaie'))."', 35, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (36, '".pSQL($this->l('payé avec'))."', 36, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (37, '".pSQL($this->l('facture no'))."', 37, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (38, '".pSQL($this->l('livraison no'))."', 38, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (39, '".pSQL($this->l('transporteur'))."', 39, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (40, '".pSQL($this->l('langue'))."', 40, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (41, '".pSQL($this->l('date livraison'))."', 41, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (42, '".pSQL($this->l('date facture'))."', 42, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (43, '".pSQL($this->l('emplacement'))."', 43, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (44, '".pSQL($this->l('catégorie'))."', 44, 'order_detail',1,".pSQL($shop['id_shop'])."),
            (45, '".pSQL($this->l('statut commande'))."',45, 'order_detail',1,".pSQL($shop['id_shop'])."),

            (1, '".pSQL($this->l('identifiant'))."', 1, 'customer',1,".pSQL($shop['id_shop'])."),
            (2, '".pSQL($this->l('civilité'))."', 2, 'customer',1,".pSQL($shop['id_shop'])."),
            (3, '".pSQL($this->l('société'))."', 3, 'customer',1,".pSQL($shop['id_shop'])."),
            (4, '".pSQL($this->l('prénom'))."', 4, 'customer',1,".pSQL($shop['id_shop'])."),
            (5, '".pSQL($this->l('nom'))."', 5, 'customer',1,".pSQL($shop['id_shop'])."),
            (6, '".pSQL($this->l('adresse'))." 1', 6, 'customer',1,".pSQL($shop['id_shop'])."),
            (7, '".pSQL($this->l('adresse'))." 2', 7, 'customer',1,".pSQL($shop['id_shop'])."),
            (8, '".pSQL($this->l('code postal'))."', 8, 'customer',1,".pSQL($shop['id_shop'])."),
            (9, '".pSQL($this->l('ville'))."', 9, 'customer',1,".pSQL($shop['id_shop'])."),
            (10, '".pSQL($this->l('pays'))."', 10, 'customer',1,".pSQL($shop['id_shop'])."),
            (11, '".pSQL($this->l('téléphone'))."', 11, 'customer',1,".pSQL($shop['id_shop'])."),
            (12, '".pSQL($this->l('téléphone mobile'))."', 12, 'customer',1,".pSQL($shop['id_shop'])."),
            (13, '".pSQL($this->l('email'))."', 13, 'customer',1,".pSQL($shop['id_shop'])."),
            (14, '".pSQL($this->l('date anniversaire'))."', 14, 'customer',1,".pSQL($shop['id_shop'])."),
            (15, '".pSQL($this->l('newsletter'))."', 15, 'customer',1,".pSQL($shop['id_shop'])."),
            (16, '".pSQL($this->l('groupe client'))."', 16, 'customer',1,".pSQL($shop['id_shop'])."),
            (17, '".pSQL($this->l('actif'))."', 17, 'customer',1,".pSQL($shop['id_shop'])."),
            (18, '".pSQL($this->l('supprimé'))."', 18, 'customer',1,".pSQL($shop['id_shop'])."),
            (19, '".pSQL($this->l('chiffre d\'affaire').' (ht)')."', 19, 'customer',1,".pSQL($shop['id_shop'])."),
            (20, '".pSQL($this->l('chiffre d\'affaire').' (ttc)')."', 20, 'customer',1,".pSQL($shop['id_shop'])."),
            (21, '".pSQL($this->l('langue'))."', 21, 'customer',1,".pSQL($shop['id_shop'])."),

            (1, '".pSQL($this->l('identifiant produit'))."', 1, 'product',1,".pSQL($shop['id_shop'])."),
            (2, '".pSQL($this->l('catégorie'))."', 2, 'product',1,".pSQL($shop['id_shop'])."),
            (3, '".pSQL($this->l('nom'))."', 3, 'product',1,".pSQL($shop['id_shop'])."),
            (4, '".pSQL($this->l('description courte'))."', 4, 'product',1,".pSQL($shop['id_shop'])."),
            (5, '".pSQL($this->l('description longue'))."', 5, 'product',1,".pSQL($shop['id_shop'])."),
            (6, '".pSQL($this->l('url produit'))."', 6, 'product',1,".pSQL($shop['id_shop'])."),
            (7, '".pSQL($this->l('url image'))."', 7, 'product',1,".pSQL($shop['id_shop'])."),
            (8, '".pSQL($this->l('prix (ttc)'))."', 8, 'product',1,".pSQL($shop['id_shop'])."),
            (9, '".pSQL($this->l('% taxe/TVA'))."', 9, 'product',1,".pSQL($shop['id_shop'])."),
            (10, '".pSQL($this->l('prix achat'))."', 10, 'product',1,".pSQL($shop['id_shop'])."),
            (11, '".pSQL($this->l('poids'))."', 11, 'product',1,".pSQL($shop['id_shop'])."),
            (12, '".pSQL($this->l('quantité en stock'))."', 12, 'product',1,".pSQL($shop['id_shop'])."),
            (13, '".pSQL($this->l('référence'))."', 13, 'product',1,".pSQL($shop['id_shop'])."),
            (14, '".pSQL($this->l('fournisseur'))."', 14, 'product',1,".pSQL($shop['id_shop'])."),
            (15, '".pSQL($this->l('référence fournisseur'))."', 15, 'product',1,".pSQL($shop['id_shop'])."),
            (16, '".pSQL($this->l('fabricant'))."', 16, 'product',1,".pSQL($shop['id_shop'])."),
            (17, '".pSQL($this->l('ean13'))."', 17, 'product',1,".pSQL($shop['id_shop'])."),
            (18, '".pSQL($this->l('UPC'))."', 18, 'product',1,".pSQL($shop['id_shop'])."),
            (19, '".pSQL($this->l('écotax'))."', 19, 'product',1,".pSQL($shop['id_shop'])."),
            (20, '".pSQL($this->l('montant réduction'))."', 20, 'product',1,".pSQL($shop['id_shop'])."),
            (21, '".pSQL($this->l('% réduction'))."', 21, 'product',1,".pSQL($shop['id_shop'])."),
            (22, '".pSQL($this->l('réduction depuis le'))."', 22, 'product',1,".pSQL($shop['id_shop'])."),
            (23, '".pSQL($this->l('réduction jusqu’au'))."', 23, 'product',1,".pSQL($shop['id_shop'])."),
            (24, '".pSQL($this->l('monnaie'))."', 24, 'product',1,".pSQL($shop['id_shop'])."),
            (25, '".pSQL($this->l('actif'))."', 25, 'product',1,".pSQL($shop['id_shop'])."),
            (26, '".pSQL($this->l('méta description'))."', 26, 'product',1,".pSQL($shop['id_shop'])."),
            (27, '".pSQL($this->l('méta keywords'))."', 27, 'product',1,".pSQL($shop['id_shop'])."),
            (28, '".pSQL($this->l('méta title'))."', 28, 'product',1,".pSQL($shop['id_shop'])."),
            (29, '".pSQL($this->l('date création'))."', 29, 'product',1,".pSQL($shop['id_shop']).")

            ;");
        } 
    }

}
