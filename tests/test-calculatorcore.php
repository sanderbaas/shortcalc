<?php
class CalculatorCore_Test extends WP_UnitTestCase {
	function setUp() {
	}

	function tearDown() {
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
		TestHelper::invokeMethod($calc, 'assignParameters', array($parameters));
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
		TestHelper::invokeMethod($calc, 'assignParameters', array($parameters));
		$result = $calc->renderForm($overrides);
		$expected = '/<form name="foo" id="shortcalc-form-foo"><label for="shortcalc_([0-9]*)">A<\/label><input required="1" id="shortcalc_([0-9]*)" name="a" value="3.14" type="text"  \/><label for="shortcalc_([0-9]*)">B<\/label><input required="1" id="shortcalc_([0-9]*)" name="b" value="1337" type="text"  \/><label for="shortcalc_([0-9]*)"><\/label><input value="Find C" type="submit" id="shortcalc_([0-9]*)" name="submit"  \/><\/form><div id="shortcalc-form-result-foo"><\/div><script type="text\/html" id="tmpl-calculator-result-foo">   <p>{{data.result}}<\/p><\/script>/';
		$this->assertRegExp($expected, $result);
	}

	public function test_aggregate_attributes(){
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
		$result = TestHelper::invokeMethod($calc, 'aggregateAttributes', array($parameters));

		$expected = json_decode($json);
		$expected->a->allAttributes = 'required="1" ';
		$expected->b->allAttributes = 'required="1" ';
		$expected->submit->allAttributes = 'value="Find C" type="submit" ';

		$this->assertEquals($expected, $result);
	}

	public function test_aggregate_attributes_empty_attributes(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$json = '{
			"a": {
				"label": "A",
				"attributes": {}
			},
			"b": {
				"label": "B",
				"attributes": {}
			},
			"submit": {
				"attributes": {}
			}
		}';
		$parameters = json_decode($json);
		$result = TestHelper::invokeMethod($calc, 'aggregateAttributes', array($parameters));

		$expected = json_decode($json);
		$expected->a->allAttributes = '';
		$expected->b->allAttributes = '';
		$expected->submit->allAttributes = '';

