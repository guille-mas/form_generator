<?php if($form->is_multipart): ?>
<?php echo form_open_multipart($form->getActionUrl())."\n" ?>
<?php else: ?>
<form action="<?php echo $form->getActionUrl(); ?>" >
        <?php endif; ?>
        <?php foreach($form->getFields() as $field): ?>
        <p>
                <span class="form_field_<?php echo $field->getId(); ?>">
                <?php echo form_error($field->getId(), '<span class="form-error" >','</span>')."\n\t".$this->load->view('form_views/fields/'.$field->template, array('field'=>$field))."\n\n"; ?> </span>
        </p>
        <?php endforeach; ?>
        <input type="submit" name="submit" value="Submit" /><input type="submit" name="cancel" value="Cancel" />
</form>
