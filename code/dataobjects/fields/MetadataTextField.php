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

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldsToTab('Root.Options', array(
			new NumericField('Rows', 'Number of rows')
		));

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

}