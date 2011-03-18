<?php
/**
 * @package silverstripe-metadata
 */
class MetadataHtmlField extends MetadataTextField {

	public static $defaults = array(
		'Rows' => 10
	);

	public function getFieldTitle() {
		return 'HTML Field';
	}

	/**
	 * @return HtmlEditorField
	 */
	public function getFormField() {
		return new HtmlEditorField($this->getFormFieldName(), $this->Title, $this->Rows);
	}

}