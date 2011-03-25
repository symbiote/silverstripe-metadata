<?php
/**
 * @package silverstripe-metadata
 */
class MetadataDropdownFieldOption extends DataObject {

	public static $db = array(
		'Key'   => 'Varchar(100)',
		'Value' => 'Varchar(255)'
	);

	public static $has_one = array(
		'Parent' => 'MetadataDropdownField'
	);

	public static $summary_fields = array(
		'Key',
		'Value'
	);

	/**
	 * @return string
	 */
	public function getKey() {
		return ($key = $this->getField('Key')) ? $key : $this->Value;
	}

	public function validate() {
		$result = parent::validate();

		if (!$this->Value) {
			$result->error('Each dropdown option must have a value.');
		}

		return $result;
	}

	public function singular_name() {
		return 'Option';
	}

}