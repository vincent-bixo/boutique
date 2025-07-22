<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class MdnProd extends Module
{
    public function __construct()
    {
        $this->name = 'mdnprod';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Vincent';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Divers overrides maison';
        $this->description = 'Divers overrides maison';
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function uninstall()
    {
        PrestaShopLogger::addLog('MDNPROD: désinstallation.', 1);

        // Supprimez les rôles d'autorisation liés à ce module
        $db = \Db::getInstance();
        $db->execute("DELETE FROM `" . _DB_PREFIX_ . "authorization_role` WHERE slug LIKE 'ROLE_MOD_MODULE_" . strtoupper($this->name) . "_%'");

        return parent::uninstall();
    }

    public function install()
    {
        if (!parent::install()) {
            PrestaShopLogger::addLog('MDNPROD: Échec de l’installation.', 3);
            return false;
        }
        PrestaShopLogger::addLog('MDNPROD: Installation ok.', 1);

        $this->clearCache();
    
        // Enregistrez les hooks nécessaires
//        $this->registerHook('displayOverrideTemplate'); // Exemple d'un hook associé
/*
        $hooks = ['actionDispatcherBefore'];
        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                PrestaShopLogger::addLog("MDNPROD: Échec d'enregistrement du hook $hook.", 3);
                return false;
            }
        }
*/

        PrestaShopLogger::addLog('MDNPROD: Module installé avec succès et hooks enregistrés.', 1);
        return true;
    }
        
    public function hookActionOverrideTemplate($params)
    {
        PrestaShopLogger::addLog('MDNPROD: Hook actionDispatcherBefore exécuté.', 1);
    
        $overridePath = _PS_MODULE_DIR_ . 'mdnprod/views/overrides/wkcharitydonation/';
//        $overridePath = _PS_MODULE_DIR_ . 'mdnprod/views/templates/wkcharitydonation/';
        $this->context->smarty->addTemplateDir($overridePath);
    
        PrestaShopLogger::addLog('MDNPROD: Chemin de surcharge ajouté via actionFrontControllerSetMedia : ' . $overridePath, 1);
    }

    private function clearCache()
    {
        // Vider le cache Smarty
        return $this->get('prestashop.adapter.legacy.context')->smarty->clearCompiledTemplate();
    }
/*    
    public function install()
    {
        return parent::install() && $this->registerHook('actionDispatcherBefore');
    }
    
    public function hookActionDispatcherBefore()
    {
        PrestaShopLogger::addLog('MDNHERE', 1);

        $path = _PS_MODULE_DIR_ . 'mdnprod/views/PrestaShopBundle';
        $this->context->smarty->addTemplateDir($path, 0);
        PrestaShopLogger::addLog('MDN: Chemin Twig injecté : ' . $path, 1);

        // Chemin de surcharge
        $overridePath = _PS_MODULE_DIR_ . 'mdnprod/views/overrides/wkcharitydonation/';
        $this->context->smarty->addTemplateDir($overridePath);
        // Log pour vérifier l'ajout
        PrestaShopLogger::addLog('MDN: Chemin de surcharge pour checkout-donation.tpl ajouté : ' . $overridePath, 1);

    }
*/
}
