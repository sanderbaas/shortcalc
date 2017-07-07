<?php
namespace ShortCalc;

class Plugin {
	private $plugin_slug = null;
	public $implementations = array();

	/**
	 * Constructor for Plugin class.
	 * @param String $slug Slug to use for textdomain etc
	 **/
	public function __construct($slug) {
		$this->plugin_slug = $slug;
		add_action('init', array($this,'init'));
		add_action('wp_ajax_get_calculator_result', array($this, 'getCalculatorResult'));
		add_action('wp_ajax_nopriv_get_calculator_result', array($this, 'getCalculatorResult'));
	}

	public function registerImplementations($implementations) {
		foreach ($implementations as $key => $classNames) {
			$this->implementations[$key] = $classNames;
		}
	}

	public function init() {
		wp_enqueue_script('shortcalc-js', plugins_url("../../js/shortcalc.js", __FILE__) ,array('jquery','wp-util'));
		$ajax_object = array(
			'ajax_url' => admin_url('admin-ajax.php')
		);
		wp_localize_script( 'shortcalc-js', 'ajax_object', $ajax_object);

		$this->loadPluginTextdomain();
		$this->registerPostTypes();
		$this->registerTaxonomies();

		$implementations = array(
			'calculators' => array(
				'ShortCalc\Calculators\WPPostCalculator',
				'ShortCalc\Calculators\YAMLCalculator',
				'ShortCalc\Calculators\JsonCalculator',
			),
			'formulaParsers' => array(
				'ShortCalc\FormulaParsers\HoaMath',
			)
		);
		$implementations = apply_filters('shortcalc_register_implementations', $implementations);

		$this->registerImplementations($implementations);
		$this->addShortcodes();

		do_action($this->plugin_slug . '_init');
	}

	/**
	 * This method should be hooked to WordPress activation hook and
	 * installs the plugin.
	 **/
	static function install() {
	}

	/**
	 * This method should be hooked to WordPress deactivation hook and
	 * cleans an installation of this plugin.
	 **/
	static function uninstall() {
	}

	private function registerPostTypes() {
	}

	private function registerTaxonomies() {
	}

	/**
	 * Private method to add shortcodes to WordPress.
	 **/
	private function addShortcodes() {
		add_shortcode('shortcalc_calculator', array($this, 'runShortcodeCalculator'));
	}

	function runShortcodeCalculator($atts) {
		// todo: get default type from available calculators
		$a = shortcode_atts( array(
			'type' => 'no type',
		), $atts, 'shortcalc_calculator' );

		// consult IoC to request calculator by type
		$calculator = IoC::getCalculator($atts['type']);
		// set default values, from $atts?
		return $calculator->renderForm();
	}

	public function getCalculatorResult() {
		$type = $_POST['calculator_type'];
		// consult IoC to request calculator by type
		$calculator = IoC::getCalculator($type);
		return $calculator->renderResult($_POST);
	}

	public function loadPluginTextdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters('plugin_locale', get_locale(), $domain);
		load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
		load_plugin_textdomain($domain, FALSE, basename(plugin_dir_path(dirname(__FILE__ ))) . '/languages/');
	}
}