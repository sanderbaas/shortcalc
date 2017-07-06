<?php
namespace ShortCalc\FormulaParsers;
use \ShortCalc\FormulaParserInterface;
use \Rezzza\Formulate\Formula;

class HoaMath implements FormulaParserInterface {
	public $name;
	private $formula;

	public function __construct() {
		
	}

	public function setFormula($formula) {
		$formula = new Formula($formula);
		$this->formula = $formula;
	}

	public function setParameter(String $key, $value) {
		$this->formula->setParameter($key, $value);
	}

	public function getResult() {
		$this->formula->setIsCalculable(true);
		return $this->formula->render();
	}
}