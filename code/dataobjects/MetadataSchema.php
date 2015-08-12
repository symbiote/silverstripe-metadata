<?php
/**
 * A metadata schema with a number of fields that can be attached to an object.
 *
 * @package silverstripe-metadata
 */
class MetadataSchema extends DataObject {

	private static $db = array(
		'Name'        => 'Varchar(100)',
		'Title'       => 'Varchar(255)',
		'Description' => 'Text'
	);

	private static $indexes = array(
		'NameUnique' => array('type' => 'unique', 'value' => 'Name')
	);

	private static $has_many = array(
		'Fields' => 'MetadataField',
		'Links'  => 'MetadataSchemaLink'
	);

	private static $default_sort = '"Title"';

	private static $summary_fields = array(
		'Name',
		'Title',
		'DescriptionSummary'
	);

	private static $searchable_fields = array(
		'Name',
		'Title',
		'Description'
	);
	
	private static $default_schemas = array(
		
	);

	
	/**
	 * @return FieldList
	 */
	public function getFormFields($record = null) {
		$fields = new FieldList();

		foreach ($this->Fields()->sort('Sort') as $field) {
			$fields->push($field->getFormField($record));
		}

		return $fields;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->insertBefore(
			new HeaderField('SchemaDetailsHeader', 'Schema Details'), 'Name'
		);
		$fields->dataFieldByName('Description')->setRows(3);

		if ($this->isInDB()) {
			$fields->removeByName('Fields');
			$fields->removeByName('Links');

			$gridFieldConfig = GridFieldConfig::create()->addComponents(
				new GridFieldAddNewMetadataFieldButton(),
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

			$gridField = new GridField('Fields', 'MetaData Fields', $this->Fields()->sort('Sort'), $gridFieldConfig);

			$fields->addFieldsToTab('Root.Main', array(
				new HeaderField('MetadataFieldsHeader', 'Metadata Fields'),
				//new MetadataFieldsTableField($this, 'Fields', 'MetadataField')
				$gridField
			));
		} else {
			$fields->addFieldToTab('Root.Main', new LiteralField(
				'AddFieldsOnceSavedNote',
				'<p>You can add metadata fields once you save for the first time.</p>'
			));
		}



		return $fields;
	}

	/**
	 * @return RequiredFields
	 */
	public function getCMSValidator() {
		return new RequiredFields('Name', 'Title');
	}

	public function validate() {
		$result = parent::validate();

		if (preg_match('/[^.a-zA-Z0-9_]+/', $this->Name)) {
			$result->error(
				'The schema name can only contain alphanumeric characters,'
				. ' underscores and periods.'
			);
		}

		$other = DataObject::get_one('MetadataSchema', sprintf(
			'"Name" = \'%s\' %s',
			Convert::raw2sql($this->Name),
			($this->ID ? "AND \"MetadataSchema\".\"ID\" <> {$this->ID}" : '')
		));

		if ($other) {
			$result->error(
				"The name \"{$this->Name}\" is already in use, please choose another one."
			);
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function DescriptionSummary() {
		return $this->obj('Description')->LimitCharacters(150);
	}
	
	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		// get schemas that need creating
		$schemas = $this->config()->get('default_schemas');
		
		
		require_once 'spyc/spyc.php';
		
		foreach ($schemas as $file) {
			if (file_exists(Director::baseFolder().'/'.$file)) {
				$parser = new Spyc();

				$factory = new FixtureFactory();

				$fixtureContent = $parser->loadFile(Director::baseFolder().'/'.$file);
				
				if (isset($fixtureContent['MetadataSchema'])) {
					$toBuild = array();
					// check if it exists or not, if so don't re-create it
					foreach ($fixtureContent['MetadataSchema'] as $id => $desc) {
						$name = isset($desc['Name']) ? $desc['Name'] : null;
						if (!$name) {
							throw new Exception("Cannot create metadata schema without a name");
						}
						$existing = MetadataSchema::get()->filter('Name', $name)->first();
						if ($existing) {
							$factory->setId('MetadataSchema', $id, $existing->ID);
						} else {
							$factory->createObject('MetadataSchema', $id, $desc);
							DB::alteration_message('Metadata schema ' . $id . ' created', 'created');
						}
					}
					// don't need this now
					unset($fixtureContent['MetadataSchema']);
					
					// go through and unset any existing fields
					$toBuild = array();

					foreach($fixtureContent as $class => $items) {
						foreach($items as $identifier => $data) {
							$nameField = isset($data['Name']) ? 'Name' : (isset($data['Key']) ? 'Key' : '');
							if (!strlen($nameField)) {
								throw new Exception("Metadata fields must have a Name or Key field defined");
							}
							if (!isset($data['Title'])) {
								$data['Title'] = $data[$nameField];
							}
							
							$existing = $class::get()->filter($nameField, $data[$nameField])->first();
							if ($existing) {
								$factory->setId($class, $identifier, $existing->ID);
							} else {
								$factory->createObject($class, $identifier, $data);
								DB::alteration_message('Metadata field ' . $data[$nameField] . ' created', 'created');
							}
						}
					}
				}
				
				
			}
	
		}
	}

}