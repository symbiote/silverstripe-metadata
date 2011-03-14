<?php
/**
 * @package silverstripe-metadata
 */
class MetadataDateField extends MetadataField {

	public static $db = array(
		'Type' => 'Enum("datetime, date, time", "datetime")'
	);

	public function getFieldTitle() {
		return 'Date/Time Field';
	}

	public function getFormField() {
		switch ($this->Type) {
			case 'datetime':
				$field = new DatetimeField($this->getFormFieldName(), $this->Title);
				$field->getDateField()->setConfig('showcalendar', true);
				$field->getTimeField()->setConfig('showdropdown', true);
				return $field;

			case 'date':
				$field = new DateField($this->getFormFieldName(), $this->Title);
				$field->setConfig('showcalendar', true);
				return $field;

			case 'time':
				$field = new TimeField($this->getFormFieldName(), $this->Title);
				$field->setConfig('showdropdown', true);
				return $field;
		}
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', new OptionsetField(
			'Type', 'Field type', array(
				'datetime' => 'A combination date and time field',
				'date'     => 'Date only',
				'time'     => 'Time only'
			)
		));

		return $fields;
	}

}