<form name="<?php echo $name; ?>" id="shortcalc-form-<?php echo $name;?>">
<?php foreach ($parameters as $key => $parameter):?>
<label for="<?php echo $parameter->attributes->id;?>"><?php echo $parameter->label;?></label>
<?php if ($parameter->element == 'input'): ?>
<?php echo $parameter->prefix; ?><input <?php echo $parameter->allAttributes;?> /><?php echo $parameter->postfix; ?>
<?php endif; ?>
<?php if ($parameter->element == 'select'): ?>
<?php echo $parameter->prefix; ?><select <?php echo $parameter->allAttributes;?>>
<?php foreach ($parameter->options as $option):?>
<option value="<?php echo $option;?>" <?php selected($parameter->attributes->value, $option); ?>>
	<?php echo $option;?>
</option>
<?php endforeach; ?>
</select><?php echo $parameter->postfix; ?>
<?php endif; ?>
<?php endforeach; ?>
</form>
<div id="shortcalc-form-result-<?php echo $name;?>"></div>

<script type="text/html" id="tmpl-calculator-result-<?php echo $name;?>">
   <p><?php echo $result_prefix; ?>{{data.result}}<?php echo $result_postfix;?></p>
</script>