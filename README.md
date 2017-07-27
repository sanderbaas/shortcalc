# ShortCalc: WordPress plugin for calculator forms

This plugin makes a shortcode available to show a calculator
form. Calculators can be defined in WordPress as a post (custom
posttype) or in a JSON file.

Calculators can also be defined in a custom format which can be
added from a theme (functions.php). The parsing of formula's is
done with HoaMath, but can also be extended with custom formula
parsers

## Shortcode
This plugin makes the following shortcode available:
`[shortcalc_calculator name="pythagoras"]`. A `name` must always
be supplied. Other parameters can be used to add default values
to the parameters of a calculator form. For example to fill the a-field
for the pythagoras form do the following:
`[shortcalc_calculator name="pythagoras" param_a="11"]`.

## Calculators
As mentioned, calculators can be defined in two ways, but that can
be extended by adding custom ways. For all calculators goes that they
can be defined very detailled, by also defining the fields for the forms.
That is not neccesary, because when the fields are not defined, they are
generated.

### Define calculator as WordPress post
This plugin adds a calulator posttype to WordPress and through this calculators
can be defined.

### Define calculator as JSON file
Calculators can be defined in a JSON file. One example is included, this is the
`pythagoras` calculator. Custom calculators need to be defined in the active
WordPress theme. Just put the definitions in de root of the theme, or better,
in the shortcalc subdirectory of it. Use this file format: `calculator-{name}.json`.

## Formula's
Formula's can be defined using HoaMath. Documentation can be found
(here)[https://github.com/hoaproject/Math], but the most important features are described
below.

### Defining formula's with HoaMath
Formula's consist of variables, constantes, operators and functions. The
constantes are just numbers like 3.14 or 300000 and can be used as such.
The variables must be defined enclosed by double curly brackets, like `{{E}}`
or `{{speed}}`. The operators are symbols and the functions are (short) words.
The functions take parameters and after a function name regular brackets should
be used: `avg({{a}},4,5,{{b}})`

#### Operators
* `+`
* `-`
* `*`
* `/`

#### Functions
* abs
* acos
* asin
* atan
* average
* avg
* ceil
* cos
* count
* deg2rad
* exp
* floor
* ln
* log
* max
* min
* pow
* rad2deg
* round
* sin
* sqrt
* sum
* tan

## Changing form layout
It is possible to change the look and feel of the calculator forms.
To override all calculator forms at once, copy the view from the plugin
`views/content-calculator-form.php` to the theme directory that is
active in WordPress. Copy it into the theme directory or a subdirectory
of it named `shortcalc`.

### Overriding a specific calculator form
To override the view for a specific calculator, append the name of the
calculator to the filename. For example for a calculator named `pythagoras`
change the filename to `content-calculator-form-pythagoras.php`.

Make sure to leave the id of the form the same, as well as the id for the
container of the result: `shortcalc-form-result-*`.

### Template for result area
The layout of the result of the calculation is loaded with ajax and the
template for this is embedded as a script-tag in the template above.
```
<script type="text/html" id="tmpl-calculator-result-<?php echo $name;?>">
...
</script>
```

## Defining custom calculator implementations
It is possible to define a calculator by WordPress post or JSON file. But it is
also possible to define a custom calculator implementation. To do this, extend
ShortCalc\Calculators\CalculatorCore and place this file in the active theme.
There are two possible locations for this:
- `[theme-dir]/class/ShortCalc/Calculators/MyCalculator.php`
- `[theme-dir]/shortcalc/class/ShortCalc/Calculators/MyCalculator.php`

The class should also implement the `ShortCalc\CalculatorInterface`. An example
file is:
````
namespace ShortCalc\Calculators;
use ShortCalc\CalculatorInterface;

class MyCalculator extends CalculatorCore implements CalculatorInterface {
	public static function find(String $name) {
		error_log('MyCalculator find: ' . $name);
	}
}
````

Don't forget to register the calculator like so:
````
add_filter('shortcalc_register_implementations', function($implementations){
	$implementations['calculators'][] = 'ShortCalc\Calculators\MyCalculator';
	return $implementations;
},10,1);
````

## Defining a custom formula parser implementation
It is possible to define a custom formula parser implementation and this works
almost the same as defining a custom calculator implementation. In order to do
this, create a new file with a class implementing `ShortCalc\FormulaParserInterface`.

There are two possible locations for this:
- `[theme-dir]/class/ShortCalc/FormulaParsers/MyFormulaParser.php`
- `[theme-dir]/shortcalc/class/ShortCalc/FormulaParsers/MyFormulaParser.php`

An example
file is:
````
namespace ShortCalc\FormulaParsers;
use ShortCalc\FormulaParserInterface;

class MyFormulaParser implements FormulaParserInterface {
	public function __construct() {};
	public function setFormula($formula) {};
	public function setParameter(String $key, $value) {};
	public static function extractParameters($formula) {};
	public function getResult() {};
}
````

Don't forget to register the formula parser like so:
````
add_filter('shortcalc_register_implementations', function($implementations){
	$implementations['formulaParsers'][] = 'ShortCalc\FormulaParsers\MyFormulaParser';
	return $implementations;
},10,1);
````
