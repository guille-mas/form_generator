<label for="<?php echo $field->getId(); ?>"><?php echo $field->getLabel(); ?></label>
<?php echo $field->getValue(); ?>
<input 
type="hidden" name="<?echo $field->getName(); ?>" 
value="<?php echo $field->getValue(); ?>" 
id="<?php echo $field->getId(); ?>"
/>