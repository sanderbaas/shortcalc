<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class WPPostCalculator implements CalculatorInterface {
	public $name = null;
	public $settings = null;
	public $formula = null;
	public $formatter = null;

	public function __construct(String $name) {
		
	}

	public static function wpInit() {
		add_action( 'cmb2_admin_init', array('ShortCalc\Calculators\WPPostCalculator', 'registerMetabox'));
	}

	public function registerMetabox() {
		$plugin = IoC::getPluginInstance();

		$domain = $plugin->plugin_slug;
		$prefix = $plugin->plugin_slug . '_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'calculator_mb',
			'title'         => esc_html__( 'Calculator Details', $domain ),
			'object_types'  => array( 'shortcalc_calculator', ),
		) );

		$options = array();
		foreach ($plugin->implementations['formulaParsers'] as $parser){
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

	public static function find(String $name) {
		// wordpress select query
		error_log('wppost find: ' . $name);
	}

	public function renderForm(String $view = null) {
		return $this->name;
	}

	public function renderResult(String $view = null) {
		return $this->name;
	}
}