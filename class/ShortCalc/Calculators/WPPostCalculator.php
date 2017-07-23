<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class WPPostCalculator extends CalculatorCore implements CalculatorInterface {
	public static function wpInit() {
		add_action( 'cmb2_admin_init', array('ShortCalc\Calculators\WPPostCalculator', 'registerMetabox'));
	}

	function sanitizeText($str) {
		// restore leading and trailing space (just one)
		$filtered = sanitize_text_field($str);
		if (substr($str,0,1) == " ") { $filtered = ' ' . $filtered; }
		if (substr($str,-1) == " ") { $filtered = $filtered . ' '; }
		return $filtered;
	}

	public function registerMetabox() {
		$plugin = IoC::getPluginInstance();

		$domain = $plugin->plugin_slug;
		$prefix = $plugin->plugin_slug . '_';

		$cmb = new_cmb2_box(array(
			'id' => $prefix . 'calculator_mb',
			'title' => __('Calculator Details', $domain),
			'object_types' => array('shortcalc_calculator'),
		));

		$options = array();
		foreach ($plugin->implementations['formulaParsers'] as $parser){
			$value = str_replace('ShortCalc\\FormulaParsers\\','',$parser);
			$options[$value] = $value;
		}

		$cmb->add_field(array(
			'name' => __('Formula', $domain),
			'desc' => __('Formula parsable by Formula Parser above. Docs: https://hoa-project.net/En/Literature/Hack/Math.html', $domain),
			'id' => $prefix . 'formula',
			'type' => 'text',
			'column' => true,
		));

		$cmb->add_field(array(
			'name' => __('Formula Parser', $domain),
			'id' => $prefix . 'formula_parser',
			'type' => 'select',
			'options' => $options,
		));

		$cmb->add_field(array(
			'name' => __('Result Prefix', $domain),
			'desc' => __('Text to put before result', $domain),
			'id' => $prefix . 'result_prefix',
			'type' => 'text',
			'sanitization_cb' => array('ShortCalc\Calculators\WPPostCalculator', 'sanitizeText'),
			'column' => true,
		));

		$cmb->add_field(array(
			'name' => __('Result Postfix', $domain),
			'desc' => __('Text to put after result', $domain),
			'id' => $prefix . 'result_postfix',
			'type' => 'text',
			'sanitization_cb' => array('ShortCalc\Calculators\WPPostCalculator', 'sanitizeText'),
			'column' => true,
		));

		$group_parameters = $cmb->add_field(array(
			'id' => $prefix . 'parameters',
			'type' => 'group',
			'description' => __('Parameter meta data', $domain),
			'options' => array(
				'group_title' => __('Parameter {#}', $domain),
				'add_button' => __('Add Another Parameter', $domain),
				'remove_button' => __('Remove Parameter', $domain)
			),
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Name', $domain),
			'description' => __('Must match a parameter in the formula', $domain),
			'id' => 'name',
			'type' => 'text',
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Type of Element', $domain),
			'id' => 'element',
			'type' => 'select',
			'default' => 'input',
			'options' => array(
				'input' => __('Input', $domain),
				'select' => __('Select', $domain),
			),
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Label', $domain),
			'id' => 'label',
			'description' => __('The label is used on the calculator frontend', $domain),
			'type' => 'text',
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Prefix', $domain),
			'id' => 'prefix',
			'description' => __('Text to put before field on form', $domain),
			'sanitization_cb' => array('ShortCalc\Calculators\WPPostCalculator', 'sanitizeText'),
			'type' => 'text'
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Postfix', $domain),
			'id' => 'postfix',
			'description' => __('Text to put after field on form', $domain),
			'sanitization_cb' => array('ShortCalc\Calculators\WPPostCalculator', 'sanitizeText'),
			'type' => 'text',
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Attributes', $domain),
			'description' => __('HTML attributes, for example: value="Foo" required type="submit"', $domain),
			'id' => 'attributes',
			'type' => 'textarea_small',
		));

		$cmb->add_group_field( $group_parameters, array(
			'name' => __('Options', $domain),
			'description' => __('Selectable options for select elements', $domain),
			'id' => 'options',
			'type' => 'text',
			'repeatable' => true,
		));
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
				// due to a bug in CMB2 this is never not empty
				// so check if there is only 1 empty parameter
				$parametersSerialized = $meta['shortcalc_parameters'][0];
				$arrParameters = unserialize($parametersSerialized);
				if (sizeof($arrParameters)==1 && !isset($arrParameters[0]['name'])) {
					$arrParameters = array();
				}
			}

			$parameters = new \StdClass;
			foreach ($arrParameters as $key => $param) {
				$clsParam = new \StdClass;
				$clsParam->element = $param['element'];
				$clsParam->label = $param['label'];
				$clsParam->prefix = $param['prefix'];
				$clsParam->postfix = $param['postfix'];
				$clsParam->name = $param['name'];
				$clsParam->attributes = new \StdClass;

				// split attributes into a class
				$attributes = explode(' ', $param['attributes']);
				foreach ($attributes as $attr) {
					$parts = explode('=', $attr);
					if (sizeof($parts)>1) {
						$clsParam->attributes->{$parts[0]} = trim($parts[1],"\"'");
					}
					if (sizeof($parts)==1) {
						$clsParam->attributes->{$parts[0]} = true;
					}
				}
				$parameters->{'param_' . $key} = $clsParam;
			}

			$calculator = IoC::newCalculator($name, __CLASS__);
			$calculator->formula = $meta['shortcalc_formula'][0];
			$calculator->resultPrefix = $meta['shortcalc_result_prefix'][0];
			$calculator->resultPostfix = $meta['shortcalc_result_postfix'][0];
			$formulaParser = $meta['shortcalc_formula_parser'][0];
			$calculator->formulaParser = IoC::newFormulaParser('\\ShortCalc\\FormulaParsers\\' . $formulaParser);
			$calculator->assignParameters($parameters);

			return $calculator;
		}
		return false;
	}
}