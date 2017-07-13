<?php
namespace ShortCalc\FormulaParsers;
use \ShortCalc\FormulaParserInterface;
use \Rezzza\Formulate\Formula;

class HoaMath implements FormulaParserInterface {
	private $formula;

	public function __construct() {}

	public function setFormula($formula) {
		$formula = new Formula($formula);
		$this->formula = (string)$formula;
	}

	public function setParameter(String $key, $value) {
		$this->formula->setParameter($key, $value);
	}

	public static function extractParameters($formula) {
		$formula = (string)$formula;
		preg_match_all('/\{\{([^\}])*\}\}/', $formula, $matches);
		return $matches[1];
	}

	public function getResult() {
		$this->formula->setIsCalculable(true);
		return $this->formula->render();
	}
}