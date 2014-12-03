# SilverStripe Metadata Module

## Maintainer Contacts

* Marcus Nyeholt (<marcus@silverstripe.com.au>)

## Requirements

* SilverStripe 3+
* The SilverStripe Orderable module.

## Getting started

* Add the extension to your data class, eg `Object::add_extension('Page', 'MetadataExtension');`
* Navigate to the Metadata section of the CMS (/admin/metadata)
* Create a new Metadata Schema. Note that the 'Title' is what is used to refer to the item from templates, so try and 
  limit this to a-z0-9_-. characters - eg test_schema
* Add a few metadata fields - some usual ones are
  * Title (title)
  * Keywords (keywords)
  * Description (description)
* Set the 'Default' value for each of these to $Title. Leave the 'cascade' setting blank for now, as you're providing
  a default already
* Navigate to a top level page and on its Metadata tab, select the schema you just created; click save
* Enter metadata values
* In your Page.ss template, add the following to output all metadata fields
  * `$MetadataMetaTags` 
* To output just the values for a particular applied schema, use
  * `$MetadataMetaTags(SchemaName)`
* To access raw metadata values directly, use
  * `$Metadata(SchemaName,FieldName)` eg `$Metadata(test_schema,keywords)`

## Project Links
* [GitHub Project Page](https://github.com/ajshort/silverstripe-metadata)
* [Issue Tracker](https://github.com/ajshort/silverstripe-metadata/issues)
