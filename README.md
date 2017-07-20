# ShortCalc: WordPress plugin for calculator forms

This plugin makes a shortcode available to show a calculator
form. Calculators can be defined in WordPress as a post (custom
posttype) or in a JSON file.

Calculators can also be defined in a custom format which can be
added from a theme (functions.php). The parsing of formula's is
done with HoaMath, but can also be extended with custom formula
parsers

## HoaMath
The formula parser that is included does not have the best documentation.
It can be found here: https://github.com/hoaproject/Math

### Formula's
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

## Overriding theme for forms
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

#### Template for result area
The layout of the result of the calculation is loaded with ajax and the
template for this is embedded as a script-tag in the template above.
````<script type="text/html" id="tmpl-calculator-result-<?php echo $name;?>">````
````...````
````</script>````