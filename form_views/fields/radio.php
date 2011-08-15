<label for="<?php echo $field->getName(); ?>"> <?php echo $field->getLabel(); ?></label>
<input 
type="radio" 
name="<?echo $field->getName(); ?>" 
value="<?php echo $field->getValue(); ?>" 
id="<?php echo $field->getId(); ?>" 
class="<?php echo $field->getClasses(); ?>"
/>