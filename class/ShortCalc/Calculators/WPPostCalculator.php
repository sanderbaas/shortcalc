<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class WPPostCalculator extends CalculatorCore implements CalculatorInterface {
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
			$value = str_replace('ShortCalc\\FormulaParsers\\','',$parser);
			$options[$value] = $value;
		}

		$cmb->add_field( array(
			'name'       => esc_html__( 'Formula', $domain ),
			'desc'       => esc_html__( 'Formula parsable by Formula Parser above. Docs: https://hoa-project.net/En/Literature/Hack/Math.html', $domain ),
			'id'         => $prefix . 'formula',
			'type'       => 'text',
			'column' => true,
		) );

		$cmb->add_field(array(
			'name' => 'Formula Parser',
			'id'   => $prefix . 'formula_parser',
			'type' => 'select',
			'options' => $options
		) );

		$group_parameters = $cmb->add_field( array(
			'id'          => $prefix . 'parameters',
			'type'        => 'group',
			'description' => __('Parameter meta data', $domain),
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
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Attributes',
			'description' => __('HTML attributes, for example: value="Foo" required type="submit"', $domain),
			'id'   => 'attributes',
			'type' => 'textarea_small',
		) );

		$cmb->add_group_field( $group_parameters, array(
			'name' => 'Options',
			'description' => __('Selectable options for select elements', $domain),
			'id'   => 'options',
			'type' => 'text',
			'repeatable' => true,
		) );
	}

	public static function find(String $name) {
		$args=array(
			'name' => $name,
			'post_type' => 'shortcalc_calculator',
			'post_status' => 'publish',
			'posts_per_page' => 1
		);
		$calculators = get_posts($args);
		if (sizeof($calculators) == 1) {
			$meta = get_post_meta( $calculators[0]->ID, '', true );

			// convert parameters to json array
			$arrParameters = array();
			if (!empty($meta['shortcalc_parameters'][0])) {
				$parametersSerialized = $meta['shortcalc_parameters'][0];
				$arrParameters = unserialize($parametersSerialized);
			}

			$parameters = new \StdClass;
			foreach ($arrParameters as $key => $param) {
				$clsParam = new \StdClass;
				$clsParam->element = $param['element'];
				$clsParam->label = $param['label'];
				$clsParam->name = $param['name'];
				$clsParam->attributes = new \StdClass;

				// split attributes into a class
				$attributes = explode(' ', $param['attributes']);
				foreach ($attributes as $attr) {
					$parts = explode('=', $attr);
					if (sizeof($parts)>1) {
						$clsParam->attributes->{$parts[0]} = $parts[1];
					}
					if (sizeof($parts)==1) {
						$clsParam->attributes->{$parts[0]} = true;
					}
				}
				$parameters->{'param_' . $key} = $clsParam;
			}

			$calculator = IoC::newCalculator($name, __CLASS__);
			$calculator->formula = $meta['shortcalc_formula'][0];
			$formulaParser = $meta['shortcalc_formula_parser'][0];
			$calculator->formulaParser = IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\' . $formulaParser);
			$calculator->parameters = $parameters;

			foreach ($calculator->parameters as $key => $param) {
				if (empty($param->attributes)) { $param->attributes = new \stdClass(); }
				if (empty($param->attributes->id)) { $param->attributes->id = $key;}
				if (empty($param->attributes->name)) { $param->attributes->name = $param->name;}
				if (empty($param->element)) { $param->element = 'input';}
				if ($param->element == 'input' && empty($param->attributes->type)) {
					$param->attributes->type = 'text';
				}
				$param->allAttributes = "";
				foreach ($param->attributes as $name => $value) {
					$param->allAttributes .= "$name=\"$value\" ";
				}
				if (empty($param->label) && $param->attributes->type !== 'submit'
					&& $param->element !== 'button') {
					$param->label = $key;
				}
			}

			return $calculator;
		}
		return false;
	}
}