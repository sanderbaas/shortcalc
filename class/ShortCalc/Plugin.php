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

		add_action( 'cmb2_admin_init', array($this, 'registerCalculatorMetabox'));
	}

	public function registerCalculatorMetabox() {
		$domain = $this->plugin_slug;
		$prefix = $this->plugin_slug . '_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'calculator_mb',
			'title'         => esc_html__( 'Calculator Details', $domain ),
			'object_types'  => array( 'shortcalc_calculator', ),
		) );

		$options = array();
		foreach ($this->implementations['formulaParsers'] as $parser){
			$options[$parser] = $parser;
		}

		$cmb->add_field(array(
			'name' => 'Formula Parser',
			'id'   => 'formula_parser',
			'type' => 'select',
			'options' => $options
		) );

		$cmb->add_field( array(
			'name'       => esc_html__( 'Formula', $domain ),
			'desc'       => esc_html__( 'field description (optional)', $domain ),
			'id'         => $prefix . 'text',
			'type'       => 'text',
			'column' => true,
		) );

		$group_parameters = $cmb->add_field( array(
			'id'          => $prefix . 'parameters',
			'type'        => 'group',
			// 'repeatable'  => false, // use false if you want non-repeatable group
			'options'     => array(
				'group_title'   => __( 'Parameter {#}', $domain ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __( 'Add Another Parameter', $domain ),
				'remove_button' => __( 'Remove Parameter', $domain ),
				'sortable'      => true, // beta
				// 'closed'     => true, // true to have the groups closed by default
			),
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Name',
			'description' => __('Must match a parameter in the formula', $domain),
			'id'   => 'name',
			'type' => 'text',
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Type of Element',
			'id'   => 'element',
			'type' => 'select',
			'default' => 'input',
			'options' => array(
				'input' => __('Input', $domain),
				'select' => __('Select', $domain),
			),
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Label',
			'id'   => 'label',
			'description' => __('The label is used on the calculator frontend', $domain),
			'type' => 'text',
			// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Attributes',
			'description' => __('HTML attributes, for example: value="Foo" required type="submit"', $domain),
			'id'   => 'attribute',
			'type' => 'textarea_small',
			'repeatable' => false, // Repeatable fields are supported w/in repeatable groups (for most types)
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Options',
			'description' => __('Selectable options for select elements', $domain),
			'id'   => 'options',
			'type' => 'text',
			'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
		) );
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