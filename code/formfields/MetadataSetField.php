<?php
/**
 * A form field that renders a set of accordions for editing metadata, then
 * saves the serialized data into a single field.
 *
 * @package silverstripe-metadata
 */
class MetadataSetField extends FormField {

	/**
	 * @var DataObject
	 */
	protected $parent;

	public function __construct($parent, $name) {
		if (!$parent->hasExtension('MetadataExtension')) {
			throw new Exception('The parent class must have the metadata extension.');
		}

		$this->parent = $parent;
		parent::__construct($name);
	}

	public function saveInto(DataObjectInterface $record) {
		$schemas  = $record->getSchemas();
		$value    = $this->Value();
		$metadata = array();

		foreach ($schemas as $schema) {
			$metadata[$schema->Name] = array();
			$fields = $schema->getFormFields();
			$namesMap = array();

			foreach ($fields as $field) {
				$brPos = strrpos($field->getName(), '[');
				$name  = substr($field->getName(), $brPos + 1, -1);

				$namesMap[$field->getName()] = $name;
			}

			if (isset($value[$schema->Name])) foreach ($fields as $field) {
				$fName = $field->getName();
				$sName = $namesMap[$fName];

				if (array_key_exists($sName, $value[$schema->Name])) {
					$field->setValue($value[$schema->Name][$sName], $value[$schema->Name]);
				} else {
					$field->setValue(null);
				}
			}

			foreach ($fields as $field) {
				$name        = $namesMap[$field->getName()];
				$schemaField = $schema->Fields()->find('Name', $name);
				$toSave      = $schemaField->processBeforeWrite($field->dataValue(), $record);

				$metadata[$schema->Name][$name] = $toSave;
			}
		}

		$record->{$this->name} = serialize($metadata);
	}

	/**
	 * @param Validator $validator
	 */
	public function validate($validator) {
		foreach ($this->parent->getSchemas() as $schema) {
			foreach ($schema->Fields() as $field) {
				if (isset($this->value[$schema->Name][$field->Name])) {
					$value = $this->value[$schema->Name][$field->Name];
				} else {
					$value = null;
				}

				$field->validateValue($value, $validator);
			}
		}
	}

	public function FieldHolder($properties = array()) {

		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery/jquery.js');
		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery-ui/jquery-ui.js');
		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript('metadata/javascript/MetadataSetField.js');
		Requirements::css(FRAMEWORK_DIR . '/thirdparty/jquery-ui-themes/smoothness/jquery.ui.css');
		Requirements::css('metadata/css/MetadataSetField.css');

		return $this->renderWith('MetadataSetField');
	}

	/**
	 * @return ArrayList
	 */
	public function Schemas() {
		$record  = $this->form->getRecord();
		if (!$record) {
			return;
		}
		$schemas = $record->getSchemas();
		$result  = new ArrayList();

		foreach ($schemas as $schema) {
			$fields = new ArrayList();

			foreach ($schema->getFormFields($record) as $field) {
				$brPos  = strrpos($field->getName(), '[');
				$scName = substr($field->getName(), $brPos + 1, -1);

				$field->setValue($record->getRawMetadataValue($schema->Name, $scName));
				$fields->push($field);
			}

			$result->push(new ArrayData(array(
				'Link'		  => Controller::join_links('admin/metadata/MetadataSchema/EditForm/field/MetadataSchema/item', $schema->ID, 'edit'),
				'Title'       => $schema->Title,
				'Description' => $schema->Description,
				'Fields'      => $fields
			)));
		}

		return $result;
	}

	/**
	 * @return DataObjectSet
	 */
	public function Keywords() {
		return $this->getKeywordsFor($this->form->getRecord());
	}

	/**
	 * @return DataObjectSet
	 */
	public function MemberKeywords() {
		return $this->getKeywordsFor(Member::currentUser());
	}

	protected function getKeywordsFor($record) {
		$result = new ArrayList();

		foreach ($record->toMap() as $name => $value) {
			$result->push(new ArrayData(array(
				'Name'  => $name,
				'Label' => $record->fieldLabel($name)
			)));
		}

		return $result;
	}

}