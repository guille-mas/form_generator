<?php
require_once dirname(__FILE__).'/AbstractModelForm.php';

/**
 * class that implements a common basic constructor and methods to retrieve data from a DoctrineForm
 *
 * @author Guillermo Maschwitz <guillermo.maschwitz@gmail.com>
 */
class DoctrineForm extends AbstractModelForm{

	/**
	 * adding features to parent::loadPostData()
	 * @return boolean
	 *  
	 */
	public function loadPostData()
	{
		$return = parent::loadPostData();

		$ci =& get_instance();

		if($ci->input->post('id') !== false){

			foreach($this->fields as $field){
				$postedData = $ci->input->post($field->getId());
				if($postedData === false && $field->hasAttribute('DoctrineRelation')){

					switch($field->getType()){
						case 'array':
							$field->setValue(array());
							break;
						default:
							$field->setValue(null);
							break;
					}

					$return = true;
				}
			}
		}

		return $return;
	}




	/**
	 * Given this->object attribute <> NULL , set each Formfield value to the value of each object attribute
	 * @return null
	 *  
	 */
	function hydrateFormWithObjectAttributes()
	{
		if(!isset($this->object)){
			throw new FormException('Unable to load object attributes to hydrate form');
		}

		foreach($this->getFields() as $field){
			$fieldId = $field->getId();
			if(isset($this->object->$fieldId) or $field->hasAttribute('DoctrineRelation')){

				if($field->hasAttribute('DoctrineRelation')){
					/**
					 * if this field has a Doctrine_Relation object as attribute
					 */
					$assocClass = $field->getAttribute('DoctrineRelation')->getAlias();

					$values = array();
					if($this->object->get($assocClass) != null){
						foreach($this->object->$assocClass as $assoc){
							if(isset($assoc->id)){
								$values[] = $assoc->id;
							}else{
								$values[] = $assoc->id_string;
							}
						}
						$field->setValue($values);
					}
				}else{
					/**
					 * if the attribute is a local field
					 */
					$this->getField($fieldId)->setValue($this->object->$fieldId);
				}

			}
		}
	}





	/**
	 * Add fields to the Form instance from a Doctrine model definition
	 * @return null
	 *  
	 */
	public function loadFieldsFromModel(){
		$table = Doctrine::getTable($this->modelClass);
		$tableDefinition = $table->getColumns();

		if(count($this->usefulFields) > 0){
			foreach($tableDefinition as $key=>$attributes){
				if(array_key_exists($key, $this->usefulFields)){
					$rawFieldsDefinition[$key] = $attributes;
				}
			}
		}else{
			$rawFieldsDefinition = $tableDefinition;
		}

		/**
		 * loading fields
		 */
		foreach($rawFieldsDefinition as $key => $attributes){
			$options = null;
			if(isset($attributes['values'])){
				$options = array_combine($attributes['values'] , $attributes['values'] );
			}

			if($key != 'created_at' and $key != 'updated_at'){
				$newField = $this->addField(new FormField($key, $attributes['type'], $attributes, $options));
				if(isset($attributes['default'])){
					$newField->setValue($attributes['default']);
				}
			}
		}

	}



	/**
	 * Persist the nested active record object into database
	 * Also support many-to-many relationships
	 * @return boolean
	 *  
	 */
	public function save()
	{
		$this->preSave();

		if(!$this->hasObject()){
			$this->object = new $this->modelClass();
		}


		foreach($this->getFields() as $field){
			$key = $field->getId();

			if(isset($this->object->$key) && $key != 'id' && $key != 'Id' && $field->getType() != 'file' && !$field->hasAttribute('DoctrineRelation')){
				$this->object->$key = $field->getValue();
			}
		}

		foreach($this->getFields() as $field){
			if($field->hasAttribute('DoctrineRelation')){

				$relation = $field->getAttribute('DoctrineRelation');
				if($relation->getType() == Doctrine_Relation::MANY){

					/**
					 * fetch many to many association table definition
					 */
					$assocTable = $relation->getAssociationFactory();
					$assocClass = $assocTable->getClassNameToReturn();
					$relationAlias = $relation->getAlias();
					$relatedClass = $relation->getClass();

					/**
					 * the association table column for $this->object
					 */
					$foreignLocalColumn = $relation->getLocalColumnName();

					/**
					 * the association table column for the related object
					 */
					$foreignForeignColumn = $relation->getForeignFieldName();

					/**
					 * dirty hack TODO: get column from model instead to specify it at Form class or by a default value
					 */
					if($field->hasAttribute('DoctrineRelationHack')){
						$attr = $field->getAttribute('DoctrineRelationHack');
						$columnWhereToPutValue = $attr['localKeyColumn'];
					}else{
						$columnWhereToPutValue = 'id';
					}

					/**
					 * erase old associated records from database
					 */
					$this->object->unlink($relationAlias);

					/**
					 * if value is not an array, make it an array
					 */
					if(!is_array($field->getValue())){
						$values = array();
						$values[] = $field->getValue();
					}else{
						$values = $field->getValue();
					}

					$this->object->link($relationAlias,$values);

				}elseIf($relation->getType() == Doctrine_Relation::ONE){
					/**
					 * TODO: implement ONE-TO-ONE relationship feature
					 */
				}
			}
		}

		/**
		 * now if there's any field of type FILE, save images to specified field upload path
		 */
		$isMultipart = false;
		foreach($this->fields as $field){
			if($field->getType() == 'file'){
				$isMultipart = true;
				break;
			}
		}
		if($isMultipart){
			if(!$this->object->exists()){
				$this->object->trySave();
			}
			foreach($this->fields as $field){
				if($field->getType() == 'file' and isset($this->object)){
					$this->preUpload();
					$this->uploadFile($field);
				}
			}
		}

		try{
			$this->object->save();
		}catch(Exception $e){
			log_message('error',get_class($this).' error: '.$e->getMessage());
			return false;
		}
		return true;
	}



