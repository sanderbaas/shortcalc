<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

/**
 * Core functionality of calculators. Specific implementations
 * share this and can override this behaviour.
 **/
class CalculatorCore implements CalculatorInterface {
	/** @var string Name of the calculator. **/
	public $name;
	/** @var object Object with all parameters needed for calculation. **/
	public $parameters;
	/** @var string Character to use as decimal separator of result. **/
	public $resultDecimalSep;
	/** @var string Character to use as thousands separator of result. **/
	public $resultThousandsSep;
	/** @var string Text to put before the result of calculation. **/
	public $resultPrefix;
	/** @var string Text to put after the result of calculation. **/
	public $resultPostfix;
	/** @var string Formula used to perform calculation. **/
	public $formula;
	/** @var mixed Formula parser used to parse formula. **/
	public $formulaParser;

	/**
	 * Constructor for this class.
	 *
	 * @param string $name Name of the new calculator.
	 **/
	public function __construct(String $name) {
		$this->name = $name;
		$this->parameters = new \stdClass();
		$this->resultDecimalSep = '.';
		$this->resultThousandsSep = '';
	}

	/**
	 * Register calculator specific WordPress behaviour. It will be called on
	 * the WordPress init trigger. This function should be overridden when needed
	 * in children.
	 **/
	public static function wpInit() {}

	/**
	 * Find a calculator by name. This core class implementation is empty, but in
	 * children it shoud be overridden.
	 *
	 * @param string $name Name of the calculator to find.
	 *
	 * @return boolean In the core class this always returns false.
	 **/
	public static function find(String $name) {
		return false;
	}

	/**
	 * Render the frontend form for the calculator.
	 *
	 * @param array @params Parameters received from shortcode caller
	 * to override default values of form fields.
	 *
	 * @return string HTML with form of calculator.
	 **/
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

	/**
	 * Walks through supplied parameters and create an allAttributes
	 * property with all parameter attributes as a single string.
	 *
	 * @param object Object with all parameters of a calculator.
	 *
	 * @return object Same object as supplied parameter, but extended
	 * with an allAttributes property per parameter.
	 **/
	private function aggregateAttributes($parameters) {
		foreach ($parameters as $key => $param) {
			$param->allAttributes = "";
			foreach ($param->attributes as $name => $value) {
				$param->allAttributes .= "$name=\"$value\" ";
			}
		}
		return $parameters;
	}

	/**
	 * Create a parameter object filled with supplied properties.
	 *
	 * @param string $name Name of the parameter.
	 * @param string $element Type of element, either input or select.
	 * @param string $type Type of input element, e.g. text or submit.
	 * @param string $value Default value of the parameter.
	 *
	 * @return object A new parameter object.
	 **/
	protected static function createParameter($name, $element = 'input', $type = 'text', $value = '') {
		$param = new \stdClass();
		$param->element = 'input';
		$param->prefix = '';
		$param->postfix = '';
		$param->label = '';
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

	/**
	 * Assign parameters to a calculator. When not all or no parameters
	 * are in supplied parameter object, parameters will be created after
	 * extracting needed parameters from formula.
	 *
	 * @param object $parameters Object with parameters as attributes.
	 *
	 * @return void
	 **/
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
			if (empty($param->attributes->name) && !empty($param->name)) {
				$param->attributes->name = $param->name;
			}
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
	 * Merge predefined parameters with shortcode parameters. It is possible to
	 * supply values to the defined parameters via shortcode attributes. This
	 * functions puts the values in the right parameter, optionally overriding
	 * predefined default values
	 *
	 * @param object $parameters Predefined calculator parameters.
	 * @param array $overrides Parameters from shortcode attributes.
	 *
	 * @return object Predefined calculator parameters overridden with shortcode
	 * parameters.
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

	/**
	 * Render the result of the calculation. The needed parameters are supplied
	 * via WordPress in the $_POST global variable. The result is immediately
	 * echoed, because WordPress calls this via ajax.
	 *
	 * @return void
	 **/
	public function renderResult() {
		$this->formulaParser->setFormula($this->formula);
		foreach ($this->parameters as $key => $param) {
			$value = self::formatParameterValue($_POST['parameters'][$param->attributes->name]);
			$this->formulaParser->setParameter($param->attributes->name,$value);
		}
		$result = $this->formulaParser->getResult();
		$numDecimals = strlen(substr(strrchr($result, "."), 1));
		$fResult = number_format($result, $numDecimals, $this->resultDecimalSep, $this->resultThousandsSep);
		echo $fResult;
		exit;
	}

	/**
	 * Parameter values can be entered with different decimal separators
	 * and optionally spaces and dots as thousends separator. This function
	 * strips this and returns values with a dot as decimal separator.
	 *
	 * @param string $value Value to format
	 *
	 * @return float The formatted value that was inputted
	 **/
	private static function formatParameterValue($value) {
		$dotPos = strrpos($value, '.');
		$commaPos = strrpos($value, ',');

		$sep = false;
		if ($dotPos && $dotPos > $commaPos) { $sep = $dotPos; }
		if ($commaPos && $commaPos > $dotPos) { $sep = $commaPos; }

		if (!$sep) {
			return floatval(preg_replace("/[^0-9]/", "", $value));
		}

		return floatval(
			preg_replace("/[^0-9]/", "", substr($value, 0, $sep)) . '.' .
			preg_replace("/[^0-9]/", "", substr($value, $sep+1, strlen($value)))
		);
	}
}