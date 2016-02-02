<?php
/**
 * A link that attaches a metadata schema to an arbitrary object.
 *
 * @package silverstripe-metadata
 */
class MetadataSchemaLink extends DataObject
{

    private static $db = array(
        'ParentClass' => 'Varchar(100)',
        'ParentID'    => 'Int'
    );

    private static $has_one = array(
        'Schema' => 'MetadataSchema'
    );

    /**
     * @return DataObject
     */
    public function getParent()
    {
        return DataObject::get_by_id($this->ParentClass, $this->ParentID);
    }

    public function onBeforeWrite()
    {
        $this->ParentClass = ClassInfo::baseDataClass($this->ParentClass);
        parent::onBeforeWrite();
    }
}
