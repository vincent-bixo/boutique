<?php
/**
 *  Order Fees Formula
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class TranslatableException extends Exception
{
    public function __construct($message, $sprintf = null, $code = 0, $previous = null)
    {
        $name = 'orderfees_shipping';
        
        if ($sprintf != null && !is_array($sprintf)) {
            $sprintf = array($sprintf);
        }
        
        $message_translated = Translate::getModuleTranslation($name, $message, $name, $sprintf);
        
        parent::__construct($message_translated, $code, $previous);
    }
}
