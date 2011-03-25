<?php
/**
 * @package silverstripe-metadata
 */
class MetadataTextField extends MetadataField {

	public static $db = array(
		'Rows' => 'Int',
	);

	public static $defaults = array(
		'Rows' => 1
	);

	public function getFieldTitle() {
		return 'Text Field';
	}

	protected $record;

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Options', new NumericField(
			'Rows', 'Number of rows'
		));
		$fields->addFieldToTab('Root.Main', new LiteralField(
			'KeywordNote', '<p>Keyword replacements in the form "$FieldName"'
			. ' can be used in the default value, as well as in the actual'
			. ' metadata value. These will be replaced with the corresponding'
			. ' field from the record the schema is applied to.<p>'
		));
		$fields->dataFieldByName('Default')->setRows(3);

		return $fields;
	}

	/**
	 * @return TextField|TextareaField
	 */
	public function getFormField() {
		if ($rows = $this->Rows > 1) {
			return new TextareaField($this->getFormFieldName(), $this->Title, $this->Rows);
		} else {
			return new TextField($this->getFormFieldName(), $this->Title);
		}
	}

	/**
	 * @return string
	 */
	public function process($value, $record) {
		$this->record = $record;

		return preg_replace_callback(
			'/\$([A-Za-z_][A-Za-z0-9_]*)/',
			array($this, 'replaceKeyword'),
			$value
		);
	}

	public function replaceKeyword($matches) {
		$record = $this->record;
		$field  = $matches[1];

		if ($record->$field) {
			return $record->$field;
		} else {
			return '$' . $field;
		}
	}

}