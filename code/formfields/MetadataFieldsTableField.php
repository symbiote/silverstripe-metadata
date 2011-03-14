<?php
/**
 * An extension to CTF to allow creating different metadata field subclasses.
 *
 * @package silverstripe-metadata
 */
class MetadataFieldsTableField extends OrderableComplexTableField {

	/**
	 * @return Form
	 */
	public function AddForm() {
		$fields = new FieldSet(new TabSet('Root', new Tab('Main',
			new LiteralField('SelectFieldType', sprintf(
				'<p>Please select a field type to add:</p>'
			)),
			new DropdownField('ClassName', '', $this->getFieldTypes(), null, null, true)
		)));

		return new $this->popupClass(
			$this,
			'AddForm',
			$fields,
			new RequiredFields('ClassName'),
			false,
			new MetadataField()
		);
	}

	/**
	 * @return array
	 */
	protected function getFieldTypes() {
		$classes = ClassInfo::subclassesFor('MetadataField');
		$result  = array();

		array_shift($classes);
		foreach ($classes as $class) {
			$result[$class] = singleton($class)->getFieldTitle();
		}

		return $result;
	}

	public function saveComplexTableField($data, $form, $params) {
		$class = $data['ClassName'];

		if (!is_subclass_of($class, 'MetadataField')) {
			$form->addErrorMessage('ClassName', 'An invalid field type was selected');
			return Director::redirectBack();
		}

		$child = new $class();
		$child->SchemaID = $this->controller->ID;
		$child->write();

		$link = SecurityToken::inst()->addToUrl(Controller::join_links(
			$this->Link(), 'item', $child->ID, 'edit'
		));

		Session::set('FormInfo.ComplexTableField_Popup_DetailForm.formError', array(
			'message' => 'The metadata field has been added, please edit it below:',
			'type'    => 'good'
		));

		return Director::redirect($link);
	}

}