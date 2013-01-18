<?php
$formattedValues = array();
foreach($field->getOptions() as $key => $label){
	if(is_array($field->getValue()) && in_array($key, $field->getValue())){
		$formattedValues[$key] = array('label'=>$label, 'checked' => true);
	}else{
		$formattedValues[$key] = array('label'=>$label, 'checked' => false);
	}
}
?>
<div class="form_field_<?php echo $field->getName(); ?>">
<?php foreach($formattedValues as $value=>$attr): ?>
<span class="checkbox_field">
	<label for="<?php echo $field->getName(); ?>"><?php echo $attr['label']; ?></label> 
	<input type="checkbox" name="<?php echo $field->getName().'[]'; ?>"
	value="<?php echo $value; ?>" 
	<?php if($attr['checked']):?>
	checked="checked" 
	<?php endif; ?> />
</span>
<?php endforeach; ?>
</div>
