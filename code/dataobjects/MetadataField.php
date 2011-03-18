<?php
/**
 * A field that is attached to a specific schema - this is a pseudo-abstract
 * class and must be extended.
 *
 * @package silverstripe-metadata
 */
class MetadataField extends DataObject {

	public static $db = array(
		'Name'     => 'Varchar(100)',
		'Title'    => 'Varchar(255)',
		'Required' => 'Boolean',
		'Default'  => 'Text'
	);

	public static $has_one = array(
		'Schema' => 'MetadataSchema'
	);

	public static $extensions = array(
		'Orderable'
	);

	public static $field_labels = array(
		'Name'  => 'Field name',
		'Title' => 'Title (human-readable name)'
	);

	/**
	 * Returns the title that describes the field type.
	 *
	 * @abstract
	 * @return string
	 */
	public function getFieldTitle() {
		throw new Exception(
			'You must implemented getFieldTitle() in your metadata field type.'
		);
	}

	/**
	 * Returns a form field instance allowing the user to input a metadata
	 * value.
	 *
	 * @abstract
	 * @return FormField
	 */
	public function getFormField() {
		throw new Exception(
			'You must implemented getFormField() in your metadata field type.'
		);
	}

	/**
	 * Returns the form field name to use for the metadata field.
	 *
	 * @return string
	 */
	public function getFormFieldName() {
		return sprintf('MetadataRaw[%s][%s]', $this->Schema()->Name, $this->Name);
	}

	public function getValidator() {
		return new RequiredFields('Name', 'Title');
	}

}