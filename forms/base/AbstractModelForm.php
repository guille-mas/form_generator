<?php
require_once dirname(__FILE__).'/Form.php';

/**
 * Abstract class that implements a common basic constructor and methods to retrieve data from a DoctrineForm
 *
 * @author Guillermo Maschwitz <guillermo.maschwitz@gmail.com>
 */
abstract class AbstractModelForm extends Form{

	protected $object;
	protected $modelClass = null;
	protected $usefulFields = array(); //just to store the id of the fields to add


	/*
	 * Constructor
	 *  
	 */
	public function __construct($modelClassName = null)
	{
		$this->setUp();
			
		if($modelClassName !== null){
			$this->modelClass = $modelClassName;
		}
		/**
		 * set fields to form from the Table definition at the model
		 */
		$this->loadFieldsFromModel();
		$this->loadRelationsDataFromModel();

		$this->postModelLoad();
	}


	/**
	 * Template method to define the form
	 *  
	 */
	protected function setUp(){}

	/**
	 * Template method
	 *  
	 */
	protected function postModelLoad(){}


	/**
	 * Template method to hook into the process of uploading files
	 */
	protected function preUpload(){}


	/**
	 * Template method to hook into the process of uploading files
	 *  
	 */
	protected function preSave(){}


	function setUsefulFields($array){
		foreach($array as $key){
			$this->usefulFields[$key] = null;
		}
	}


	/**
	 * Given this->object attribute <> NULL , set each Formfield value to the value of each object attribute
	 * @return null
	 *  
	 */
	abstract protected function hydrateFormWithObjectAttributes();


	/**
	 * Add fields to the Form instance from a Doctrine model definition
	 *
	 * @return null
	 *  
	 */
	abstract public function loadFieldsFromModel();




	/**
	 * Persist the nested active record object into database
	 * Also support many-to-many relationships
	 *
	 * @return null
	 *  
	 */
	abstract public function save();




	/**
	 * Load relations data from model definition
	 *
	 * @return null
	 *  
	 */
	abstract public function loadRelationsDataFromModel();



	/**
	 * return an array of pairs key=>value from a model class collection
	 *
	 * @param string $modelClass
	 * @param string $assocPrimaryKey is the name of the primary key column
	 * @param string $assocLabelColumn is the name of the column where the label data will be taken
	 *  
	 */
	abstract public static function fetchItemsForSelectBox($modelClass, $assocPrimaryKey = null, $assocLabelColumn = null);



	/**
	 * Load an object of class $this->className by id into this object attribute
	 * @param resource $id
	 *  
	 */
	abstract public function loadObjectById($id);






	/**
	 * ModelClass attribute getter
	 * @return string
	 *  
	 */
	public function getModelClass()
	{
		return $this->modelClass;
	}


	/**
	 * Return an array representation of the instance
	 *  
	 */
	public function toArray()
	{
		$output = parent::toArray();

		if( is_object($this->object) ){
			if( method_exist($this->object,'toArray') ){
				$output['object'] = $this->object->toArray();
			}else{
				$output['object'] = null;
			}
		}else{
			$output['object'] = null;
		}

		return $output;
	}


	/**
	 * Set this->object equal to some object
	 * @param $object
	 * @return null
	 *  
	 */
	public function loadObject($object)
	{
		if($object instanceOf $this->modelClass){
			$this->object = $object;

			foreach($this->object->toArray() as $key => $value){
				if($this->hasField($key)){
					$this->getField($key)->setValue($value);
				}
			}
		}else{
			throw new FormException('wrong parameter! Expecting '.$this->modelClass.' object. '.get_class($object).' given');
		}

		$this->hydrateFormWithObjectAttributes();
	}


	/**
	 * Check if an active record object has been loaded
	 * @return boolean
	 *  
	 */
	public function hasObject()
	{
		if($this->object instanceOf $this->modelClass){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * this object attribute getter
	 * @return Object
	 *  
	 */
	public function getObject()
	{
		if($this->hasObject()){
			return $this->object;
		}
	}

}

?>
