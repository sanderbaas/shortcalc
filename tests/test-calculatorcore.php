<?php
class CalculatorCore_Test extends WP_UnitTestCase {
	// build up
	function setUp(){

	}

	public function test_new_calculatorcore(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$this->assertInstanceOf('ShortCalc\Calculators\CalculatorCore', $calc);
		$this->assertEquals('foo', $calc->name);
		$this->assertEquals(new \StdClass(), $calc->parameters);
		$this->assertEquals('.', $calc->resultDecimalSep);
		$this->assertEquals('', $calc->resultThousandsSep);
	}

	public function test_find(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$result = $calc::find('foo');
		$this->assertFalse($result);
	}

	public function test_render_form(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');		
		$result = $calc->renderForm(array());
		$expected = '<form name="foo" id="shortcalc-form-foo"></form><div id="shortcalc-form-result-foo"></div><script type="text/html" id="tmpl-calculator-result-foo">   <p>{{data.result}}</p></script>';
		$this->assertEquals($expected,$result);
	}

	function tearDown(){

	}
}