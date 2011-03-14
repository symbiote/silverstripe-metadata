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

}