<?php
/**
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
 */
include dirname(__FILE__) . '/../../../config/config.inc.php';
include dirname(__FILE__) . '/../../../init.php';
include dirname(__FILE__) . '/../ntstats.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$nts = new NtStats();
$page = 'ntstats';

// d($nts->secure_key);

if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $nts->secure_key) {
    echo $nts->l('Your secure key is unvalid', $page);

    return false;
}

if (!Module::isInstalled($nts->name)) {
    echo $nts->l('Your module is not installed', $page);

    return false;
}

if (!Module::isEnabled($nts->name)) {
    echo $nts->l('Your module is not enabled', $page);

    return false;
}

$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

if (!Tools::isSubmit('id_shop_group') || !Tools::isSubmit('id_shop')) {
    echo $nts->l('The shop is not valid, please check the advanced automation for correct link', $page);

    return false;
}

$sent = true;
$id_shop_group = (int) Tools::getValue('id_shop_group');
$id_shop = (int) Tools::getValue('id_shop');
$a_config = NtsConfig::getConfig($id_shop, $id_shop_group, $id_lang);
$list_shops = [];
$date_file_name = date('Y.m.d');

$header_products = [
    $nts->l('Reference', $page),
    $nts->l('Name', $page),
    $nts->l('Quantity', $page),
    $nts->l('Ean13', $page),
    $nts->l('Active', $page),
];

$header_combinations = [
    $nts->l('Reference', $page),
    $nts->l('Name', $page),
    $nts->l('Quantity', $page),
    $nts->l('Ean13', $page),
    $nts->l('Active', $page),
];

$php_version = Tools::substr(phpversion(), 0, 3);

if (!$id_shop_group && !$id_shop) {
    // Context all
    $a_shops = Shop::getShops(true);

    foreach ($a_shops as $a_shop) {
        $list_shops[] = [
            'id_shop' => $a_shop['id_shop'],
            'id_shop_group' => $a_shop['id_shop_group'],
        ];
    }
} elseif ($id_shop_group && !$id_shop) {
    // Context group
    $a_shops = Shop::getShops(true, $id_shop_group);

    foreach ($a_shops as $a_shop) {
        $list_shops[] = [
            'id_shop' => $a_shop['id_shop'],
            'id_shop_group' => $a_shop['id_shop_group'],
        ];
    }
} else {
    // Context shop
    $list_shops = [
        [
            'id_shop' => $id_shop,
            'id_shop_group' => $id_shop_group,
        ],
    ];
}

