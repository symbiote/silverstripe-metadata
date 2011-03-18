<?php
/**
 * @package silverstripe-metadata
 */
class MetadataRelationField extends MetadataField {

	public static $db = array(
		'SubjectClass' => 'Varchar(100)'
	);

	public function getFieldTitle() {
		return 'Related Object Field';
	}

	/**
	 * @return DropdownField
	 */
	public function getFormField() {
		$class = $this->SubjectClass;
		$title = singleton($class)->hasField('Title') ? 'Title' : 'Name';
		$map   = new SQLMap(singleton($class)->extendedSQL(), 'ID', $title);

		return new DropdownField(
			$this->getFormFieldName(),
			$this->Title,
			$map,
			null, null, ' ');
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Subject');
		$fields->removeByName('Default');

		$classes  = ClassInfo::subclassesFor('DataObject');
		$subjects = array();

		array_shift($classes);
		sort($classes);

		foreach ($classes as $class) {
			$subjects[$class] = singleton($class)->singular_name() . " ($class)";
		}

		$subject = new DropdownField(
			'SubjectClass', 'Relationship subject class', $subjects, null, null, true
		);
		$fields->addFieldToTab('Root.Main', $subject, 'Required');

		return $fields;
	}

	public function getValidator() {
		return new RequiredFields('Name', 'Title', 'SubjectClass');
	}

}