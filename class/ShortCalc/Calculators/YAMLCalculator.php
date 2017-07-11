<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;

class YAMLCalculator implements CalculatorInterface {
	public $name = null;
	public $settings = null;
	public $formula = null;
	public $formatter = null;

	public function __construct(String $name) {
		
	}

	public static function wpInit() {
	}

	public static function find(String $name) {
	}

	public function renderForm(String $view = null) {
		return $this->name;
	}

	public function renderResult(String $view = null) {
		return $this->name;
	}
}