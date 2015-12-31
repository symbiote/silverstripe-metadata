<?php
/**
 * @package silverstripe-metadata
 */
class MetadataSelectField extends MetadataField
{

    private static $db = array(
        'Type'      => 'Enum("dropdown, optionset, checkboxset", "dropdown")',
        'EmptyMode' => 'Enum("none, blank, text")',
        'EmptyText' => 'Varchar(100)'
    );

    private static $has_many = array(
        'Options' => 'MetadataSelectFieldOption'
    );

    private static $defaults = array(
        'EmptyMode' => 'blank'
    );

    public function getFieldTitle()
    {
        return 'Select Field';
    }

    public function getFormField()
    {
        switch ($this->Type) {
            case 'dropdown':
                switch ($this->EmptyMode) {
                    case 'none':  $emptyText = false; break;
                    case 'blank': $emptyText = ' '; break;
                    case 'text':  $emptyText = $this->EmptyText; break;
                }

                $opts = $this->Options()->map('Key', 'Value')->toArray();
                
                $df = new DropdownField(
                    $this->getFormFieldName(),
                    $this->Title,
                    $opts,
                    $this->Default,
                    null);
                
                if (is_string($emptyText)) {
                    $df->setEmptyString($emptyText);
                }
                
                return $df;

            case 'optionset':
                return new OptionsetField(
                    $this->getFormFieldName(),
                    $this->Title,
                    $this->Options()->map('Key', 'Value'),
                    $this->Default);

            case 'checkboxset':
                return new CheckboxSetField(
                    $this->getFormFieldName(),
                    $this->Title,
                    $this->Options()->map('Key', 'Value'),
                    $this->Default);
        }
    }

    public function getCMSFields()
    {
        Requirements::javascript(METADATA_DIR . '/javascript/MetadataSelectFieldCms.js');

        $fields = parent::getCMSFields();

        $fields->removeByName('Options');
        $fields->removeByName('Default');
        $fields->removeByName('EmptyMode');
        $fields->removeByName('EmptyText');

        $default = $this->getFormField();
        $default->setName('Default');
        $default->setTitle('Default option(s)');
        $default->setValue($this->Default);

        $gridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldAddNewButton(),
            new GridFieldFilterHeader(),
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldPaginator(15),
            new GridFieldEditButton(),
            new GridFieldDeleteAction(),
            new GridFieldDetailForm(),
            new GridFieldSortableRows('Sort'),
            new MetaDataFieldAddForm
        );
        $gridField = new GridField('Options', 'Options', $this->Options(), $gridFieldConfig);

        $fields->addFieldsToTab('Root.Main', array(
            new DropdownField('Type', 'Field type', array(
                'dropdown'    => 'Dropdown select field',
                'optionset'   => 'Set of radio options',
                'checkboxset' => 'Checkbox set field (allows multiple selection)'
            ))
        ));

        $fields->addFieldsToTab('Root.Options', array(
            $default,
            new OptionsetField('EmptyMode', 'Empty first option', array(
                'none'  => 'Do not display an empty default option',
                'blank' => 'Display an empty option as the first option',
                'text'  => 'Display an empty option with text as the first option'
            )),
            new TextField('EmptyText', 'Empty Text'),
            new HeaderField('OptionsHeader', 'Options'),
            $gridField
        ));

        
        return $fields;
    }
}
