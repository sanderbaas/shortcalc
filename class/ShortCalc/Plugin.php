<?php
namespace ShortCalc;

class Plugin {
	public $plugin_slug = null;
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
		$this->initCalculators();
		$this->addShortcodes();

		do_action($this->plugin_slug . '_init');
	}

	public function initCalculators() {
		foreach ($this->implementations['calculators'] as $calculator) {
			$calculator::wpInit($this->plugin_slug);
		}
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
		$this->registerCalculatorPostType();
	}

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

	private function registerTaxonomies() {
	}

	/**
	 * Private method to add shortcodes to WordPress.
	 **/
	private function addShortcodes() {
		add_shortcode('shortcalc_calculator', array($this, 'runShortcodeCalculator'));
	}

	function runShortcodeCalculator($atts) {
		$a = shortcode_atts( array(
			'name' => '',
		), $atts, 'shortcalc_calculator' );

		// consult IoC to request calculator by type
		$calculator = IoC::findCalculator($atts['name']);
		// set default values, from $atts?
		return $calculator->renderForm();
	}

	public function getCalculatorResult() {
		$name = $_POST['calculator_name'];
		// consult IoC to request calculator by name
		$calculator = IoC::getCalculator($type);
		$calculator->renderResult($_POST);
	}

	public function loadPluginTextdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters('plugin_locale', get_locale(), $domain);
		load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
		load_plugin_textdomain($domain, FALSE, basename(plugin_dir_path(dirname(__FILE__ ))) . '/languages/');
	}
}