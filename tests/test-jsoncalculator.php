<?php
class JsonCalculator_Test extends WP_UnitTestCase {
	function setUp() {
	}

	function tearDown() {
	}

	public function test_new_jsoncalculator(){
		$calc = new ShortCalc\Calculators\JsonCalculator('foo');
		$this->assertInstanceOf('ShortCalc\Calculators\JsonCalculator', $calc);
		$this->assertEquals('foo', $calc->name);
		$this->assertEquals(new \StdClass(), $calc->parameters);
		$this->assertEquals('.', $calc->resultDecimalSep);
		$this->assertEquals('', $calc->resultThousandsSep);
	}

	public function test_find(){
		$calc = \ShortCalc\Calculators\JsonCalculator::find('pythagoras');
		$this->assertInstanceOf('ShortCalc\Calculators\JsonCalculator', $calc);
		$this->assertEquals('pythagoras', $calc->name);
		$this->assertInstanceOf('\StdClass', $calc->parameters);
		$this->assertEquals('.', $calc->resultDecimalSep);
		$this->assertEquals('', $calc->resultThousandsSep);
	}
}