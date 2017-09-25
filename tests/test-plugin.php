<?php
class Plugin_Test extends WP_UnitTestCase {
	// build up
	function setUp(){
		add_filter( 'filesystem_method_file', array( $this, 'filter_abstraction_file' ) );
        add_filter( 'filesystem_method', array( $this, 'filter_fs_method' ) );
        WP_Filesystem();
	}

	function tearDown(){
		global $wp_filesystem;
		remove_filter( 'filesystem_method_file', array( $this, 'filter_abstraction_file' ) );
		remove_filter( 'filesystem_method', array( $this, 'filter_fs_method' ) );
		unset( $wp_filesystem );
		
		parent::tearDown();		
	}

	function filter_fs_method( $method ) {
		return 'MockFS';
	}

	function filter_abstraction_file( $file ) {
		return getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/mock-fs.php';
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

	public function test_install() {
		$plugin = ShortCalc\IoC::getPluginInstance();
		$result = $plugin->install();
		$this->assertNull($result);
	}

	public function test_uninstall() {
		$plugin = ShortCalc\IoC::getPluginInstance();
		$result = $plugin->uninstall();
		$this->assertNull($result);
	}

	public function test_shortcode(){
		$result = do_shortcode('[shortcalc_calculator name=pythagoras]');
		$expected = '/<form name="pythagoras" id="shortcalc-form-pythagoras"><label for="shortcalc_([0-9]*)">A<\/label><input required="1" id="shortcalc_([0-9]*)" name="a" value="" type="text"  \/><label for="shortcalc_([0-9]*)">B<\/label><input required="1" id="shortcalc_([0-9]*)" name="b" value="" type="text"  \/><label for="shortcalc_([0-9]*)"><\/label><input value="Find C" type="submit" id="shortcalc_([0-9]*)" name="submit"  \/><\/form><div id="shortcalc-form-result-pythagoras"><\/div><script type="text\/html" id="tmpl-calculator-result-pythagoras">   <p>{{data.result}}<\/p><\/script>/';
		$this->assertRegExp($expected, $result);
	}

	public function test_shortcode_with_params(){
		$result = do_shortcode('[shortcalc_calculator name=pythagoras param_a=1337]');
		$expected = '/<form name="pythagoras" id="shortcalc-form-pythagoras"><label for="shortcalc_([0-9]*)">A<\/label><input required="1" id="shortcalc_([0-9]*)" name="a" value="1337" type="text"  \/><label for="shortcalc_([0-9]*)">B<\/label><input required="1" id="shortcalc_([0-9]*)" name="b" value="" type="text"  \/><label for="shortcalc_([0-9]*)"><\/label><input value="Find C" type="submit" id="shortcalc_([0-9]*)" name="submit"  \/><\/form><div id="shortcalc-form-result-pythagoras"><\/div><script type="text\/html" id="tmpl-calculator-result-pythagoras">   <p>{{data.result}}<\/p><\/script>/';
		$this->assertRegExp($expected, $result);
	}

	public function test_get_calculator_result(){
		$_POST['calculator_name'] = 'pythagoras';
		$plugin = ShortCalc\IoC::getPluginInstance();
		_disable_wp_die();
		ob_start();
		$plugin->getCalculatorResult();
		$result = ob_get_clean();
		_enable_wp_die();
		$expected = '360.79366402419';
		$this->assertEquals($expected, $result);
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
}