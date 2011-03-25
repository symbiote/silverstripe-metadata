<?php
/**
 * @package silverstripe-metadata
 */
class MetadataSelectField extends MetadataField {

	public static $db = array(
		'Type'      => 'Enum("dropdown, optionset, checkboxset", "dropdown")',
		'EmptyMode' => 'Enum("none, blank, text")',
		'EmptyText' => 'Varchar(100)'
	);

	public static $has_many = array(
		'Options' => 'MetadataSelectFieldOption'
	);

	public static $defaults = array(
		'EmptyMode' => 'blank'
	);

	public function getFieldTitle() {
		return 'Select Field';
	}

	public function getFormField() {
		switch ($this->Type) {
			case 'dropdown':
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

			case 'optionset':
				return new OptionsetField(
					$this->getFormFieldName(),
					$this->Title,
					$this->Options()->map('Key', 'Value'),
					$this->Default);

			case 'checkboxset':
				return new CheckboxSetField(
					$this->getFormFieldName(),
					$this->Title,
					$this->Options()->map('Key', 'Value'),
					$this->Default);
		}
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Options');
		$fields->removeByName('Default');
		$fields->removeByName('EmptyMode');
		$fields->removeByName('EmptyText');

		$default = $this->getFormField();
		$default->setName('Default');
		$default->setTitle('Default option(s)');
		$default->setValue($this->Default);

		$fields->addFieldsToTab('Root.Main', array(
			new OptionsetField('Type', 'Field type', array(
				'dropdown'    => 'Dropdown select field',
				'optionset'   => 'Set of radio options',
				'checkboxset' => 'Checkbox set field (allows multiple selection)'
			)),
			new TableField(
				'Options',
				'MetadataSelectFieldOption',
				null,
				array(
					'Key'   => 'TextField',
					'Value' => 'TextField'
				),
				'ParentID',
				$this->ID
			),
			$default,
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
		Requirements::javascript(METADATA_DIR . '/javascript/MetadataSelectFieldCms.js');
	}

}