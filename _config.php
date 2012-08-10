<?php
/**
 * @package silverstripe-metadata
 */

// In order to add the ability to apply metadata to a class, you need to
// apply to MetadataExtension extension:
//
// Object::add_extension('<class>', 'MetadataExtension');

if (!class_exists('GridFieldSortableRows')) {
	throw new Exception('The Metadata module required the SortableGridField module.');
}

define('METADATA_DIR', 'metadata');