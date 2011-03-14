<?php
/**
 * An extension that must be applied to an object in order for it to have the
 * ability to have metadata attached to it.
 *
 * @package silverstripe-metadata
 */
class MetadataExtension extends DataObjectDecorator {

	public function extraStatics() {
		return array(
			'db'        => array('MetadataRaw'     => 'Text'),
			'many_many' => array('MetadataSchemas' => 'MetadataSchema')
		);
	}

	/**
	 * Returns all the schema objects attached to this object, or any of its
	 * parents.
	 *
	 * @return DataObjectSet
	 */
	public function getSchemas() {
		if (!$this->owner->hasExtension('Hierarchy')) {
			return $this->owner->MetadataSchemas();
		}

		$schemas = $this->getInheritedSchemas();
		$schemas->merge($this->owner->MetadataSchemas());
		$schemas->removeDuplicates();
		$schemas->sort('Title');
		return $schemas;
	}

	/**
	 * If this is attached to an object with the hierarchy extension, it returns
	 * a set of a schema objects attached to any ancestors (which should be
	 * present on this object).
	 *
	 * @return DataObjectSet
	 */
	public function getInheritedSchemas() {
		if (!$this->owner->hasExtension('Hierarchy')) {
			return new DataObjectSet();
		}

		$ids      = array();
		$parents  = $this->owner->getAncestors();
		$relation = $this->owner->many_many('MetadataSchemas');

		foreach ($parents as $parent) {
			$ids[] = $parent->ID;
		}

		$result = DataObject::get(
			'MetadataSchema',
			sprintf('"%s"."%s" IN (%s)', $relation[4], $relation[2], implode(', ', $ids)),
			null,
			sprintf('INNER JOIN "%1$s" ON "%1$s"."MetadataSchemaID" = "MetadataSchema"."ID"', $relation[4])
		);

		return $result ? $result : new DataObjectSet();
	}

	/**
	 * @return array
	 */
	public function getAllMetadata() {
		if (!$raw = $this->owner->MetadataRaw) {
			return array();
		}

		$metadata = @unserialize($raw);
		return is_array($metadata) ? $metadata : array();
	}

	/**
	 * Returns a metadata value if it exists for a schema and field name. This
	 * is the main method for accessing the metadata attached to an object.
	 *
	 * @param  string $schema
	 * @param  string $field
	 * @return mixed
	 */
	public function Metadata($schema, $field) {
		if (!$metadata = $this->owner->MetadataRaw) {
			return;
		}

		$metadata = $this->getAllMetadata();

		if (isset($metadata[$schema][$field])) {
			return $metadata[$schema][$field];
		}
	}

	public function updateCMSFields(FieldSet $fields) {
		$allSchemas = DataObject::get('MetadataSchema');
		$schemas    = $this->getSchemas();

		if (!$allSchemas) return;

		$fields->addFieldsToTab('Root.Metadata', array(
			new HeaderField('MetadataInfoHeader', 'Metadata Information'),
			new MetadataSetField('MetadataRaw'),
			new HeaderField('MetadataSchemasHeader', 'Metadata Schemas'),
			$linkedSchemas = new CheckboxSetField('MetadataSchemas', '', $allSchemas)
		));

		$inherited = $this->getInheritedSchemas()->map('ID', 'ID');
		$linkedSchemas->setDefaultItems($inherited);
		$linkedSchemas->setDisabledItems($inherited);

		if ($this->owner->hasExtension('Hierarchy')) {
			$fields->addFieldToTab('Root.Metadata', new LiteralField(
				'SchemaAppliedToChildrenNote',
				'<p>Any metadata schemas selected will also be applied to this'
				. " item's children.</p>"
			));
		}
	}

	/**
	 * Ensures that schemas that are linked to parent objects are not saved
	 * into this object's relationships.
	 *
	 * @param string $values
	 */
	public function saveMetadataSchemas($values) {
		$inherited = $this->getInheritedSchemas()->map('ID', 'ID');
		$ids       = array_map('intval', explode(',', $values));
		$component = $this->owner->MetadataSchemas();

		$component->setByIDList(array_diff($ids, $inherited));
	}

}