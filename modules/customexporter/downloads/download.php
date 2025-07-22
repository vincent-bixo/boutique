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

include(dirname(__FILE__).'/../../../config/config.inc.php');
$file = Tools::getValue('file');

if(strpos($file,'?')!==false){
   $file_name = explode('?',$file);
   $file = str_replace('file=','',$file_name[0]);
}

$handle = fopen($file,"r");
header('Content-Description: File Transfer');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename='.$file);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.filesize($file));
@ob_clean();
flush();
readfile($file);
fclose($handle);
exit;

?>