	/**
	 * Template method to hook into the process of uploading files
	 *  
	 */
	protected function preSave(){}



	/**
	 * Load relations data from model definition
	 * @return null
	 *  
	 */
	public function loadRelationsDataFromModel(){
		$table = Doctrine::getTable($this->modelClass);

		/**
		 * loading relations
		 */
		$tableRelations = $table->getRelations();
		foreach($tableRelations as $relation){
			$assocClass = $relation->getAlias();

			/**
			 * if field with same name as association exist
			 */
			if($this->hasField($assocClass) and $relation->getType() == Doctrine_Relation::MANY){
				/**
				 * if association is of type MANY
				 */
				$field = $this->getField($assocClass)
				->addAttribute('DoctrineRelation',$relation);

				$field->addOptions(AdminForm::fetchItemsForSelectBox($relation->getClass()));

			}elseif($relation->getType() == Doctrine_Relation::ONE){
				/**
				 * TODO: ONE-TO-MANY AND ONE-TO-ONE RELATIONS
				 */
			}
		}

	}



	/**
	 * return an array of pairs key=>value from a model class collection
	 * @param string $modelClass
	 * @param string $assocPrimaryKey
	 * @param string $assocLabelColumn
	 * @return array
	 *  
	 */
	public static function fetchItemsForSelectBox($modelClass, $assocPrimaryKey = null, $assocLabelColumn = null)
	{
		$table = Doctrine::getTable($modelClass);


		if($assocPrimaryKey == null){
			/**
			 * looking for primary autoincrement value field
			 */
			foreach($table->getColumns() as $colName => $colAttributes){
				if(isset($colAttributes['primary'])){
					$assocPrimaryKey = $colName;
					break;
				}
			}
		}

		if(!isset($assocPrimaryKey)){
			throw new FormException('Not primaryKey field specified. Also couldnt choose one for default. Please specify the second argument');
			return;
		}


		if($assocLabelColumn == null){
			foreach($table->getColumns() as $colName => $colAttributes){
				if($colAttributes['type'] == 'string' and $colAttributes['notnull']){
					$assocLabelColumn = $colName;
					break;
				}
			}
		}

		$q = Doctrine_Query::create()
		->select("r.$assocPrimaryKey as value, r.$assocLabelColumn as label")
		->from($modelClass.' r')
		->orderBy('label');

		$q->setHydrationMode(Doctrine::HYDRATE_ARRAY);

		$result = $q->execute();

		$output = array();
		if(is_array($result)){
			foreach($result as $row){
				$output[$row['value']] = $row['label'];
			}
		}

		return $output;
	}
	
	
	
	/**
	 * Load an object of class $this->className by id into this object attribute
	 * @param int $id
	 *  
	 */
	public function loadObjectById($id)
	{
		$this->object = Doctrine::getTable($this->modelClass)->find($id);
		if(!($this->object instanceOf $this->modelClass)){
			throw new FormException("record with id $id not found");
		}
		foreach($this->object->toArray() as $key => $value){
			if(isset($this->fields[$key])){
				$this->getField($key)->setValue($value);
			}
		}
		$this->hydrateFormWithObjectAttributes();
	}
}

?>
