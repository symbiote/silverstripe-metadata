<?php
/**
 * @package silverstripe-metadata
 */
class MetadataDateField extends MetadataField {

	public static $db = array(
		'Type'        => 'Enum("datetime, date, time", "datetime")',
		'DefaultType' => 'Enum("specific, created", "specific")'
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
				break;

			case 'date':
				$field = new DateField($this->getFormFieldName(), $this->Title);
				$field->setConfig('showcalendar', true);
				break;

			case 'time':
				$field = new TimeField($this->getFormFieldName(), $this->Title);
				$field->setConfig('showdropdown', true);
				break;
		}

		if ($this->DefaultType == 'created') {
			$field->setRightTitle(
				'The value will default to the time this record was created.'
			);
		}

		return $field;
	}

	/**
	 * @return Date
	 */
	public function process($value, $record) {
		switch ($this->Type) {
			case 'datetime': return DBField::create('SS_Datetime', $value);
			case 'date':     return DBField::create('Date', $value);
			case 'time':     return DBField::create('Time', $value);
		}
	}

	public function processBeforeWrite($value, $record) {
		if ($this->DefaultType == 'created' && !$value) {
			return $record->Created;
		} else {
			return $value;
		}
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');
		$default = $this->getFormField();
		$default->setName('Default');
		$default->setTitle('');

		$fields->addFieldsToTab('Root.Main', array(
			new OptionsetField('Type', 'Field type', array(
				'datetime' => 'A combination date and time field',
				'date'     => 'Date only',
				'time'     => 'Time only'
			)),
			new OptionSetField('DefaultType', 'Default to', array(
				'specific' => 'A specific date/time',
				'created'  => 'The time the object was created'
			)),
			$default
		));

		return $fields;
	}

	public function getRequirementsForPopup() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(METADATA_DIR . '/javascript/MetadataDateFieldCms.js');
	}

}