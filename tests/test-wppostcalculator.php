<?php
use Symfony\Component\DomCrawler\Crawler;

class WPPostCalculator_Test extends WP_UnitTestCase {
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

	/**
	 * Call protected/private method of a class.
	 *
	 * @param string $className	 Name of class that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethodByClassName($className, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass($className);
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	function setUp() {

	}

	function tearDown() {
	}

	public function test_sanitizetext(){
		$result = ShortCalc\Calculators\WPPostCalculator::sanitizeText('     foo     ');
		$this->assertEquals(' foo ', $result);
	}

	public function test_registermetabox(){
		$plugin = ShortCalc\IoC::getPluginInstance();
		do_action('cmb2_admin_init');
		ob_start();
		$plugin->meta_box->show_form('shortcalc_calculator');
		$result = ob_get_clean();
		$crawler = new Crawler($result);

		$this->assertEquals(1, $crawler->filter('div#cmb2-metabox-shortcalc_calculator_mb')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_formula')->count());
		$this->assertEquals(1, $crawler->filter('select#shortcalc_formula_parser')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_result_decimal_sep')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_result_thousands_sep')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_result_prefix')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_result_postfix')->count());
		$this->assertEquals(1, $crawler->filter('div.cmb2-id-shortcalc-parameters')->count());
		$this->assertEquals(1, $crawler->filter('select#shortcalc_parameters_0_element')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_parameters_0_label')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_parameters_0_prefix')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_parameters_0_postfix')->count());
		$this->assertEquals(1, $crawler->filter('textarea#shortcalc_parameters_0_attributes')->count());
		$this->assertEquals(1, $crawler->filter('input#shortcalc_parameters_0_options_0')->count());
	}

	public function test_new_wppostcalculator(){
		$calc = new ShortCalc\Calculators\WPPostCalculator('foo');
		$this->assertInstanceOf('ShortCalc\Calculators\WPPostCalculator', $calc);
		$this->assertEquals('foo', $calc->name);
		$this->assertEquals(new \StdClass(), $calc->parameters);
		$this->assertEquals('.', $calc->resultDecimalSep);
		$this->assertEquals('', $calc->resultThousandsSep);
	}

	public function test_find(){
		$p = $this->factory->post->create(array(
			'post_type' => 'shortcalc_calculator',
			'post_title' => 'Foo WP Post',
			'post_name' => 'foo-wp-post',
			'meta_input' => array(
				'shortcalc_formula' => '{{a}}*{{b}}+{{c}}',
				'shortcalc_formula_parser' => 'HoaMath',
				'shortcalc_parameters' => array(
					array('name'=>'a', 'element'=>'input','label'=>'A','prefix'=>'','postfix'=>'','attributes'=>'required foo="bar"'),
					array('name'=>'b', 'element'=>'input','label'=>'B','prefix'=>'','postfix'=>'','attributes'=>'required'),
					array('name'=>'c', 'element'=>'input','label'=>'C','prefix'=>'','postfix'=>'','attributes'=>'required')
				)
			)
		));

		$calc = \ShortCalc\Calculators\WPPostCalculator::find('foo-wp-post');
		$this->assertInstanceOf('ShortCalc\Calculators\WPPostCalculator', $calc);
		$this->assertEquals('foo-wp-post', $calc->name);
		$this->assertInstanceOf('\StdClass', $calc->parameters);

		$this->assertInstanceOf('\StdClass', $calc->parameters->a);
		$this->assertEquals('A', $calc->parameters->a->label);
		$this->assertEquals('input', $calc->parameters->a->element);
		$this->assertEquals('', $calc->parameters->a->prefix);
		$this->assertEquals('', $calc->parameters->a->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->a->attributes);
		$this->assertTrue($calc->parameters->a->attributes->required);
		$this->assertEquals('bar', $calc->parameters->a->attributes->foo);
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
		$this->assertTrue($calc->parameters->b->attributes->required);
		$this->assertEquals('b', $calc->parameters->b->attributes->name);
		$this->assertEquals('', $calc->parameters->b->attributes->value);
		$this->assertEquals('text', $calc->parameters->b->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->b->attributes->id);

		$this->assertInstanceOf('\StdClass', $calc->parameters->c);
		$this->assertEquals('C', $calc->parameters->c->label);
		$this->assertEquals('input', $calc->parameters->c->element);
		$this->assertEquals('', $calc->parameters->c->prefix);
		$this->assertEquals('', $calc->parameters->c->postfix);
		$this->assertInstanceOf('\StdClass', $calc->parameters->c->attributes);
		$this->assertTrue($calc->parameters->c->attributes->required);
		$this->assertEquals('c', $calc->parameters->c->attributes->name);
		$this->assertEquals('', $calc->parameters->c->attributes->value);
		$this->assertEquals('text', $calc->parameters->c->attributes->type);
		$this->assertRegExp('/shortcalc_([0-9]*)/', $calc->parameters->c->attributes->id);

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

		$this->assertEquals('.', $calc->resultDecimalSep);
		$this->assertEquals('', $calc->resultThousandsSep);
		$this->assertEquals('{{a}}*{{b}}+{{c}}', $calc->formula);
	}

	public function test_find_no_params(){
		$p = $this->factory->post->create(array(
			'post_type' => 'shortcalc_calculator',
			'post_title' => 'Foo WP Post',
			'post_name' => 'bar-wp-post',
			'meta_input' => array(
				'shortcalc_formula' => '{{a}}*{{b}}+{{c}}',
				'shortcalc_formula_parser' => 'HoaMath',
				'shortcalc_parameters' => array(
					array(),
				)
			)
		));

		$calc = \ShortCalc\Calculators\WPPostCalculator::find('bar-wp-post');
		$this->assertInstanceOf('ShortCalc\Calculators\WPPostCalculator', $calc);
		$this->assertEquals('bar-wp-post', $calc->name);
		$this->assertInstanceOf('\StdClass', $calc->parameters);
	}

	public function test_find_result_settings(){
		$p = $this->factory->post->create(array(
			'post_type' => 'shortcalc_calculator',
			'post_title' => 'Foo WP Post',
			'post_name' => 'qux-wp-post',
			'meta_input' => array(
				'shortcalc_formula' => '{{a}}*{{b}}+{{c}}',
				'shortcalc_formula_parser' => 'HoaMath',
				'shortcalc_parameters' => array(
					array(),
				),
				'shortcalc_result_decimal_sep' => 'a',
				'shortcalc_result_thousands_sep' => 'b',
				'shortcalc_result_prefix' => 'c',
				'shortcalc_result_postfix' => 'd',
			)
		));

		$calc = \ShortCalc\Calculators\WPPostCalculator::find('qux-wp-post');
		$this->assertInstanceOf('ShortCalc\Calculators\WPPostCalculator', $calc);
		$this->assertEquals('qux-wp-post', $calc->name);
		$this->assertEquals('a', $calc->resultDecimalSep);
		$this->assertEquals('b', $calc->resultThousandsSep);
		$this->assertEquals('c', $calc->resultPrefix);
		$this->assertEquals('d', $calc->resultPostfix);
	}


}