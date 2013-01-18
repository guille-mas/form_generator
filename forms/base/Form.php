<?php
require_once dirname(__FILE__).'/FormField.php';

/**
 * class with the implementation of the basic methods for all forms
 *
 * @author Guillermo Maschwitz <guillermo.maschwitz@gmail.com>
 */
class Form
{

	protected $output = array();
	protected $actionUrl;
	protected $fields = array();
	public $is_multipart = false;
	public $template = 'default';

	
	/**
	 * returns a representation of the instance as an array
	 * @return array
	 *  
	 */
	public function toArray()
	{
		$output = array();
		$output['actionUrl'] = $this->actionUrl;

		foreach($this->fields as $field){
			$output['fields'][$field->getId()] = array(
                'id' => $field->getId(),
                'type' => $field->getType(),
                'name' => $field->getName(),
                'label' => $field->getLabel(),
                'value' => $field->getValue(),
                'options' => $field->getOptions(),
                'attributes' => $field->getAttributes()
			);
		}
		return $output;
	}



	/**
	 * Fields attribute getter
	 * Return an array of FormFields
	 * @return array
	 *  
	 */
	public function getFields()
	{
		return $this->fields;
	}


	/**
	 * Specific field getter
	 * Returns a Formfield by id from this Form fields attribute
	 * @return Formfield
	 *  
	 */
	public function getField($fieldId){
		if($this->hasField($fieldId)){
			return $this->fields[$fieldId];
		}
	}


	/**
	 * Check if some FormField exist into the Form instance fields by id
	 * @param string $fieldId
	 * @return boolean
	 *  
	 */
	public function hasField($fieldId){
		if(isset($this->fields[$fieldId])){
			return true;
		}
	}

	
	/**
	 * Returns an html view
	 * 
	 * @return string
	 * @todo finish the separation of views from Form controllers classes
	 *  
	 */
	function buildHTML()
	{
		return get_instance()->load->view('form_views/'.$this->template,array('form'=>$this), true);
	}


