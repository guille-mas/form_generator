<?php	
/**
 *showing the image if any next to the field
 */
if($field->getValue() !== null){
	//dirty hack to avoid problems with StoresAdminForm inconsistence
	$arrayPathValue = explode('/',$field->getValue());
	$arrayNameValue = explode('.',end($arrayPathValue));
	$fileName = $arrayNameValue[0];
	$extension = $arrayNameValue[1];
	//end of hack later we will just need to use $imagePath.$field->getValue();
}
?>

<?php if($extension == ('png' or 'jpeg' or 'jpg' or 'gif' )): ?>
	<img class="file_upload_image" src="<?php echo site_url().$imagePath.$field->getValue().'#'.uniqId(); ?>" />
<?php endif; ?>

<label for="<?php echo $field->getName(); ?>"> <?php echo $field->getLabel(); ?></label>
<input 
type="file" 
name="<?echo $field->getName(); ?>" 
value="<?php echo $field->getValue(); ?>" 
id="<?php echo $field->getId(); ?>" 
class="<?php echo $field->getClasses(); ?>"
/>