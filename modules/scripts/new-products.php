<?php

// 22.04.20 - Webbax | TUTO 99
require(dirname(__FILE__).'/../../config/config.inc.php');

$token = Tools::getValue('token');
if($token!='N3W!8072'){die('error token');}

echo '
<form method="POST">
    <h1>Remettre en "nouveautés"</h1>
    id_product :  <input type="text" name="id_product"/>
    Date : <input type="date" name="date_add">
    <input type="submit" value="Valider"> 
</form>';

if(isset($_POST['id_product'])){
    $date_add = Tools::getValue('date_add');
    $id_product = Tools::getValue('id_product');
    $product_db = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'product WHERE `id_product`="'.pSQL($id_product).'"');
    if(!empty($product_db)){
        if(empty($date_add)){
            $date_add=date('Y-m-d H:i:s');
        }else{
            $date_add_ex = explode('-',$date_add);
            $date_add_db = $date_add_ex[0].'-'.$date_add_ex[1].'-'.$date_add_ex[2].' '.date('H:i:s');
            $date_add = $date_add_db;
        }
        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'product SET `date_add`="'.pSQL($date_add).'" WHERE `id_product`="'.pSQL($id_product).'"');
        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'product_shop SET `date_add`="'.pSQL($date_add).'" WHERE `id_product`="'.pSQL($id_product).'"');
        echo '<strong>La date a été modifiée</strong>';
    }else{
        echo '<strong>Aucun produit trouvé</strong>';
    }
}

?>

