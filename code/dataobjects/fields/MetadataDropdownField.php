<?php
/**
 * @package silverstripe-metadata
 */
class MetadataDropdownField extends MetadataField {

	public static $db = array(
		'EmptyMode' => 'Enum("none, blank, text")',
		'EmptyText' => 'Varchar(100)'
	);

	public static $has_many = array(
		'Options' => 'MetadataDropdownFieldOption'
	);

	public static $defaults = array(
		'EmptyMode' => 'blank'
	);

	public function getFieldTitle() {
		return 'Dropdown Field';
	}

	public function getFormField() {
		switch ($this->EmptyMode) {
			case 'none':  $emptyText = false; break;
			case 'blank': $emptyText = true; break;
			case 'text':  $emptyText = $this->EmptyText; break;
		}

		return new DropdownField(
			$this->getFormFieldName(),
			$this->Title,
			$this->Options()->map('Key', 'Value'),
			$this->Default,
			null,
			$emptyText);
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Options');
		$fields->removeByName('Default');
		$fields->removeByName('EmptyMode');
		$fields->removeByName('EmptyText');

		$fields->addFieldToTab(
			'Root.Main',
			new HeaderField('FieldConfigHeader', 'Field Configuration'),
			'Name'
		);

		$fields->addFieldsToTab('Root.Main', array(
			new HeaderField('DropdownOptionsHeader', 'Dropdown Options'),
			$options = new TableField(
				'Options',
				'MetadataDropdownFieldOption',
				null,
				array(
					'Key'   => 'TextField',
					'Value' => 'TextField'
				),
				'ParentID',
				$this->ID
			),
			new DropdownField(
				'Default',
				'Default option',
				$this->Options()->map('Key', 'Value'),
				null, null, true
			),
			new OptionsetField('EmptyMode', 'Empty first option', array(
				'none'  => 'Do not display an empty default option',
				'blank' => 'Display an empty option as the first option',
				'text'  => 'Display an empty option with text as the first option'
			)),
			new TextField('EmptyText', '')
		));

		return $fields;
	}

	public function getRequirementsForPopup() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('metadata/javascript/MetadataDropdownFieldCms.js');
	}

}