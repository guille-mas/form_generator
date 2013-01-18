<?php
require_once dirname(__FILE__).'/DoctrineForm.php';
require_once APPPATH.'libraries/MY_Upload.php';

/**
 * @author Guillermo Maschwitz <guillermo.maschwitz@gmail.com>
 */
class AdminForm extends DoctrineForm
{

	/**
	 * This method is working right now, but is not secure, nor practic, to base the filename format of the uploaded file by the unique and primary keys fields of the model
	 * @param FormField $field
	 * @return null
	 *  
	 */
	public function uploadFile(FormField $field)
	{
		if(!$field->hasAttribute('file_upload')){
			throw new FormException('missing file_upload parameters');
		}
		$fileAttr = $field->getAttribute('file_upload');
		if(!(is_array($fileAttr))){
			throw new FormException('file_upload attribute must be an array');
		}

		/*
		 * fetch old file name
		 */
		$oldCompleteFileName = $field->getValue();
		$oldFileNameArray = explode('/',$oldCompleteFileName);
		$oldFileName = end($oldFileNameArray);

		foreach($fileAttr as $attr){
			$config['upload_path'] = APPPATH.'../../'.$attr['upload_path'];
			$config['allowed_types'] = 'gif|jpg|jpeg|png';

			if(isset($attr['filename_format'])){
				$config['file_name'] = $attr['filename_format'];
			}
			if(isset($attr['max_size'])){
				$config['max_size'] = $attr['max_size'];
			}
			if(isset($attr['max_width'])){
				$config['max_width'] = $attr['max_width'];
			}
			if(isset($attr['max_height'])){
				$config['max_height'] = $attr['max_height'];
			}
			if(isset($attr['image_width'])){
				$config['image_width'] = $attr['image_width'];
			}
			if(isset($attr['image_height'])){
				$config['image_height'] = $attr['image_height'];
			}

			$extArray = explode('/',$_FILES[$field->getId()]['type']);
			@$fileExtension = $extArray[1];

			/**
			 * Obtain the column names from the file_name_format field attribute.
			 * This columns are gonna be used to build the formated filename for the uploaded file
			 */
			$patternModelColumns = '/\{([a-zA-Z\_\-0-9]+)\}/';
			preg_match_all($patternModelColumns,$attr['filename_format'],$matchModelColumns,PREG_PATTERN_ORDER);

			if(isset($matchModelColumns[1])) {
				/**
				 * O sea encontro resultados y los tiro a $matchModelColumns
				 * El valor [0] devuelve toda la cadena con las llaves, del
				 * primer (y en este caso único juego de matches) que no nos importa así
				 * que lo saco, deja al primer valor util con index 0 (los valores
				 * siguientes son los matches)
				 */
				array_shift($matchModelColumns);

				foreach($matchModelColumns as $key => $subKey){
					foreach($subKey as $colName){
						$config['file_name'] = str_replace('{'.$colName.'}', $this->object->get($colName) , $config['file_name']);
					}
				}

				/**
				 * adding extension to file name
				 */
				$config['file_name'] = str_replace('[ext]', $fileExtension , $config['file_name']);
			}
			/**
			 * remove the spaces from the file_name
			 */
			$config['file_name'] = preg_replace('/\s+/', '', $config['file_name']);

			$oldFilePath = $config['upload_path'].$oldFileName;


			/**
			 * if some jpg file with more than zero lenght was posted...
			 */
			if($_FILES[$field->getId()]['size'] > 0){
				/**
				 * if a file exist at oldFilePath remove it
				 */
				if(isset($oldFilePath) && is_file($oldFilePath)){
					if(!unlink($oldFilePath)){
						throw new FormException('old file could not be removed');
						error_log('file_upload_exception: old file could not be removed');
					}
				}
			}
				
			/**
			 * loading Upload library
			 */
			$upload = new MY_Upload();
			$upload->initialize($config);

			if(!$upload->do_upload($field->getId())){
				$errorMessage = $upload->display_errors('','');
				if($errorMessage !== null and $errorMessage !== 'You did not select a file to upload.'){
					throw new Exception($errorMessage);
					error_log('admin_form_error: '.$errorMessage);
				}
			}else{
				/**
				 * if upload work and the field exist at the model level, set the updated value to the active record object
				 */
				$fieldId = $field->getId();
				if(isset($this->object->$fieldId) and isset($attr['persist_file_path']) and $attr['persist_file_path'] == true){
					$this->object->$fieldId = $config['file_name'];
				}
			}
		}
	}


}

?>
