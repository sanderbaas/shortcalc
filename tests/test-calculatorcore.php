<?php
class CalculatorCore_Test extends WP_UnitTestCase {
	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod(&$object, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

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

	public function test_render_form_no_attributes(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$result = $calc->renderForm(array());
		$expected = '<form name="foo" id="shortcalc-form-foo"></form><div id="shortcalc-form-result-foo"></div><script type="text/html" id="tmpl-calculator-result-foo">   <p>{{data.result}}</p></script>';
		$this->assertEquals($expected,$result);
	}

	public function test_render_form_no_attributes_result_prefix_postfix(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->resultPrefix = 'prefix ';
		$calc->resultPostfix = ' postfix';
		$result = $calc->renderForm(array());
		$expected = '<form name="foo" id="shortcalc-form-foo"></form><div id="shortcalc-form-result-foo"></div><script type="text/html" id="tmpl-calculator-result-foo">   <p>prefix {{data.result}} postfix</p></script>';
		$this->assertEquals($expected,$result);
	}

	public function test_render_form_with_attributes(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$json = '{
			"a": {
				"label": "A",
				"attributes": {
					"required": true
				}
			},
			"b": {
				"label": "B",
				"attributes": {
					"required": true
				}
			},
			"submit": {
				"attributes": {
					"value": "Find C",
					"type": "submit"
				}
			}
		}';
		$parameters = json_decode($json);
		$this->invokeMethod($calc, 'assignParameters', array($parameters));
		$result = $calc->renderForm(array());
		$expected = '/<form name="foo" id="shortcalc-form-foo"><label for="shortcalc_([0-9]*)">A<\/label><input required="1" id="shortcalc_([0-9]*)" name="a" value="" type="text"  \/><label for="shortcalc_([0-9]*)">B<\/label><input required="1" id="shortcalc_([0-9]*)" name="b" value="" type="text"  \/><label for="shortcalc_([0-9]*)"><\/label><input value="Find C" type="submit" id="shortcalc_([0-9]*)" name="submit"  \/><\/form><div id="shortcalc-form-result-foo"><\/div><script type="text\/html" id="tmpl-calculator-result-foo">   <p>{{data.result}}<\/p><\/script>/';
		$this->assertRegExp($expected, $result);
	}

	public function test_render_form_with_attributes_and_overrides(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$json = '{
			"a": {
				"label": "A",
				"attributes": {
					"required": true
				}
			},
			"b": {
				"label": "B",
				"attributes": {
					"required": true
				}
			},
			"submit": {
				"attributes": {
					"value": "Find C",
					"type": "submit"
				}
			}
		}';
		$parameters = json_decode($json);
		$overrides = array("a" => "3.14", "b" => "1337");
		$this->invokeMethod($calc, 'assignParameters', array($parameters));
		$result = $calc->renderForm($overrides);
		$expected = '/<form name="foo" id="shortcalc-form-foo"><label for="shortcalc_([0-9]*)">A<\/label><input required="1" id="shortcalc_([0-9]*)" name="a" value="3.14" type="text"  \/><label for="shortcalc_([0-9]*)">B<\/label><input required="1" id="shortcalc_([0-9]*)" name="b" value="1337" type="text"  \/><label for="shortcalc_([0-9]*)"><\/label><input value="Find C" type="submit" id="shortcalc_([0-9]*)" name="submit"  \/><\/form><div id="shortcalc-form-result-foo"><\/div><script type="text\/html" id="tmpl-calculator-result-foo">   <p>{{data.result}}<\/p><\/script>/';
		$this->assertRegExp($expected, $result);
	}

	function tearDown(){

	}
}