		$this->assertEquals($expected, $result);
	}

	public function test_aggregate_attributes_no_parameters(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$json = '{}';
		$parameters = json_decode($json);
		$result = TestHelper::invokeMethod($calc, 'aggregateAttributes', array($parameters));

		$expected = json_decode($json);

		$this->assertEquals($expected, $result);
	}

	public function test_create_parameter_only_name(){
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'createParameter', array('foo'));
		$this->assertInstanceOf('\StdClass', $result);
		$this->assertEquals('input', $result->element);
		$this->assertEquals('', $result->prefix);
		$this->assertEquals('', $result->postfix);
		$this->assertEquals('foo', $result->label);
		$this->assertInstanceOf('\StdClass', $result->attributes);
		$this->assertTrue($result->attributes->required);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $result->attributes->id);
		$this->assertEquals('foo', $result->attributes->name);
		$this->assertEquals('text', $result->attributes->type);
		$this->assertEquals('', $result->attributes->value);
	}

	public function test_create_parameter_submit(){
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'createParameter', array('foo','input','submit'));
		$this->assertInstanceOf('\StdClass', $result);
		$this->assertEquals('input', $result->element);
		$this->assertEquals('', $result->prefix);
		$this->assertEquals('', $result->postfix);
		$this->assertEquals('', $result->label);
		$this->assertInstanceOf('\StdClass', $result->attributes);
		$this->assertNull($result->attributes->required);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $result->attributes->id);
		$this->assertEquals('foo', $result->attributes->name);
		$this->assertEquals('submit', $result->attributes->type);
		$this->assertEquals('', $result->attributes->value);
	}

	public function test_create_parameter_params(){
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'createParameter', array('foo','bar','quz','val'));
		$this->assertInstanceOf('\StdClass', $result);
		$this->assertEquals('bar', $result->element);
		$this->assertEquals('', $result->prefix);
		$this->assertEquals('', $result->postfix);
		$this->assertEquals('foo', $result->label);
		$this->assertInstanceOf('\StdClass', $result->attributes);
		$this->assertTrue($result->attributes->required);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $result->attributes->id);
		$this->assertEquals('foo', $result->attributes->name);
		$this->assertEquals('quz', $result->attributes->type);
		$this->assertEquals('val', $result->attributes->value);
	}

	public function test_assign_parameters(){
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
		TestHelper::invokeMethod($calc, 'assignParameters', array($parameters));

		$this->assertInstanceOf('\StdClass', $calc->parameters);
		$this->assertInstanceOf('\StdClass', $calc->parameters->a);
		$this->assertEquals('A', $calc->parameters->a->label);
		$this->assertEquals('input', $calc->parameters->a->element);
		$this->assertEquals('', $calc->parameters->a->prefix);
		$this->assertEquals('', $calc->parameters->a->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->a->attributes);
		$this->assertTrue($calc->parameters->a->attributes->required);
		$this->assertEquals('a', $calc->parameters->a->attributes->name);
		$this->assertEquals('', $calc->parameters->a->attributes->value);
		$this->assertEquals('text', $calc->parameters->a->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->a->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->b);
		$this->assertEquals('B', $calc->parameters->b->label);
		$this->assertEquals('input', $calc->parameters->b->element);
		$this->assertEquals('', $calc->parameters->b->prefix);
		$this->assertEquals('', $calc->parameters->b->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->b->attributes);
		$this->assertTrue($calc->parameters->a->attributes->required);
		$this->assertEquals('b', $calc->parameters->b->attributes->name);
		$this->assertEquals('', $calc->parameters->b->attributes->value);
		$this->assertEquals('text', $calc->parameters->b->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->b->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->submit);
		$this->assertEquals('', $calc->parameters->submit->label);
		$this->assertEquals('input', $calc->parameters->submit->element);
		$this->assertEquals('', $calc->parameters->submit->prefix);
		$this->assertEquals('', $calc->parameters->submit->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->submit->attributes);
		$this->assertNull($calc->parameters->submit->attributes->required);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->name);
		$this->assertEquals('Find C', $calc->parameters->submit->attributes->value);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->submit->attributes->id);
	}

	public function test_assign_parameters_add_submit(){
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
			}
		}';
		$parameters = json_decode($json);
		TestHelper::invokeMethod($calc, 'assignParameters', array($parameters));

		$this->assertInstanceOf('\StdClass', $calc->parameters);

		$this->assertInstanceOf('\StdClass', $calc->parameters->submit);
		$this->assertEquals('', $calc->parameters->submit->label);
		$this->assertEquals('input', $calc->parameters->submit->element);
		$this->assertEquals('', $calc->parameters->submit->prefix);
		$this->assertEquals('', $calc->parameters->submit->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->submit->attributes);
		$this->assertNull($calc->parameters->submit->attributes->required);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->name);
		$this->assertEquals('Calculate', $calc->parameters->submit->attributes->value);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->submit->attributes->id);
	}

	public function test_assign_parameters_add_label(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$json = '{
			"a": {
				"label": "",
				"attributes": {
					"required": true
				}
			}
		}';
		$parameters = json_decode($json);
		TestHelper::invokeMethod($calc, 'assignParameters', array($parameters));

		$this->assertEquals('a', $calc->parameters->a->label);
	}


	public function test_assign_parameters_from_formula(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}+{{b}}';

		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));

		$this->assertInstanceOf('\StdClass', $calc->parameters);

		$this->assertInstanceOf('\StdClass', $calc->parameters->a);
		$this->assertEquals('a', $calc->parameters->a->label);
		$this->assertEquals('input', $calc->parameters->a->element);
		$this->assertEquals('', $calc->parameters->a->prefix);
		$this->assertEquals('', $calc->parameters->a->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->a->attributes);
		$this->assertTrue($calc->parameters->a->attributes->required);
		$this->assertEquals('a', $calc->parameters->a->attributes->name);
		$this->assertEquals('', $calc->parameters->a->attributes->value);
		$this->assertEquals('text', $calc->parameters->a->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->a->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->b);
		$this->assertEquals('b', $calc->parameters->b->label);
		$this->assertEquals('input', $calc->parameters->b->element);
		$this->assertEquals('', $calc->parameters->b->prefix);
		$this->assertEquals('', $calc->parameters->b->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->b->attributes);
		$this->assertTrue($calc->parameters->a->attributes->required);
		$this->assertEquals('b', $calc->parameters->b->attributes->name);
		$this->assertEquals('', $calc->parameters->b->attributes->value);
		$this->assertEquals('text', $calc->parameters->b->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->b->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->submit);
		$this->assertEquals('', $calc->parameters->submit->label);
		$this->assertEquals('input', $calc->parameters->submit->element);
		$this->assertEquals('', $calc->parameters->submit->prefix);
		$this->assertEquals('', $calc->parameters->submit->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->submit->attributes);
		$this->assertNull($calc->parameters->submit->attributes->required);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->name);
		$this->assertEquals('Calculate', $calc->parameters->submit->attributes->value);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->submit->attributes->id);
	}

	function test_merge_parameters(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}+{{b}}';

		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));

		$params = array("a" => "3.14", "b" => "1337");
		TestHelper::invokeMethod($calc, 'mergeParameters', array($calc->parameters, $params));

		$this->assertInstanceOf('\StdClass', $calc->parameters);

		$this->assertInstanceOf('\StdClass', $calc->parameters->a);
		$this->assertEquals('a', $calc->parameters->a->label);
		$this->assertEquals('input', $calc->parameters->a->element);
		$this->assertEquals('', $calc->parameters->a->prefix);
		$this->assertEquals('', $calc->parameters->a->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->a->attributes);
		$this->assertTrue($calc->parameters->a->attributes->required);
		$this->assertEquals('a', $calc->parameters->a->attributes->name);
		$this->assertEquals('3.14', $calc->parameters->a->attributes->value);
		$this->assertEquals('text', $calc->parameters->a->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->a->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->b);
		$this->assertEquals('b', $calc->parameters->b->label);
		$this->assertEquals('input', $calc->parameters->b->element);
		$this->assertEquals('', $calc->parameters->b->prefix);
		$this->assertEquals('', $calc->parameters->b->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->b->attributes);
		$this->assertTrue($calc->parameters->b->attributes->required);
		$this->assertEquals('b', $calc->parameters->b->attributes->name);
		$this->assertEquals('1337', $calc->parameters->b->attributes->value);
		$this->assertEquals('text', $calc->parameters->b->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->b->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->submit);
		$this->assertEquals('', $calc->parameters->submit->label);
		$this->assertEquals('input', $calc->parameters->submit->element);
		$this->assertEquals('', $calc->parameters->submit->prefix);
		$this->assertEquals('', $calc->parameters->submit->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->submit->attributes);
		$this->assertNull($calc->parameters->submit->attributes->required);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->name);
		$this->assertEquals('Calculate', $calc->parameters->submit->attributes->value);
		$this->assertEquals('submit', $calc->parameters->submit->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->submit->attributes->id);
	}

	function test_merge_parameters_non_existing(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}+{{b}}';

		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));

		$params = array("x" => "3.14", "y" => "1337");
		TestHelper::invokeMethod($calc, 'mergeParameters', array($calc->parameters, $params));

		$this->assertInstanceOf('\StdClass', $calc->parameters);
		$this->assertNull($calc->parameters->x);
		$this->assertNull($calc->parameters->y);
		$this->assertEquals('', $calc->parameters->a->attributes->value);
		$this->assertEquals('', $calc->parameters->b->attributes->value);
	}

	function test_merge_parameters_param_no_attributes(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}+{{b}}';

		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));

		$params = array("a" => "3.14", "b" => "1337");
		$calcParams = (object) [
			'a' => (object) [
				'element' => 'input',
				'prefix' => '',
				'postfix' => '',
				'label' => ''
			]
		];
		TestHelper::invokeMethod($calc, 'mergeParameters', array($calcParams, $params));

		$this->assertInstanceOf('\StdClass', $calc->parameters);
		$this->assertEquals('3.14', $calc->parameters->a->attributes->value);
	}

	function test_merge_parameters_non_string(){
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}+{{b}}';

		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));

		$params = array("a" => 3.14, "b" => 1337);
		TestHelper::invokeMethod($calc, 'mergeParameters', array($calc->parameters, $params));

		$this->assertInstanceOf('\StdClass', $calc->parameters);
		$this->assertEquals('3.14', $calc->parameters->a->attributes->value);
		$this->assertEquals('1337', $calc->parameters->b->attributes->value);
	}

	function test_render_result(){
		$_POST['parameters']['a'] = '2';
		$_POST['parameters']['b'] = '3';
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}*{{b}}';
		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));
		$this->expectOutputString('6');
		_disable_wp_die();
		$calc->renderResult();
		_enable_wp_die();
	}

	function test_render_result_separators(){
		$_POST['parameters']['a'] = '200.22';
		$_POST['parameters']['b'] = '300.14';
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}*{{b}}';
		$calc->resultDecimalSep = ',';
		$calc->resultThousandsSep = '.';
		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));
		$this->expectOutputString('60.094,0308');
		_disable_wp_die();
		$calc->renderResult();
		_enable_wp_die();
	}

	function test_render_result_decimal_correction(){
		$_POST['parameters']['a'] = '200.22';
		$_POST['parameters']['b'] = '300,14';
		$calc = new ShortCalc\Calculators\CalculatorCore('foo');
		$calc->formulaParser = ShortCalc\IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\HoaMath');
		$calc->formula = '{{a}}*{{b}}';
		$calc->resultDecimalSep = ',';
		$calc->resultThousandsSep = '.';
		TestHelper::invokeMethod($calc, 'assignParameters', array(array()));
		$this->expectOutputString('60.094,0308');
		_disable_wp_die();
		$calc->renderResult();
		_enable_wp_die();
	}

	function test_format_parameter_value(){
		$value = '1,2';
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'formatParameterValue', array($value));
		$this->assertEquals(1.2, $result);

		$value = '1.000.000,2000000';
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'formatParameterValue', array($value));
		$this->assertEquals(1000000.2, $result);

		$value = '1,000,000,2';
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'formatParameterValue', array($value));
		$this->assertEquals(1000000.2, $result);

		$value = '1.000.000,2';
		$result = TestHelper::invokeMethodByClassName('ShortCalc\Calculators\CalculatorCore', 'formatParameterValue', array($value));
		$this->assertEquals(1000000.2, $result);
	}
}