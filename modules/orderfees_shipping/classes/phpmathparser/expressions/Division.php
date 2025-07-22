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

class Division extends Operator
{
    protected $precedence = 5;

    public function operate(Stack $stack)
    {
        $left = $stack->pop()->operate($stack);
        $right = $stack->pop()->operate($stack);

        return $right / $left;
    }
}
