<label for="<?php echo $field->getName(); ?>"> <?php echo $field->getLabel(); ?></label>
<textarea 
name="<?echo $field->getName(); ?>"
id="<?php echo $field->getId(); ?>"
class="<?php echo $field->getClasses(); ?>"
cols="<?php echo $field->getAttribute('cols'); ?>"
rows="<?php echo $field->getAttribute('rows'); ?>"
><?php echo $field->getValue(); ?></textarea>