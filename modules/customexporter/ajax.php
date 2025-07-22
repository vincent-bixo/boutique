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

// autoriser l'accès Ajax
header('Access-Control-Allow-Origin: *');

include(dirname(__FILE__).'/../../config/config.inc.php');
//@include(dirname(__FILE__).'/../../header.php');

/* debug */
//$oFirebug = FirePHP::getInstance(true);
//$oFirebug->fb();

$action = Tools::getValue('action');
$id = Tools::getValue('id');
$order = Tools::getValue('place');
$export_type = Tools::getValue('export_type');
$id_shop = Tools::getValue('id_shop');
$token_module = Tools::getValue('token_module');

if($token_module!=_COOKIE_IV_){
   $res = array('error'=>'token module error');
   echo Tools::jsonEncode($res);
}else{
    // Effectue un traitement selon les paramètres $_GET
    switch($action){
        case 'list_fields':
              list_fields($export_type,$id_shop);
              break;
        case 'move_up':
              move_up($id,$order,$export_type,$id_shop);
              break;
        case 'move_down':
              move_down($id,$order,$export_type,$id_shop);
              break;
    }
}

/*
 * Liste les champs pour l'export type
 * @param string (type d'export)
 * @param int (id_shop)
 * return json
 */
function list_fields($export_type,$id_shop){
      $columns_place = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'customexporter 
                                                    WHERE `export_type`="'.pSQL($export_type).'" 
                                                    AND `id_shop`="'.pSQL($id_shop).'"
                                                    ORDER BY `place` ASC');
      @ob_end_clean();
      ob_start();
      echo Tools::jsonEncode($columns_place);
}

/*
 * Monte d'un cran le champ
 * @param int (id)
 * @param int (ordre actuel)
 * @param string (type export)
 * @param int ($id_shop)
 * return -
 */
function move_up($id,$place,$export_type,$id_shop){
    $place_up = $place-1;
    $place_down = $place;

    $place_select = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'customexporter WHERE `place`="'.pSQL($place).'" AND `export_type`="'.pSQL($export_type).'" AND `id_shop`="'.pSQL($id_shop).'"');
    $place_dest = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'customexporter WHERE `place`="'.pSQL($place_up).'" AND `export_type`="'.pSQL($export_type).'" AND `id_shop`="'.pSQL($id_shop).'"');
    if(!empty($place_select) && !empty($place_dest)){
        // Monte
        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customexporter` 
                                    SET `place`="'.pSQL($place_down).'" 
                                    WHERE `id_customexporter`="'.pSQL($place_dest[0]['id_customexporter']).'" 
                                    AND `export_type`="'.pSQL($export_type).'"
                                    AND `id_shop`="'.pSQL($id_shop).'"');
        // Descend
        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customexporter` 
                                    SET `place`='.pSQL($place_up).'
                                    WHERE `id_customexporter`="'.pSQL($place_select[0]['id_customexporter']).'" 
                                    AND `export_type`="'.pSQL($export_type).'"
                                    AND `id_shop`="'.pSQL($id_shop).'"');
    }
}

/*
 * Descend d'un cran le champ
 * @param int (id)
 * @param int (ordre actuel)
 * @param string (type export)
 * @param int ($id_shop)
 * return -
 */
function move_down($id,$place,$export_type,$id_shop){
    $place_down = $place+1;
    $place_up = $place;
    $place_select = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'customexporter WHERE `place`="'.pSQL($place).'" AND `export_type`="'.pSQL($export_type).'" AND `id_shop`="'.pSQL($id_shop).'"');
    $place_dest = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'customexporter WHERE `place`="'.pSQL($place_down).'" AND `export_type`="'.pSQL($export_type).'" AND `id_shop`="'.pSQL($id_shop).'"');
    if(!empty($place_select) && !empty($place_dest)){
        // Monte
        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customexporter` 
                                    SET `place`="'.pSQL($place_up).'" 
                                    WHERE `id_customexporter`="'.pSQL($place_dest[0]['id_customexporter']).'"
                                    AND `export_type`="'.pSQL($export_type).'"
                                    AND `id_shop`="'.pSQL($id_shop).'"');
        // Descend
        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customexporter` 
                                    SET `place`="'.pSQL($place_down).'" 
                                    WHERE `id_customexporter`='.pSQL($place_select[0]['id_customexporter']).' 
                                    AND `export_type`="'.pSQL($export_type).'"
                                    AND `id_shop`="'.pSQL($id_shop).'"');
    }
}