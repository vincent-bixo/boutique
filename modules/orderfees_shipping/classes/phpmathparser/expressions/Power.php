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

class Power extends Operator
{
    protected $precedence = 6;

    public function operate(Stack $stack)
    {
        $right = $stack->pop()->operate($stack);
        $left = $stack->pop()->operate($stack);

        return pow($left, $right);
    }
}
