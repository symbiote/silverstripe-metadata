<?php
/**
 * An extension that must be applied to an object in order for it to have the
 * ability to have metadata attached to it.
 *
 * @package silverstripe-metadata
 */
class MetadataExtension extends DataObjectDecorator {

	/**
	 * @var DataObjectSet
	 */
	protected $schemas;

	public function extraStatics() {
		return array('db' => array(
			'MetadataRaw' => 'Text'
		));
	}

	/**
	 * Returns all the schema objects attached to this object, or any of its
	 * parents.
	 *
	 * @return DataObjectSet
	 */
	public function getSchemas() {
		if (!$this->schemas) {
			$schemas = $this->getAttachedSchemas();

			if ($this->owner->hasExtension('Hierarchy')) {
				$schemas->merge($this->getInheritedSchemas());
				$schemas->removeDuplicates();
				$schemas->sort('Title');
			}

			$this->schemas = $schemas;
		}

		return $this->schemas;
	}

	/**
	 * Returns metadata schemas directly attached to this object via a schema
	 * link (not including inherited schemas).
	 *
	 * @return DataObjectSet
	 */
	public function getAttachedSchemas() {
		$filter = sprintf(
			'"MetadataSchema"."ID" = "MetadataSchemaLink"."SchemaID"'
			. ' AND "MetadataSchemaLink"."ParentClass" = \'%s\''
			. ' AND "MetadataSchemaLink"."ParentID" = %d',
			ClassInfo::baseDataClass($this->owner->class),
			$this->owner->ID
		);

		$schemas = DataObject::get(
			'MetadataSchema',
			null,
			null,
			'INNER JOIN "MetadataSchemaLink" ON ' . $filter);

		return $schemas ? $schemas : new DataObjectSet();
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

		$ids     = array();
		$parents = $this->owner->getAncestors();

		foreach ($parents as $parent) {
			$ids[] = $parent->ID;
		}

		$filter = sprintf(
			'"MetadataSchema"."ID" = "MetadataSchemaLink"."SchemaID"'
			. ' AND "MetadataSchemaLink"."ParentClass" = \'%s\''
			. ' AND "MetadataSchemaLink"."ParentID" IN (%s)',
			ClassInfo::baseDataClass($this->owner->class),
			implode(', ', $ids)
		);

		$result = DataObject::get(
			'MetadataSchema',
			null,
			null,
			'INNER JOIN "MetadataSchemaLink" ON ' . $filter
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
		$metadata = $this->getAllMetadata();
		$schema   = $this->getSchemas()->find('Name', $schema);

		if (!$schema) {
			return false;
		}

		if (isset($metadata[$schema->Name][$field])) {
			return $metadata[$schema->Name][$field];
		} else {
			if ($field = $schema->Fields()->find('Name', $field)) {
				return $field->Default;
			}
		}
	}

	public function updateCMSFields(FieldSet $fields) {
		if (!$allSchemas = DataObject::get('MetadataSchema')) {
			return;
		}

		$fields->addFieldsToTab('Root.Metadata', array(
			new HeaderField('MetadataInfoHeader', 'Metadata Information'),
			new MetadataSetField($this->owner, 'MetadataRaw'),
			new HeaderField('MetadataSchemasHeader', 'Metadata Schemas'),
			$linkedSchemas = new CheckboxSetField('MetadataSchemas', '', $allSchemas)
		));

		$inherited = $this->getInheritedSchemas()->map('ID', 'ID');
		$linkedSchemas->setValue($this->getAttachedSchemas()->map('ID', 'ID'));
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
		$attached  = $this->getAttachedSchemas();
		$inherited = $this->getInheritedSchemas()->map('ID', 'ID');

		$ids = array_map('intval', explode(',', $values));
		$ids = array_diff($ids, $inherited);

		$add = array_diff($ids, $attached->map('ID', 'ID'));
		$del = array_diff($attached->map('ID', 'ID'), $ids);

		if ($add) foreach ($add as $id) {
			$link = new MetadataSchemaLink();
			$link->ParentClass = $this->owner->class;
			$link->ParentID    = $this->owner->ID;
			$link->SchemaID    = $id;
			$link->write();
		}

		if ($del) DB::query(sprintf(
			'DELETE FROM "MetadataSchemaLink" WHERE "SchemaID" IN (%s)'
			. ' AND "ParentClass" = \'%s\' AND "ParentID" = %d',
			implode(', ', $del),
			Convert::raw2sql(ClassInfo::baseDataClass($this->owner->class)),
			$this->owner->ID
		));
	}

}