<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class CalculatorCore implements CalculatorInterface {
	public $name;
	public $parameters;
	public $resultPrefix;
	public $resultPostfix;
	public $formula;
	public $formulaParser;

	public function __construct(String $name) {
		$this->name = $name;
		$this->parameters = new \stdClass();
	}

	public static function wpInit() {}

	public static function find(String $name) {
		return false;
	}

	public function renderForm(Array $params) {
		$template = __DIR__ . '/../../../views/content-calculator-form.php';
		$override = locate_template(array(
			'shortcalc/content-calculator-form.php',
			'shortcalc/content-calculator-form-'.sanitize_file_name($this->name).'.php',
			'content-calculator-form.php',
			'content-calculator-form-'.sanitize_file_name($this->name).'.php',
		));
		$template = $override ? $override : $template;
		$parameters = $this->mergeParameters($this->parameters, $params);
		$parameters = $this->aggregateAttributes($parameters);

		set_query_var('name', $this->name);
		set_query_var('parameters', $parameters);
		set_query_var('result_prefix', $this->resultPrefix);
		set_query_var('result_postfix', $this->resultPostfix);
		ob_start();
		load_template($template, false);
		return str_replace("\n","",ob_get_clean());
	}

	private function aggregateAttributes($parameters) {
		foreach ($parameters as $key => $param) {
			$param->allAttributes = "";
			foreach ($param->attributes as $name => $value) {
				$param->allAttributes .= "$name=\"$value\" ";
			}
		}
		return $parameters;
	}

	protected static function createParameter($name, $element = 'input', $type = 'text', $value = '') {
		$param = new \stdClass();
		$param->element = 'input';
		$param->attributes = new \stdClass();
		if ($type !== 'submit') {
			$param->label = $name;
			$param->attributes->required = true;
		}
		$param->attributes->id = 'shortcalc_'.rand(0,1000000);
		$param->attributes->name = $name;
		$param->attributes->type = $type;
		$param->attributes->value = $value;
		return $param;
	}

	protected function assignParameters($parameters) {
		$plugin = IoC::getPluginInstance();
		$domain = $plugin->plugin_slug;

		// create default parameters
		$formulaParameters = $this->formulaParser::extractParameters($this->formula);
		foreach ($formulaParameters as $formulaParameter){
			$this->parameters->{$formulaParameter} = self::createParameter($formulaParameter);
		}

		$processedSubmit = false;
		foreach ($parameters as $key => $param) {
			if (empty($param->attributes)) { $param->attributes = new \stdClass(); }
			if (empty($param->attributes->id)) { $param->attributes->id = 'shortcalc_'.rand(0,1000000);}
			if (empty($param->attributes->name)) { $param->attributes->name = $param->name;}
			if (empty($param->attributes->name)) { $param->attributes->name = $key;}
			if (empty($param->attributes->value)) { $param->attributes->value = '';}
			if (empty($param->element)) { $param->element = 'input';}
			if (empty($param->label)) { $param->label = '';}
			if (empty($param->prefix)) { $param->prefix = '';}
			if (empty($param->postfix)) { $param->postfix = '';}

			if ($param->element == 'input' && empty($param->attributes->type)) {
				$param->attributes->type = 'text';
			}

			if (empty($param->label) && $param->attributes->type !== 'submit'
				&& $param->element !== 'button') {
				$param->label = $key;
			}

			$this->parameters->{$param->attributes->name} = $param;

			if ($param->attributes->type == 'submit') {
				$processedSubmit = true;
			}
		}

		// add submit button if there is not any
		if (!$processedSubmit) {
			$this->parameters->submit = self::createParameter('submit', 'input', 'submit', __('Calculate', $domain));
		}
	}

	/**
	 * Merge predefined parameters with shortcode parameters.
	 * It is possible to supply values to the defined parameters
	 * via shortcode attributes. This functions puts the values
	 * in the right parameter, optionally overriding predefined
	 * default values
	 *
	 * @param object $parameters Predefined calculator parameters.
	 * @param array $overrides Parameters from shortcode attributes.
	 * @return object Predefined calculator parameters overridden with shortcode parameters.
	 */
	private function mergeParameters($parameters, $overrides) {
		foreach ($overrides as $key => $value) {
			if (!empty($parameters->{$key})) {
				if (empty($parameters->{$key}->attributes)) {
					$parameters->{$key}->attributes = new \stdClass;
				}
				$parameters->{$key}->attributes->value = $value;
			}
		}
		return $parameters;
	}

	public function renderResult() {
		$this->formulaParser->setFormula($this->formula);
		foreach ($this->parameters as $key => $param) {
			$value = $_POST['parameters'][$param->attributes->name];
			$this->formulaParser->setParameter($param->attributes->name,$value);
		}
		echo $this->formulaParser->getResult();
		exit;
	}
}