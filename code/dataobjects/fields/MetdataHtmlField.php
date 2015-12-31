<?php
/**
 * @package silverstripe-metadata
 */
class MetadataHtmlField extends MetadataTextField
{

    private static $defaults = array(
        'Rows' => 10
    );

    public function getFieldTitle()
    {
        return 'HTML Field';
    }

    /**
     * @return HtmlEditorField
     */
    public function getFormField()
    {
        $field = new HtmlEditorField($this->getFormFieldName(), $this->Title, $this->Rows);
        $field->setRightTitle(sprintf(
            '<a href="#" class="ss-metadatasetfield-showreplacements">Available keyword replacements</a>'
        ));

        return $field;
    }
}
