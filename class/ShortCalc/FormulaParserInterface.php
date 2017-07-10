<?php
namespace ShortCalc;

interface FormulaParserInterface {
	public function __construct();
	public function setFormula($formula);
	public function setParameter(String $key, $value);
	public function getResult();
}