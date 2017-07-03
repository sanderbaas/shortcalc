<?php
namespace ShortCalc;

/*
type is just the name

elements are all html elements shown on form (also tabs, separators, buttons, etc)
is there a default technique/lib/way of doing this -> adopt this

formula
can we use Excel style of is there another default, maybe support multiple styles

result formatter also argument?
maybe this is part of formula, what is the output: just one number or a graph, or whatever
then a result formatter would be cool. Also build this 'plugin' style

make it possible to add calculators as postType, but also as yaml (with d.i. we can handle these
different sources)
*/
interface CalculatorInterface {
	public function __construct(String $name);
	public static function find(String $name);
	public function renderForm(String $view = null);
	public function renderResult(String $view = null);
}