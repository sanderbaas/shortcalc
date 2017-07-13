<form name="<?php echo $name; ?>" id="shortcalc-form-<?php echo $name;?>">
<?php foreach ($parameters as $key => $parameter):?>
<?php if ($parameter->element == 'input'): ?>
<label for="<?php echo $parameter->attributes->id;?>">
<?php echo $parameter->label;?></label>
<input <?php echo $parameter->allAttributes;?> />
<?php endif; ?>
<?php if ($parameter->element == 'select'): ?>
<select <?php echo $parameter->allAttributes;?>>
<?php foreach ($parameter->options as $option):?>
<option value="<?php echo $option;?>" <?php selected($parameter->attributes->value, $option); ?>>
	<?php echo $option;?>
</option>
<?php endforeach; ?>
</select>
<?php endif; ?>
<?php endforeach; ?>
</form>
<div id="shortcalc-form-result-<?php echo $name;?>"></div>

<script type="text/html" id="tmpl-calculator-result-<?php echo $name;?>">
   <p>{{data.result}}</p>
</script>