	/**
	 * Generate an html output for this Form and all of its fields
	 *
	 * @TODO This method is too large! Each field output should be handled at each field instances, but for that there should be subclasses for each type of Field.
	 * @return string
	 *  
	 */
	public function buildForm()
	{
		$hasFile = false;

		foreach($this->getFields() as $field){
			$value = $field->getValue();
			$length = $field->getAttribute('length');
			$fieldType = $field->getType();
			$label = $this->formatLabel($field->getLabel());

			$fieldName = $field->getName();

			$fieldKey = $field->getId();

			if(!$field->hasAttribute('id')){
				$fieldId = $field->getId();
			}else{
				$fieldId = $field->getAttribute('id');
			}

			$fieldOptions = $field->getOptions();

			if($fieldType == 'file'){
				$hasFile = true;
			}

			if($fieldType == 'hidden' || $fieldName == 'id'){
				array_push($this->output, $this->formatField($fieldKey, '', form_hidden($fieldKey, $value) ) );
			}
			elseIf($fieldType == 'password'){
				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_password($fieldKey, '') ) );
			}
			elseif($fieldType == 'enum'){
				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_dropdown($fieldKey,  $field->getOptions(), $this->fields[$fieldKey]->getValue() )));

			}elseif($fieldType == 'boolean'){
				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_dropdown($fieldKey, array(true => 'true', false => 'false'), $this->fields[$fieldKey]->getValue())) );

			}elseif($fieldType == 'file'){
					
				$imageAttributes = $field->getAttribute('file_upload');

				if(is_array($imageAttributes)){
					foreach($imageAttributes as $imgAttr){
						if(isset($imgAttr['persist_file_path']) and $imgAttr['persist_file_path']){
							$imagePath = $imgAttr['upload_path'];
							break;
						}
					}
				}else{
					throw new FormException('\'file_upload\' attribute must be defined to render '.$field->getId().' field', $code);
				}
					
					
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
					if($extension == ('png' or 'jpeg' or 'jpg' or 'gif' )){
						array_push($this->output, '<img class="file_upload_image" src="'.site_url().$imagePath.$field->getValue().'?nocache='.uniqId().'" />');
					}
				}


				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_upload(array(
                                    'name'        => $fieldKey,
                                    'id'          => $fieldId,
                                    'value'       => $value,
									'class'       => implode(' ',$this->getField($fieldKey)->getClasses())
				)) ));


			}elseif($fieldType == 'timestamp'){

				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_input(
				array(
                                    'name'        => $fieldKey,
                                    'id'          => $fieldId,
                                    'value'       => $value,
                                    'maxlength'   => $length,
                                    'size'        => '50',
                                    'class'       => 'datepicker'
                                    ))));

			}elseif($fieldType == 'text' or $fieldType == 'blob'){

				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_textarea(
				array(
                                    'name'        => $fieldKey,
                                    'id'          => $fieldId,
                                    'value'       => $value,
                                    'rows'        => '5',
                                    'cols'        => '35',
                                    'class'       => implode(' ',$this->getField($fieldKey)->getClasses())
				))));

			}elseif($fieldType == 'select'){
				$fieldOptions = $field->getOptions();
				if(!$field->getAttribute('notnull') and !$field->getAttribute('required')){
					$fieldOptions[null] = null;
				}

				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_dropdown($fieldKey,  $fieldOptions, $this->fields[$fieldKey]->getValue() )));
			}elseif($fieldType == 'multiple-select'){

				$selectedValues = $this->fields[$fieldKey]->getValue();

				array_push($this->output, $this->formatField($fieldKey.'[]', form_label($label, $fieldKey.'[]'), form_multiselect($fieldKey.'[]',  $field->getOptions(), $selectedValues)));
			}elseif($fieldType == 'multiple-checkbox'){

				$selectedValues = $this->fields[$fieldKey]->getValue();

				$output = '';

				/**
				 * getting a formattedValues array with the checked boolean key
				 */
				$formattedValues = array();
				foreach($fieldOptions as $key => $label){
					if(is_array($selectedValues) && in_array($key, $selectedValues)){
						$formattedValues[$key] = array('label'=>$label, 'checked' => true);
					}else{
						$formattedValues[$key] = array('label'=>$label, 'checked' => false);
					}
				}

				/**
				 * iterating over formattedValuesArray to build each checkbox
				 */
				foreach($formattedValues as $key=>$attr){
					$preOutput = '<input type="checkbox"  name="'.$fieldKey.'[]" value="'.$key.'" ';
					if($attr['checked']){
						$preOutput .= ' checked="checked" ';
					}
					$preOutput .= '/>';

					$output .=  '<div class="checkbox_field">'.$this->formatField($fieldKey.'[]', form_label($attr['label'], $fieldKey.'[]'), $preOutput, false).'</div>';
				}

				$output = '<span class="form_field_'. $fieldKey.'" >'. $output.'</span>';

				array_push($this->output, $output  );

			}elseif($fieldType == 'array'){

				if(isset($value[0])){
					$formattedValue = implode("\n", $value);
				}else{
					$formattedValue = null;
				}

				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_textarea(
				array(
                                    'name'        => $fieldKey,
                                    'id'          => $fieldId,
                                    'value'       => $formattedValue,
                                    'rows'        => '5',
                                    'cols'        => '35',
									'class'       => implode(' ',$this->getField($fieldKey)->getClasses())
				))));

			}elseIf($fieldType == 'fixed'){
				
				$opts = $field->getOptions();
				if(!empty($opts)){
					$v = $opts[$value];
				}else{
					$v = $value;
				}
				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey),$v.form_hidden($fieldKey, $value)));


			}else{

				array_push($this->output, $this->formatField($fieldKey, form_label($label, $fieldKey), form_input(
				array(
                                    'name'        => $fieldKey,
                                    'id'          => $fieldId,
                                    'value'       => $value,
                                    'maxlength'   => $length,
                                    'size'        => '50',
									'class'       => implode(' ',$this->getField($fieldKey)->getClasses())
				))));
			}
		}

		array_push($this->output,  '<p><span class="form_field_'.$fieldKey.'" >'."\n".''.form_submit('submit', 'Submit').form_submit('cancel', 'Cancel')."</span></p>\n" );
		array_push($this->output,  form_close()."\n");


		//adding form opening tag...
		if($hasFile){
			$formOpen = "<div class=\"input_form with_file\">\n".form_open_multipart($this->actionUrl, array('class'=>'mainforms helpforms','id' => get_class($this)))."\n".form_token();
		}else{
			$formOpen = "<div class=\"input_form\">\n".form_open($this->actionUrl, array('class'=>'mainforms helpforms','id' => get_class($this)))."\n".form_token();
		}
		array_unshift($this->output, $formOpen );

		return implode('',$this->output).'</div>';
	}



	/**
	 * Load data from POST and set the corresponding fields
	 * @return boolean
	 *  
	 */
	public function loadPostData()
	{
		$ci =& get_instance();
		$emptyPost = true;
		foreach($this->fields as $field){
			$postedData = $ci->input->post($field->getId());
			if($postedData !== false && $postedData !== ''){
				$emptyPost = false;

				switch($field->getType()){
					case 'array':
						$field->setValue(explode("\n", $postedData));
						break;
					default:
						$field->setValue($postedData);
						break;
				}
			}
		}

		if($emptyPost){
			return false;
		}

		return true;
	}




	/**
	 * Validate data sent by post
	 * @return boolean
	 *  
	 */
	public function isValid()
	{
		$ci = get_instance();

		foreach($this->getFields() as $field){

			if($field->getAttribute('notnull') === true || $field->getAttribute('required') === true){
				$ci->form_validation->set_rules($field->getId(), ucfirst($field->getName()), "xss_clean|required");                }
				else{
					$ci->form_validation->set_rules($field->getId(), ucfirst($field->getName()), "xss_clean");
				}
		}

		if($this->loadPostData() && $ci->form_validation->run() && $ci->input->post('submit') != false){
			return true;
		}
		return false;
	}





	/**
	 * this actionUrl setter
	 * @return null
	 *  
	 */
	public function setActionUrl($actionUrl)
	{
		$this->actionUrl = $actionUrl;
	}
	
	
	
	/**
	 * actionUrl getter
	 * @return string
	 *  
	 */
	public function getActionUrl()
	{
		return $this->actionUrl;
	}


	/**
	 * Given the fieldName, the field label, and the value, returns a formatted output
	 * @param string $fieldKey
	 * @param string $label
	 * @param string $field
	 * @param boolean $useParagraph
	 * @return string
	 *  
	 */
	private function formatField($fieldKey, $label = '', $field, $useParagraph = true)
	{
		$return = form_error($fieldKey, '<span class="form-error" >','</span>').  "\n\t$label $field\n\n";

		if($useParagraph){
			$return = '<p>'.'<span class="form_field_'. $fieldKey.'" >'. $return.'</span>'.'</p>';
		}


		return $return;
	}



	/**
	 * Returns a formated string
	 * @param $string string
	 * @return string
	 *  
	 */
	private function formatLabel($string)
	{
		$output = str_replace('_', ' ', $string);
		$output = ucwords($output);
		return $output;
	}
	
	
	
	/**
	 * Add a FormField to this fields array
	 *
	 * @param FormField $field
	 * @param string $afterField
	 * @return FormField
	 * @throw FormException
	 *  
	 */
	public function addField(FormField $field, $afterField = null)
	{
		//$field->template = $field->template;
		if($afterField == null){
			$this->fields[$field->getId()] = $field;
		}else{
			/**
			 * Put the field orderer after the given $afterField
			 */
			$offset = 0;
			foreach($this->fields as $f){
				$offset++;
				if($f->getId() == $afterField){
					break;
				}
			}
			array_splice($this->fields, $offset,0,array($field->getId() => $field));
		}
		/**
		 * formatting label from field->id
		 */
		$field->setLabel(ucwords(str_replace('_', ' ', $field->getId())));

		return $field;
	}


	/**
	 * Remove FormFields from this fields collection by an array of field ids
	 * @param array $fieldIds
	 * @return null
	 *  
	 */
	public function removeFields(array $fieldIds)
	{
		foreach($fieldIds as $id){
			if(isset($this->fields[$id])){
				unset($this->fields[$id]);
			}
		}
	}
	
	
	/**
	 * Remove FormFields from this fields collection by an array of field ids
	 * @param string $fieldId
	 * @return null
	 *  
	 */
	public function removeField($fieldId)
	{
		unset($this->fields[$fieldId]);
	}


}


?>
