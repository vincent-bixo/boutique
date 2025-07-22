<?php
/**
 * The PHP Math Parser library
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace PHPMathParser;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PHPMathParser\Expressions\Addition;
use PHPMathParser\Expressions\Division;
use PHPMathParser\Expressions\Multiplication;
use PHPMathParser\Expressions\Number;
use PHPMathParser\Expressions\Parenthesis;
use PHPMathParser\Expressions\Power;
use PHPMathParser\Expressions\Subtraction;
use PHPMathParser\Expressions\Unary;
use PHPMathParser\Expressions\MathFunction;
use PHPMathParser\Expressions\HelperFunction;

abstract class TerminalExpression
{
    protected $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function factory($value)
    {
        if (is_object($value) && $value instanceof self) {
            return $value;
        } elseif (is_numeric($value) || $value == ';') {
            return new Number($value);
        } elseif ($value == 'u') {
            return new Unary($value);
        } elseif ($value == '+') {
            return new Addition($value);
        } elseif ($value == '-') {
            return new Subtraction($value);
        } elseif ($value == '*') {
            return new Multiplication($value);
        } elseif ($value == '/') {
            return new Division($value);
        } elseif (in_array($value, array('(', ')'))) {
            return new Parenthesis($value);
        } elseif ($value == '^') {
            return new Power($value);
        } elseif (MathFunction::isFunction($value)) {
            return new MathFunction($value);
        } elseif (HelperFunction::isFunction($value)) {
            return new HelperFunction($value);
        }
        throw new \TranslatableException('Undefined Value "%s"', $value);
    }

    abstract public function operate(Stack $stack);

    public function isOperator()
    {
        return false;
    }

    public function isUnary()
    {
        return false;
    }

    public function isParenthesis()
    {
        return false;
    }

    public function isNoOp()
    {
        return false;
    }

    public function render()
    {
        return $this->value;
    }
}
