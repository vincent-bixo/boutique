<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CrontabValidator
 * @package hollodotme\CrontabValidator
 */
class CrontabValidator
{
	/** @var string */
	private $expressionCheckRegExp;

	public function __construct()
	{
		$this->expressionCheckRegExp = $this->buildExpressionCheckRegExp();
	}

	/**
	 * @return string
	 */
	private function buildExpressionCheckRegExp()
	{
		$numbers = [
			'min' => '[0-5]?\d',
			'hour' => '[01]?\d|2[0-3]',
			'dayOfMonth' => '((0?[1-9]|[12]\d|3[01])W?|L|\?)',
			'month' => '0?[1-9]|1[012]|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec',
			'dayOfWeek' => '([0-7]|mon|tue|wed|thu|fri|sat|sun|\?)(L|\#[1-5])?',
		];

		$steps = [
			'min' => '(0?[1-9]|[1-5]\d)',
			'hour' => '(0?[1-9]|1\d|2[0-3])',
			'dayOfMonth' => '(0?[1-9]|[12]\d|3[01])',
			'month' => '(0?[1-9]|1[012])',
			'dayOfWeek' => '[1-7]',
		];

		$sections = [];
		foreach ( $numbers as $section => $number )
		{
			$step = $steps[ $section ];
			$range = "({$number})(-({$number})(/{$step})?)?";
			$sections[ $section ] = "\*(/{$step})?|{$number}(/{$step})?|{$range}(,{$range})*";
		}

		$joinedSections = '(' . implode( ')\s+(', $sections ) . ')';
		$replacements = '@reboot|@yearly|@annually|@monthly|@weekly|@daily|@midnight|@hourly';

		return "^\s*({$joinedSections}|({$replacements}))\s*$";
	}

	/**
	 * @param string $expression
	 *
	 * @return bool
	 */
	public function isExpressionValid( $expression )
	{
		return (bool)preg_match( "#{$this->expressionCheckRegExp}#i", $expression );
	}

	/**
	 * @param string $expression
	 *
	 * @throws InvalidExpressionException
	 */
	public function guardExpressionIsValid( $expression )
	{
		if ( !$this->isExpressionValid( $expression ) )
		{
			throw (new InvalidExpressionException())->withExpression( $expression );
		}
	}
}
