<?php
/**
 * The PHP Math Parser library
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace PHPMathParser\Expressions;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PHPMathParser\Stack;

class MathFunction extends Operator
{
    protected $precedence = 10;
    
    protected static $functions = array(
        'ABS',
        'COS', 'COSH', 'SIN', 'SINH', 'TAN', 'TANH', 'ACOS', 'ACOSH', 'ASIN', 'ASINH', 'ATAN', 'ATAN2', 'ATANH',
        'DEG2GRAD', 'RAD2DEG', 'PI',
        'CEIL', 'FLOOR', 'ROUND', 'SQRT', 'LOG10'
    );
    
    public static function isFunction($value)
    {
        return in_array($value, self::$functions);
    }
    
    public function operate(Stack $stack)
    {
        $value = $stack->pop()->operate($stack);
        
        $function = \Tools::strtolower($this->value);
        
        $result = new Number($function($value));
        
        return $result->operate($stack);
    }
}
