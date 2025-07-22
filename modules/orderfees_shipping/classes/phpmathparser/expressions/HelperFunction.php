<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2020 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

namespace PHPMathParser\Expressions;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PHPMathParser\Stack;

class HelperFunction extends Operator
{
    const ARG_SEPARATOR = ';';
    
    public static $fns = array();
    
    protected $precedence = 10;

    public static function isFunction($value)
    {
        $function = \Tools::strtolower($value);
        
        return isset(self::$fns[$function]) || method_exists(__CLASS__, $function);
    }
    
    public function hasArg($stack)
    {
        $next = $stack->peek();
        
        if ($next && $next->render() === self::ARG_SEPARATOR) {
            return $stack->pop();
        }
        
        return false;
    }
    
    public function operate(Stack $stack)
    {
        $args = array();
        
        do {
            $value = $stack->pop()->operate($stack);
            
            if ($value !== self::ARG_SEPARATOR) {
                array_unshift($args, (float)$value);
            }
        } while ($this->hasArg($stack));
        
        $function = \Tools::strtolower($this->value);
        
        $result = new Number(call_user_func_array(isset(self::$fns[$function]) ? self::$fns[$function] : array(__CLASS__, $function), $args));
        
        return $result->operate($stack);
    }
    
    public static function price($price)
    {
        return \Tools::convertPrice($price);
    }
    
    public static function min(...$values)
    {
        return min($values);
    }
    
    public static function max(...$values)
    {
        return max(...$values);
    }
}
