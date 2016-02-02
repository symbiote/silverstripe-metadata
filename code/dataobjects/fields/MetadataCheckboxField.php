<?php
/**
 * @package silverstripe-metadata
 */
class MetadataCheckboxField extends MetadataField
{

    public function getFieldTitle()
    {
        return 'Checkbox Field';
    }

    /**
     * @return CheckboxField
     */
    public function getFormField()
    {
        return new CheckboxField($this->getFormFieldName(), $this->Title, $this->Default);
    }

    /**
     * @return Boolean
     */
    public function process($value, $record)
    {
        return DBField::create_field('Boolean', $value);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Required');
        $fields->replaceField('Default', new CheckboxField('Default'));

        return $fields;
    }
}
