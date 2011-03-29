<?php
/**
 * An interface to allow the definition and management of metadata schemas.
 *
 * @package silverstripe-metadata
 */
class MetadataAdmin extends ModelAdmin {

	public static $menu_title  = 'Metadata';
	public static $url_segment = 'metadata';

	public static $managed_models  = 'MetadataSchema';
	public static $model_importers = array();

	public static $collection_controller_class = 'MetadataAdmin_CollectionController';

}

class MetadataAdmin_CollectionController extends ModelAdmin_CollectionController {

	/*
	 * Works around in issue with ModelAdmin not validating created objects.
	 */
	public function doCreate($data, $form, $request) {
		// First run the validation so we don't create an empty record if
		// it fails.
		$form->saveInto($schema = new MetadataSchema());
		$result = $schema->validate();

		if (!$result->valid()) {
			$form->sessionMessage($result->message(), 'bad');

			if ($this->isAjax()) {
				return $form->forAjaxTemplate();
			} else {
				return $this->redirectBack();
			}
		}

		return parent::doCreate($data, $form, $request);
	}

}