<?php
namespace ShortCalc;

/**
 * This class implements the base plugin functions. It should be used
 * as a singleton.
 **/
class Plugin {
	/**
	 * @var string|null Identifier of plugin, used for textdomains and creation
	 * of unique id's for objects associated with this plugin.
	 **/
	public $plugin_slug = null;

	/** @var array Contains the registered calculators and formula parsers. **/
	public $implementations = array();

	/**
	 * Constructor for Plugin class.
	 *
	 * @param String $slug Slug to use for textdomain etc.
	 **/
	public function __construct($slug) {;
		$this->plugin_slug = $slug;
		add_action('init', array($this,'init'));
		add_action('wp_ajax_get_calculator_result', array($this, 'getCalculatorResult'));
		add_action('wp_ajax_nopriv_get_calculator_result', array($this, 'getCalculatorResult'));
	}

	/**
	 * Register implementations of calculators and formula parsers.
	 *
	 * @param array $implementations Array with classnames of calculators
	 * and formula parsers, separated in different subarrays under the keys
	 * calculators and formulaParsers.
	 *
	 * @return void
	 **/
	private function registerImplementations($implementations) {
		foreach ($implementations as $key => $classNames) {
			$this->implementations[$key] = $classNames;
		}
	}

	/**
	 * Initialization of (WordPress) functionality of this plugin:
	 * - enqueue javascript script in WP
	 * - load textdomain of plugin
	 * - register custom post type
	 *
	 * @return void
	 **/
	public function init() {
		wp_enqueue_script('shortcalc-js', plugins_url("../../js/shortcalc.js", __FILE__) ,array('jquery','wp-util'));
		$ajax_object = array(
			'ajax_url' => admin_url('admin-ajax.php')
		);
		wp_localize_script( 'shortcalc-js', 'ajax_object', $ajax_object);

		$this->loadPluginTextdomain();
		$this->registerCalculatorPostType();

		$implementations = array(
			'calculators' => array(
				'ShortCalc\Calculators\WPPostCalculator',
				'ShortCalc\Calculators\JsonCalculator',
			),
			'formulaParsers' => array(
				'ShortCalc\FormulaParsers\HoaMath',
			)
		);
		$implementations = apply_filters('shortcalc_register_implementations', $implementations);

		$this->registerImplementations($implementations);
		$this->initCalculators();
		$this->addShortcodes();

		do_action($this->plugin_slug . '_init');
	}

	/**
	 * Call init function for each registered calculator to initialize possible
	 * functionality that has to be registered on WordPress init.
	 *
	 * @return void
	 **/
	private function initCalculators() {
		foreach ($this->implementations['calculators'] as $calculator) {
			$calculator::wpInit($this->plugin_slug);
		}
	}

	/**
	 * This method should be hooked to WordPress activation hook and
	 * installs the plugin.
	 *
	 * @return void
	 **/
	static function install() {
	}

	/**
	 * This method should be hooked to WordPress deactivation hook and
	 * cleans an installation of this plugin.
	 *
	 * @return void
	 **/
	static function uninstall() {
	}

	/**
	 * Register custom WordPress posttype.
	 *
	 * @return void
	 **/
	private function registerCalculatorPostType() {
		$domain = $this->plugin_slug;
		$labels = array(
			'name'               => _x( 'Calculators', 'post type general name', $domain ),
			'singular_name'      => _x( 'Calculator', 'post type singular name', $domain ),
			'menu_name'          => _x( 'Calculators', 'admin menu', $domain ),
			'name_admin_bar'     => _x( 'Calculator', 'add new on admin bar', $domain ),
			'add_new'            => _x( 'Add New', 'calculator', $domain ),
			'add_new_item'       => __( 'Add New Calculator', $domain ),
			'new_item'           => __( 'New Calculator', $domain ),
			'edit_item'          => __( 'Edit Calculator', $domain ),
			'view_item'          => __( 'View Calculator', $domain ),
			'all_items'          => __( 'All Calculators', $domain ),
			'search_items'       => __( 'Search Calculators', $domain ),
			'parent_item_colon'  => __( 'Parent Calculators:', $domain ),
			'not_found'          => __( 'No calculators found.', $domain ),
			'not_found_in_trash' => __( 'No calculators found in Trash.', $domain )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', $domain ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'calculator' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( 'shortcalc_calculator', $args );
	}

	/**
	 * Register shortcodes in WordPress.
	 *
	 * @return void
	 **/
	public function addShortcodes() {
		add_shortcode('shortcalc_calculator', array($this, 'runShortcodeCalculator'));
	}

	/**
	 * Run shortcode for calculator when called by WordPress.
	 *
	 * @param array $atts Array containing the attributes with which
	 * the shortcode was called.
	 * - string name Name of calculator to use
	 * - string param_* Default values for arguments used in formula, prefixed
	 *   with param_ and then the argument name.
	 *
	 * @return string HTML of the form the fill out calculator.
	 **/
	public function runShortcodeCalculator($atts) {
		$a = shortcode_atts( array(
			'name' => '',
		), $atts, 'shortcalc_calculator' );

		// default values
		$defaults = array_filter($atts, function($val, $key){
			return preg_match('/^param_/', $key) == 1;
		}, ARRAY_FILTER_USE_BOTH);

		// remove prefix
		$params = [];
		foreach($defaults as $key => $value) {
			$params[preg_filter('/^param_/','',$key)] = $value;
		}

		// consult IoC to request calculator by type
		$calculator = IoC::findCalculator($a['name']);
		if (!$calculator) { return false; }
		// set default values, from $atts?
		return $calculator->renderForm($params);
	}

	/**
	 * Calculate the result of a specific calculator and let it echo
	 * the result by the specific calculator. Nothing is returned, because
	 * this is function is called in an ajax call.
	 *
	 * @param array $_POST Parameters and values submitted by the
	 * calculator form.
	 *
	 * @return void
	 **/
	public function getCalculatorResult() {
		$name = $_POST['calculator_name'];
		// consult IoC to request calculator by name
		$calculator = IoC::findCalculator($name);
		$calculator->renderResult($_POST);
	}

	/**
	 * Load translation file from active theme and this plugin.
	 *
	 * @return void
	 **/
	private function loadPluginTextdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters('plugin_locale', get_locale(), $domain);
		$moFile = trailingslashit(WP_LANG_DIR)
					.sanitize_file_name($domain) . '/'
					.sanitize_file_name($domain) . '-'
					.sanitize_file_name($locale) . '.mo';
		load_textdomain($domain, $moFile );
		load_plugin_textdomain($domain, FALSE, basename(plugin_dir_path(dirname(__FILE__,2))) . '/languages/');
	}
}