<?php
/**
 * @package silverstripe-metadata
 */
class MetadataCheckboxField extends MetadataField {

	public function getFieldTitle() {
		return 'Checkbox Field';
	}

	/**
	 * @return CheckboxField
	 */
	public function getFormField() {
		return new CheckboxField($this->getFormFieldName(), $this->Title, $this->Default);
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Required');
		$fields->replaceField('Default', new CheckboxField('Default'));

		return $fields;
	}

}