foreach ($list_shops as $a_shop) {
    $a_shop['id_shop'] = (int) $a_shop['id_shop'];
    $a_shop['id_shop_group'] = (int) $a_shop['id_shop_group'];
    $a_config['email_alert_threshold'] = (int) $a_config['email_alert_threshold'];

    $o_shop_group = new ShopGroup($a_shop['id_shop_group']);
    $o_shop = new Shop($a_shop['id_shop']);

    // if quantities are shared between shops of the group
    if ($o_shop_group->share_stock) {
        $req_shop = ' AND sa.`id_shop_group` = ' . $a_shop['id_shop_group'] . ' AND sa.`id_shop` = 0';
    } else {
        $req_shop = ' AND sa.`id_shop` = ' . $a_shop['id_shop'] . ' AND sa.`id_shop_group` = 0';
    }

    // Product active status
    $where_active = '';

    if ($a_config['email_alert_active'] != NtsConfig::EMAIL_ALERT_ACTIVE_ALL) {
        $where_active = ' AND p.`active` = ' . (int) $a_config['email_alert_active'];
    }

    $list_products_os = Db::getInstance()->executeS('
        SELECT DISTINCT p.`reference`, IFNULL(pl.`name`, "") AS name, IFNULL(sa.`quantity`, 0) AS quantity,
            IFNULL(p.`ean13`, "") AS ean13, p.`active`,
            IFNULL(ps.`low_stock_threshold`, ' . $a_config['email_alert_threshold'] . ') AS email_alert_threshold
        FROM `' . _DB_PREFIX_ . 'product` p
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
            AND pl.`id_lang` = ' . $id_lang . ' AND pl.`id_shop` = ' . $a_shop['id_shop'] . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.`id_product` = p.`id_product` AND ps.`id_shop` = ' . $a_shop['id_shop'] . '
        LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = p.`id_product`
            ' . $req_shop . ' AND sa.`id_product_attribute` = 0
        WHERE 1 = 1
        ' . $where_active . '
        GROUP BY p.`id_product`
        HAVING quantity <= email_alert_threshold
        AND (
                SELECT count(pa.`id_product_attribute`)
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                WHERE pa.`id_product` = p.`id_product`
            ) <= 0
        ORDER BY p.`reference`, name
    ');

    $list_products_attribute_os = Db::getInstance()->executeS('
        SELECT DISTINCT IF(pa.`reference` IS NULL OR pa.`reference` = "", p.`reference`, pa.`reference`) AS reference,
            CONCAT(
                IFNULL(pl.`name`, ""), " - ", GROUP_CONCAT(
                    DISTINCT CONCAT(
                        IFNULL(agl.`name`, ""), " - ", IFNULL(al.`name`, "")
                    ) ORDER BY agl.`name`, al.`name` SEPARATOR ", "
                )
            ) AS name,
            IFNULL(sa.`quantity`, 0) AS quantity, IFNULL(pa.`ean13`, "") AS ean13, p.`active`,
            IFNULL(pas.`low_stock_threshold`, ' . $a_config['email_alert_threshold'] . ') AS email_alert_threshold
        FROM `' . _DB_PREFIX_ . 'product_attribute` pa
        JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac
            ON pac.`id_product_attribute` = pa.`id_product_attribute`
        JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas ON pas.`id_product_attribute` = pa.`id_product_attribute` AND pas.`id_shop` = ' . $a_shop['id_shop'] . '
        JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
        JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON al.`id_attribute` = a.`id_attribute`
            AND al.`id_lang` = ' . $id_lang . '
        JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON agl.`id_attribute_group` = a.`id_attribute_group`
            AND agl.`id_lang` = ' . $id_lang . '
        JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = pa.`id_product`
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = pa.`id_product`
            AND pl.`id_lang` = ' . $id_lang . ' AND pl.`id_shop` = ' . $a_shop['id_shop'] . '
        LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product_attribute` = pa.`id_product_attribute` ' . $req_shop . '
        WHERE 1 = 1
        ' . $where_active . '
        GROUP BY pa.`id_product_attribute`
        HAVING quantity <= email_alert_threshold
        ORDER BY reference, name
    ');

    if (!count($list_products_os) && !count($list_products_attribute_os) && !$a_config['email_alert_send_empty']) {
        continue;
    }

    $clean_shop_name = NtsApparatus::correctFileName($o_shop->name);
    $file_attachment = null;
    $included_list_txt = '';
    $included_list_html = '';
    $file_product_name = $clean_shop_name . '_alert_stock_products_' . $date_file_name;
    $file_combination_name = $clean_shop_name . '_alert_stock_combinations_' . $date_file_name;

    if ($a_config['email_alert_type'] == NtsConfig::EMAIL_ALERT_TYPE_EXCEL) {
        if (version_compare($php_version, '7.1', '<')) {// Phpspreadsheet is not compatible with PHP < 7.1
            $a_config['email_alert_type'] = NtsConfig::EMAIL_ALERT_TYPE_CSV;
        }
    }

    switch ($a_config['email_alert_type']) {
        case NtsConfig::EMAIL_ALERT_TYPE_INCLUDED:
            $included_list_txt = $nts->l('Products', $page) . "\n";

            $included_list_html = '<table style="text-align: center; width: 100%;">';
            $included_list_html .= '<tr>';
            $included_list_html .= '<th colspan="5">' . $nts->l('Products', $page) . '<th>';
            $included_list_html .= '</tr>';
            $included_list_html .= '<tr>';
            foreach ($header_products as $hd_prod) {
                $included_list_html .= '<th>' . $hd_prod . '</th>';
            }
            $included_list_html .= '</tr>';

            foreach ($list_products_os as $prod_infos) {
                $included_list_txt .= $prod_infos['reference'] . ' - ' . $prod_infos['name'] . $nts->l(':', $page) . ' ' . $prod_infos['quantity'] . "\n";

                $included_list_html .= '<tr>';
                $included_list_html .= '<td>' . $prod_infos['reference'] . '</td>';
                $included_list_html .= '<td>' . $prod_infos['name'] . '</td>';
                $included_list_html .= '<td>' . $prod_infos['quantity'] . '</td>';
                $included_list_html .= '<td>' . $prod_infos['ean13'] . '</td>';
                $included_list_html .= '<td>' . $prod_infos['active'] . '</td>';
                $included_list_html .= '</tr>';
            }

            $included_list_html .= '</table>';

            $included_list_txt .= "\n";
            $included_list_txt .= "\n";

            $included_list_html .= '<br/>';
            $included_list_html .= '<br/>';

            $included_list_txt .= $nts->l('Combinations', $page) . "\n";

            $included_list_html .= '<table style="text-align: center; width: 100%;">';
            $included_list_html .= '<tr>';
            $included_list_html .= '<th colspan="5">' . $nts->l('Combinations', $page) . '</th>';
            $included_list_html .= '</tr>';
            $included_list_html .= '<tr>';
            foreach ($header_combinations as $hd_comb) {
                $included_list_html .= '<th>' . $hd_comb . '</th>';
            }
            $included_list_html .= '</tr>';

            foreach ($list_products_attribute_os as $prod_attr_infos) {
                $included_list_txt .= $prod_attr_infos['reference'] . ' - ' . $prod_attr_infos['name'] . $nts->l(':', $page) . ' ' . $prod_attr_infos['quantity'] . "\n";

                $included_list_html .= '<tr>';
                $included_list_html .= '<td>' . $prod_attr_infos['reference'] . '</td>';
                $included_list_html .= '<td>' . $prod_attr_infos['name'] . '</td>';
                $included_list_html .= '<td>' . $prod_attr_infos['quantity'] . '</td>';
                $included_list_html .= '<td>' . $prod_attr_infos['ean13'] . '</td>';
                $included_list_html .= '<td>' . $prod_attr_infos['active'] . '</td>';
                $included_list_html .= '</tr>';
            }

            $included_list_html .= '</table>';

            break;
        case NtsConfig::EMAIL_ALERT_TYPE_CSV:
            $file_products = tmpfile();

            // Put the header
            fputcsv($file_products, $header_products, ';');

            // Put the data
            foreach ($list_products_os as $prod_infos) {
                fputcsv($file_products, [
                    $prod_infos['reference'],
                    $prod_infos['name'],
                    $prod_infos['quantity'],
                    $prod_infos['ean13'],
                    $prod_infos['active'],
                ], ';');
            }

            $content_products = Tools::file_get_contents(stream_get_meta_data($file_products)['uri']);

            fclose($file_products);

            $file_combinations = tmpfile();

            // Put the header
            fputcsv($file_combinations, $header_combinations, ';');

            // Put the data
            foreach ($list_products_attribute_os as $prod_attr_infos) {
                fputcsv($file_combinations, [
                    $prod_attr_infos['reference'],
                    $prod_attr_infos['name'],
                    $prod_attr_infos['quantity'],
                    $prod_attr_infos['ean13'],
                    $prod_attr_infos['active'],
                ], ';');
            }

            $content_combinations = Tools::file_get_contents(stream_get_meta_data($file_combinations)['uri']);

            fclose($file_combinations);

            $file_attachment = [
                [
                    'content' => $content_products,
                    'mime' => 'text/csv',
                    'name' => $file_product_name . '.csv',
                ],
                [
                    'content' => $content_combinations,
                    'mime' => 'text/csv',
                    'name' => $file_combination_name . '.csv',
                ],
            ];

            break;
        case NtsConfig::EMAIL_ALERT_TYPE_EXCEL:
            $physic_path_modules = realpath(_PS_ROOT_DIR_ . '/modules') . '/' . $nts->name . '/';

            if (version_compare(_PS_VERSION_, '1.7.6.0', '<') === true) {
                require_once $physic_path_modules . 'lib/phpspreadsheet-1.12.0/vendor/autoload.php';
            }

            $spreadsheet_product = new Spreadsheet();
            $sheet_product = $spreadsheet_product->getActiveSheet();

            $line_index_product = 1;
            $column_index_product = 1;

            // Put the header
            foreach ($header_products as $hd_prod) {
                $sheet_product->setCellValueByColumnAndRow($column_index_product, $line_index_product, $hd_prod);
                ++$column_index_product;
            }

            // Put the data
            foreach ($list_products_os as $prod_infos) {
                $column_index_product = 1;
                ++$line_index_product;

                foreach ($prod_infos as $prod_field) {
                    $sheet_product->setCellValueByColumnAndRow($column_index_product, $line_index_product, $prod_field);
                    ++$column_index_product;
                }
            }

            // Auto size all columns
            $highest_column_letter_product = $sheet_product->getHighestColumn();

            for ($column_letter_product = 'A'; $column_letter_product <= $highest_column_letter_product; ++$column_letter_product) {
                $sheet_product->getColumnDimension($column_letter_product)->setAutoSize(true);
            }

            $writer_product = new Xlsx($spreadsheet_product);

            $temp_file_product = tempnam($physic_path_modules, 'export_products');

            $writer_product->save($temp_file_product);

            $spreadsheet_product->disconnectWorksheets();
            unset($spreadsheet_product);

            $content_products = Tools::file_get_contents($temp_file_product);
            unlink($temp_file_product);

            $spreadsheet_combination = new Spreadsheet();
            $sheet_combination = $spreadsheet_combination->getActiveSheet();

            $line_index_combination = 1;
            $column_index_combination = 1;

            // Put the header
            foreach ($header_combinations as $hd_comb) {
                $sheet_combination->setCellValueByColumnAndRow($column_index_combination, $line_index_combination, $hd_comb);
                ++$column_index_combination;
            }

            // Put the data
            foreach ($list_products_attribute_os as $prod_attr_infos) {
                $column_index_combination = 1;
                ++$line_index_combination;

                foreach ($prod_attr_infos as $prod_attr_field) {
                    $sheet_combination->setCellValueByColumnAndRow($column_index_combination, $line_index_combination, $prod_attr_field);
                    ++$column_index_combination;
                }
            }

            // Auto size all columns
            $highest_column_letter_combination = $sheet_combination->getHighestColumn();

            for ($column_letter_combination = 'A'; $column_letter_combination <= $highest_column_letter_combination; ++$column_letter_combination) {
                $sheet_combination->getColumnDimension($column_letter_combination)->setAutoSize(true);
            }

            $writer_combination = new Xlsx($spreadsheet_combination);

            $temp_file_combination = tempnam($physic_path_modules, 'export_combinations');

            $writer_combination->save($temp_file_combination);

            $spreadsheet_combination->disconnectWorksheets();
            unset($spreadsheet_combination);

            $content_combinations = Tools::file_get_contents($temp_file_combination);
            unlink($temp_file_combination);

            $file_attachment = [
                [
                    'content' => $content_products,
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'name' => $file_product_name . '.xlsx',
                ],
                [
                    'content' => $content_combinations,
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'name' => $file_combination_name . '.csv',
                ],
            ];
    }

    $template_vars = [
        // '{email_alert_threshold}'   => $a_config['email_alert_threshold'],
        '{included_list_txt}' => $included_list_txt,
        '{included_list_html}' => $included_list_html,
    ];

    $iso = Language::getIsoById((int) $id_lang);

    $theme_path = _PS_THEME_DIR_;

    if (!NtsApparatus::checkFileExists($theme_path . 'modules/' . $nts->name . '/mails/' . $iso . '/alert_stock.html')
        && !NtsApparatus::checkFileExists($theme_path . 'modules/' . $nts->name . '/mails/' . $iso . '/alert_stock.txt')
        && !NtsApparatus::checkFileExists(_PS_MODULE_DIR_ . $nts->name . '/mails/' . $iso . '/alert_stock.html')
        && !NtsApparatus::checkFileExists(_PS_MODULE_DIR_ . $nts->name . '/mails/' . $iso . '/alert_stock.txt')
    ) {
        $id_lang = Language::getIdByIso('en');
    }

    if (!Mail::Send(
        $id_lang,
        'alert_stock',
        Mail::l('Stock alert for the shop', $id_lang) . ' ' . $o_shop->name,
        $template_vars,
        explode(';', $a_config['mail_stock_alert']),
        null,
        null,
        null,
        $file_attachment,
        null,
        dirname(__FILE__) . '/../mails/',
        true,
        $id_shop
    )) {
        $sent = false;
    }
}

if ($sent) {
    exit('OK');
} else {
    echo 'ERROR';
}
