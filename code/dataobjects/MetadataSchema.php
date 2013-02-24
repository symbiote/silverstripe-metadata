<?php
/**
 * A metadata schema with a number of fields that can be attached to an object.
 *
 * @package silverstripe-metadata
 */
class MetadataSchema extends DataObject {

	public static $db = array(
		'Name'        => 'Varchar(100)',
		'Title'       => 'Varchar(255)',
		'Description' => 'Text'
	);

	public static $indexes = array(
		'NameUnique' => array('type' => 'unique', 'value' => 'Name')
	);

	public static $has_many = array(
		'Fields' => 'MetadataField',
		'Links'  => 'MetadataSchemaLink'
	);

	public static $default_sort = '"Title"';

	public static $summary_fields = array(
		'Name',
		'Title',
		'DescriptionSummary'
	);

	public static $searchable_fields = array(
		'Name',
		'Title',
		'Description'
	);

	/**
	 * @return FieldList
	 */
	public function getFormFields() {
		$fields = new FieldList();

		foreach ($this->Fields()->sort('Sort') as $field) {
			$fields->push($field->getFormField());
		}

		return $fields;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->insertBefore(
			new HeaderField('SchemaDetailsHeader', 'Schema Details'), 'Name'
		);
		$fields->dataFieldByName('Description')->setRows(3);

		if ($this->isInDB()) {
			$fields->removeByName('Fields');
			$fields->removeByName('Links');

			$gridFieldConfig = GridFieldConfig::create()->addComponents(
				new GridFieldAddNewMetadataFieldButton(),
				new GridFieldFilterHeader(),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(15),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldDetailForm(),
				new GridFieldOrderableRows('Sort'),
				new MetaDataFieldAddForm
			);

			$gridField = new GridField('Fields', 'MetaData Fields', $this->Fields()->sort('Sort'), $gridFieldConfig);

			$fields->addFieldsToTab('Root.Main', array(
				new HeaderField('MetadataFieldsHeader', 'Metadata Fields'),
				//new MetadataFieldsTableField($this, 'Fields', 'MetadataField')
				$gridField
			));
		} else {
			$fields->addFieldToTab('Root.Main', new LiteralField(
				'AddFieldsOnceSavedNote',
				'<p>You can add metadata fields once you save for the first time.</p>'
			));
		}



		return $fields;
	}

	/**
	 * @return RequiredFields
	 */
	public function getCMSValidator() {
		return new RequiredFields('Name', 'Title');
	}

	public function validate() {
		$result = parent::validate();

		if (preg_match('/[^.a-zA-Z0-9_]+/', $this->Name)) {
			$result->error(
				'The schema name can only contain alphanumeric characters,'
				. ' underscores and periods.'
			);
		}

		$other = DataObject::get_one('MetadataSchema', sprintf(
			'"Name" = \'%s\' %s',
			Convert::raw2sql($this->Name),
			($this->ID ? "AND \"MetadataSchema\".\"ID\" <> {$this->ID}" : '')
		));

		if ($other) {
			$result->error(
				"The name \"{$this->Name}\" is already in use, please choose another one."
			);
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function DescriptionSummary() {
		return $this->obj('Description')->LimitCharacters(150);
	}

}