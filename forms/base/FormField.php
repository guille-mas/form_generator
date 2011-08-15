<?php

/**
 * The FormField class
 * @author Guillermo Maschwitz 
 */
class FormField {
	protected $type;
	protected $id;
	protected $name;
	protected $label;
	protected $value;
	protected $attributes = array();
	protected $cssClasses = array();
	protected $options = array();
	public $template = '';
	
	
	/**
	 * constructor
	 * @param string $id
	 * @param string $type
	 * @param array $attributes
	 * @param array $options
	 * @author Guillermo Maschwitz 
	 */
	public function __construct($id, $type = null, $attributes = null, $options = array()){
		$this->setType($type);
		$this->setId($id);
		$this->setName($id);
		$this->setLabel($id);
		
		/*
		 * set template
		 */
		$this->template = FormField::getDefaultTemplateForFieldType($this);
		
		
		if($attributes != null){
			$this->setAttributes($attributes);
		}

		if($options == null){
			$options = array();
		}

		/**
		 * if field can be null add null as another option
		 */
		if((isset($attributes['notnull']) && $attributes['notnull'] == false) || (isset($attributes['default']) && $attributes['default'] == null)){
			$options = array_merge( array(''=>''), $options );
		}

		$this->setOptions($options);
	}
	
	
	/**
	 * 
	 * Return the name of a template by the field type
	 * @todo this logic should be handled at FormField sub classes
	 * @param FormField $type
	 * @return string
	 */
	public static function getDefaultTemplateForFieldType(FormField $field)
	{
		switch($field->getType()){
			case 'boolean':
				$template='checkbox';
				break;
			case 'enum':
				$template='select';
			default:
				$template='text';
				break;
		}
		return $template;
	}
	
	
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getType(){
		return $this->type;
	}
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getId(){
		return $this->id;
	}
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getName(){
		return $this->name;
	}
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getLabel(){
		if($this->getAttribute('notnull')==true or $this->getAttribute('required')==true){
			return $this->label.'*';
		}else{
			return  $this->label;
		}
	}

	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getValue(){
		return $this->value;
	}
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getOptions(){
		return $this->options;
	}
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getAttributes(){
		return $this->attributes;
	}
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getAttribute($attributeName){
		if(isset($this->attributes[$attributeName])){
			return $this->attributes[$attributeName];
		}
	}

	
	/**
	 * Return true if given attribute name is found on instance attributes
	 * @return boolean
	 * @author Guillermo Maschwitz 
	 */
	function hasAttribute($attributeName){
		if(isset($this->attributes[$attributeName])){
			return true;
		}
		return false;
	}
	
	
	/**
	 * Getter
	 * @return string
	 * @author Guillermo Maschwitz 
	 */
	function getClasses(){
		return $this->cssClasses;
	}
	
	
	/**
	 * Return true if given name exists between cssClasses instance attribute
	 * @param string
	 * @return boolean
	 * @author Guillermo Maschwitz 
	 */
	function hasClass($name){
		if(isset($this->cssClasses[$name])){
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * Setter
	 * @param string $arg
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setType($arg){
		$this->type = $arg;
		$this->template = $arg;
		return $this;
	}
	
	
	/**
	 * Setter
	 * @param string $arg
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setId($arg){
		$this->id = $arg;
		return $this;
	}
	
	
	/**
	 * Setter
	 * @param string $arg
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setName($arg){
		$this->name = $arg;
		return $this;
	}
	
	
	/**
	 * Setter
	 * @param string $arg
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setLabel($arg){
		$this->label = $arg;
		return $this;
	}
	
	
	/**
	 * Setter
	 * @param string $arg
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setValue($arg){
		$this->value = $arg;
		return $this;
	}
	
	
	/**
	 * setter
	 * @param array $optionsArray
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setOptions($optionsArray){
		$this->options = $optionsArray;
		return $this;
	}
	
	
	/**
	 * Add an option
	 * @param array $optionsArray
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function addOptions($optionsArray){
		if(!empty($this->options)){
			$this->options = array_merge($this->options, $optionsArray);
		}else{
			$this->options = $optionsArray;
		}

		return $this;
	}
	
	
	/**
	 * Add an option
	 * @param array $optionsArray
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function addAttribute($name,$value){
		$this->attributes[$name] = $value;
		return $this;
	}

	/**
	 * Instance attibutes setter
	 * @param array
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setAttributes(array $attributesArray){
		$this->attributes = $attributesArray;
		return $this;
	}

	/**
	 * Instance attibutes setter for one item
	 * @param array
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function setAttribute($attributeName, $value){
		$this->attributes[$attributeName] = $value;
		return $this;
	}
	
	/**
	 * Add a css class to instance
	 * @param array
	 * @return FormField
	 * @author Guillermo Maschwitz 
	 */
	function addClass($name){
		$this->cssClasses[] = $name;
		return $this;
	}
}


?>
