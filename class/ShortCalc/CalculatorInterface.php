<?php
namespace ShortCalc;

interface CalculatorInterface {
	public function __construct(String $name);
	public static function wpInit();
	public static function find(String $name);
	public function renderForm(Array $params);
	public function renderResult();
}