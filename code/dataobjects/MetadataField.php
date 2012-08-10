<?php
/**
 * A field that is attached to a specific schema - this is a pseudo-abstract
 * class and must be extended.
 *
 * @package silverstripe-metadata
 */
class MetadataField extends DataObject {

	public static $db = array(
		'Name'     	=> 'Varchar(100)',
		'Title'    	=> 'Varchar(255)',
		'Required' 	=> 'Boolean',
		'Cascade'  	=> 'Boolean',
		'Default'  	=> 'Text',
		'Sort'  	=> 'Int'
	);

	public static $indexes = array(
		'Name_SchemaID' => array('type' => 'unique', 'value' => 'Name,SchemaID')
	);

	public static $has_one = array(
		'Schema' => 'MetadataSchema'
	);

	public static $field_labels = array(
		'Name'    => 'Field name',
		'Title'   => 'Title (human readable name)',
		'Cascade' => 'Cascade to child objects without a value set',
	);

	public static $summary_fields = array(
		'Name',
		'Title',
		'Type'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', new ReadOnlyField('FieldType', 'Field Type', $this->Type()), 'Name');
		$fields->removeByName('Sort');
		$fields->removeByName('SchemaID');
		return $fields;
	}

	/**
	 * Returns the title that describes the field type.
	 *
	 * @abstract
	 * @return string
	 */
	public function getFieldTitle() {
		throw new Exception(
			'You must implemented getFieldTitle() in your metadata field type.'
		);
	}

	/**
	 * Returns a form field instance allowing the user to input a metadata
	 * value.
	 *
	 * @abstract
	 * @return FormField
	 */
	public function getFormField() {
		throw new Exception(
			'You must implemented getFormField() in your metadata field type.'
		);
	}

	/**
	 * Processes a field value before it is saved to the database, and returns
	 * the value.
	 *
	 * @param  string $value
	 * @param  DataObject $record
	 * @return mixed
	 */
	public function processBeforeWrite($value, $record) {
		return $value;
	}

	/**
	 * Processes a field value, and returns the output that should be rendered
	 * into a template.
	 *
	 * @param  string $value
	 * @param  DataObject $record
	 * @return mixed
	 */
	public function process($value, $record) {
		return $value;
	}

	/**
	 * Checks if a certain field value is valid.
	 *
	 * @param  string $value
	 * @param  Validator $validator
	 */
	public function validateValue($value, $validator) {
		if(!$this->Required) return;

		if(is_array($value)) return; //  eg. checkbox set values

		if (!strlen($value)) {
			$validator->validationError('MetadataRaw', sprintf(
				'The metadata field "%s" on the "%s" schema is required',
				$this->Title, $this->Schema()->Title
			), 'validation');
		}
	}

	/**
	 * Returns the form field name to use for the metadata field.
	 *
	 * @return string
	 */
	public function getFormFieldName() {
		return sprintf('MetadataRaw[%s][%s]', $this->Schema()->Name, $this->Name);
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return ($title = $this->getField('Title')) ? $title : $this->Name;
	}

	public function getValidator() {
		return new RequiredFields('Name');
	}

	public function validate() {
		$result = parent::validate();

		if (preg_match('/[^.a-zA-Z0-9_]+/', $this->Name)) {
			$result->error(
				'The field name can only contain alphanumeric characters,'
				. ' underscores and periods.'
			);
		}

		$other = DataObject::get_one('MetadataField', sprintf(
			'"Name" = \'%s\' AND "SchemaID" = %d %s',
			Convert::raw2sql($this->Name), $this->SchemaID,
			($this->ID ? "AND \"MetadataField\".\"ID\" <> {$this->ID}" : '')
		));

		if ($other) {
			$result->error(
				"The name \"{$this->Name}\" is already in use on this schema, "
				. ' please choose another one.'
			);
		}

		return $result;
	}


	/**
	 * @return string - label for descibing the type of field (for $summary_fields)
	 */
	public function Type(){
		return str_replace('Metadata', '', $this->ClassName);
	}


	public function onBeforeWrite(){
		parent::onBeforeWrite();

		if(!$this->Title){
			$this->Title = 'New ' . $this->ClassName;
		}
	}

}