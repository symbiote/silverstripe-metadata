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
	 * @return FieldSet
	 */
	public function getFormFields() {
		$fields = new FieldSet();

		foreach ($this->Fields() as $field) {
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

			$fields->addFieldsToTab('Root.Main', array(
				new HeaderField('MetadataFieldsHeader', 'Metadata Fields'),
				new MetadataFieldsTableField($this, 'Fields', 'MetadataField')
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

	/**
	 * @return string
	 */
	public function DescriptionSummary() {
		return $this->obj('Description')->LimitCharacters(150);
	}

}