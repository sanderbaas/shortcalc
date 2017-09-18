<?php
class JsonCalculator_Test extends WP_UnitTestCase {
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