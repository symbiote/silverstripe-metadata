<?php
/**
 * @package silverstripe-metadata
 */
class MetadataRelationField extends MetadataField {

	private static $db = array(
		'SubjectClass'		=> 'Varchar(100)',
		'SelectAny'			=> 'Boolean',
		'ReturnValue'		=> "Enum('Link,Title,Default','Default')"
	);

	public function getFieldTitle() {
		return 'Related Object Field';
	}

	/**
	 * @return DropdownField
	 */
	public function getFormField($record = null) {
		$class = $this->SubjectClass;
		$title = singleton($class)->hasField('Title') ? 'Title' : 'Name';
		
		$objects = null;
		
		if (!$this->SelectAny && $record) {
			$objects = ArrayList::create();
			if ($hasOnes = $record->has_one()) {
				foreach($hasOnes as $name => $type) {
					if (is_a($type, $class, true)) {
						$item = $record->$name();
						if ($item->ID && $item->canView()) {
							$objects->push($item);
						}
					}
				}
			}
			if ($manies = $record->many_many()) {
				foreach($manies as $name => $type) {
					if (is_a($type, $class, true)) {
						foreach ($record->$name() as $item) {
							if ($item->ID && $item->canView()) {
								$objects->push($item);
							}
						}
					}
					
				}
			}
		}
		if ($this->SelectAny || !$objects || $objects->count() === 0) {
			$objects = DataObject::get($class);
		}

		$map = $objects ? $objects->map('ID', $title) : array();
		$emptyString = count($map) ? "Select $class" : "No $class objects found";

		return DropdownField::create(
			$this->getFormFieldName(),
			$this->Title,
			$map,
			null, null)->setEmptyString($emptyString);
	}

	/**
	 * @return DataObject
	 */
	public function process($value, $record) {
		if (ctype_digit($value)) {
			$object = DataObject::get_by_id($this->SubjectClass, $value);
			
			switch ($this->ReturnValue) {
				case 'Link': {
					return $object instanceof File ? $object->getAbsoluteURL() : (method_exists($object, 'AbsoluteLink') ? $object->AbsoluteLink() : '');
				}
				case 'Title': {
					return $object->getTitle();
					break;
				}
				case 'Default': {
					return $object;
				}
			}
			return $object;
		}
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

		$subject = DropdownField::create('SubjectClass', 'Relationship subject class', $subjects, null, null)->setHasEmptyDefault(true);
		$fields->addFieldToTab('Root.Main', $subject, 'Required');
		
		$fields->dataFieldByName('SelectAny')->setRightTitle('Select any item of this type');

		return $fields;
	}

	public function getValidator() {
		return new RequiredFields('Name', 'Title', 'SubjectClass');
	}

}