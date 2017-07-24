<?php
namespace ShortCalc\FormulaParsers;
use \ShortCalc\FormulaParserInterface;
use \Rezzza\Formulate\Formula;

/**
 * Formula parser for HoaMath formula's.
 **/
class HoaMath implements FormulaParserInterface {
	/** @var \Rezzza\Formulare\Formula Formula being parsed. **/
	private $formula;

	/**
	 * Constructor of this class.
	 **/
	public function __construct() {}

	/**
	 * Register the formula to the parser.
	 *
	 * @param mixed Formula to work with, is parsed to a string.
	 *
	 * @return void
	 **/
	public function setFormula($formula) {
		$formula = (string)$formula;
		$formula = new Formula($formula);
		$this->formula = $formula;
	}

	/**
	 * Register a parameter to the parser.
	 *
	 * @param string $key The name of the parameter as it appears in the
	 * formula.
	 * @param mixed $value The value of the parameter.
	 *
	 * @return void
	 **/
	public function setParameter(String $key, $value) {
		$this->formula->setParameter($key, $value);
	}

	/**
	 * Extracts the parameters from the registered formula.
	 *
	 * @param mixed Formula to extract parameters from, is parsed to a string.
	 *
	 * @return array Returns an array with the parameters in the formula.
	 **/
	public static function extractParameters($formula) {
		$formula = (string)$formula;
		preg_match_all('/\{\{([^\}]*)\}\}/', $formula, $matches);
		return $matches[1];
	}

	/**
	 * Calculates the result of the calculation and returns it.
	 *
	 * @return string Result of the calculation.
	 **/
	public function getResult() {
		$this->formula->setIsCalculable(true);
		return $this->formula->render();
	}
}