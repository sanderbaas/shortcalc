<?php
class IoC_Test extends WP_UnitTestCase {
	// build up
	function setUp(){

	}

	public function test_get_plugin_instance(){
		$plugin = ShortCalc\IoC::getPluginInstance();
		$this->assertInstanceOf('ShortCalc\Plugin', $plugin);
		$plugin2 = ShortCalc\IoC::getPluginInstance();
		$this->assertSame($plugin, $plugin2);
	}

	public function test_find_calculator(){
		$plugin = ShortCalc\IoC::getPluginInstance();
		$plugin->init();
		$calculator = ShortCalc\IoC::findCalculator('pythagoras');
		$this->assertInstanceOf('ShortCalc\Calculators\JsonCalculator', $calculator);
		$this->assertEquals($calculator->name, 'pythagoras');
	}

	public function test_find_calculator_fail(){
		$plugin = ShortCalc\IoC::getPluginInstance();
		$plugin->init();
		$calculator = ShortCalc\IoC::findCalculator('nonexistent');
		$this->assertFalse($calculator);
	}

	public function test_new_calculator(){
		$calculator = ShortCalc\IoC::newCalculator('calc1','ShortCalc\Calculators\WPPostCalculator');
		$this->assertInstanceOf('ShortCalc\Calculators\WPPostCalculator', $calculator);
	}

	/**
     * @expectedException        Error
     * @expectedExceptionMessage Class 'ShortCalc\Calculators\nonexistent' not found
     */
	public function test_new_calculator_fail(){
		$calculator = ShortCalc\IoC::newCalculator('calc1','ShortCalc\Calculators\nonexistent');
	}

	public function test_new_formula_parser(){
		$fParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$this->assertInstanceOf('ShortCalc\FormulaParsers\HoaMath', $fParser);
	}

	/**
     * @expectedException        Error
     * @expectedExceptionMessage Class '\ShortCalc\FormulaParsers\nonexistent' not found
     */
	public function test_new_formula_parser_fail(){
		$fParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\nonexistent');
	}

	function tearDown(){

	}
}