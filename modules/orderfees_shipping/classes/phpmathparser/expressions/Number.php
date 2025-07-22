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
use PHPMathParser\TerminalExpression;

class Number extends TerminalExpression
{
    public function operate(Stack $stack)
    {
        (bool)$stack;
        
        return $this->value;
    }
}
