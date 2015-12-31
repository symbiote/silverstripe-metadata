<?php

/**
 * @author <marcus@silverstripe.com.au>
 * @license BSD License http://www.silverstripe.org/bsd-license
 */
class SolrMetadataExtension extends DataExtension
{
    
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
    }
    
    public function updateSolrSearchableFields(&$fields)
    {
        if ($this->owner->hasExtension('MetadataExtension')) {
            $all = $this->owner->getAllMetadata();
            foreach ($all as $schema => $fields) {
                foreach ($fields as $key => $val) {
                    if (strlen($val)) {
                        $fields[$key] = true;
                    }
                }
            }
        }
    }
    
    public function additionalSolrValues()
    {
        $fields = array();
        if ($this->owner->hasExtension('MetadataExtension')) {
            foreach ($this->owner->getSchemas() as $schema) {
                foreach ($schema->Fields() as $field) {
                    $value = $this->owner->Metadata($schema, $field);

                    if (!$value || ($value instanceof DBField && !$value->hasValue())) {
                        continue;
                    }

                    if (is_object($value)) {
                        $value = $value instanceof DBField ? $value->Nice() : $value->getTitle();
                    }
                    
                    if ($field instanceof MetadataSelectField) {
                        $value = explode(',', $value);
                    }

                    if (is_array($value) || strlen($value)) {
                        $fields[$field->Name] = $value;
                    }
                }
            }
        }
        return $fields;
    }
}
