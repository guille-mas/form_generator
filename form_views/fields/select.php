<label for="<?php echo $field->getName(); ?>"> <?php echo $field->getLabel(); ?>
</label>
<select name="<?echo $field->getName(); ?>"
id="<?php echo $field->getId(); ?>"
class="<?php echo $field->getClasses(); ?>"
<?php if($field->hasAttribute('multiple')):?> multiple="multiple" <?php endif;?> 
>
<?php if(!$field->getAttribute('notnull') and !$field->getAttribute('required')): ?>
<option value="" ></option>
<?php endif; ?>
	<?php foreach($field->getOptions() as $value=>$label): ?>
	<option value="<?php echo $value; ?>">
	<?php echo $label; ?>
	</option>
	<?php endforeach; ?>
</select>
