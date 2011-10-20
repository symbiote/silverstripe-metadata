<?php
/**
 * An extension that must be applied to an object in order for it to have the
 * ability to have metadata attached to it.
 *
 * NOTE: You can use a "canApplySchemas" method in order to control whether a
 * user can apply manage the schemas attached to the object.
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
		$result = new DataObjectSet();

		if (!$this->owner->hasExtension('Hierarchy')) {
			return new DataObjectSet();
		}

		$ids     = array();
		$parents = $this->owner->getAncestors();

		foreach ($parents as $parent) {
			$ids[] = $parent->ID;
		}
		
		if (count($ids)) {
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
			if (!$result) {
				$result = new DataObjectSet();
			}
		}

		if ($this->owner instanceof SiteTree) {
			// Check SiteConfig too
			$config = SiteConfig::current_site_config();
			if ($config->hasExtension('MetadataExtension')) {
				$schemas = $config->getAttachedSchemas();
				if ($schemas && $schemas->count()) {
					$result->merge($schemas);
				}
			}
		}

		return $result;
	}

	/**
	 * Links a metadata schema to this object, if it's not already linked.
	 *
	 * @param MetadataSchema|int $schema
	 */
	public function addSchema($schema) {
		$id       = is_object($schema) ? $schema->ID : $schema;
		$attached = $this->getSchemas()->map();

		if (!array_key_exists($id, $attached)) {
			$link = new MetadataSchemaLink();
			$link->ParentClass = $this->owner->class;
			$link->ParentID    = $this->owner->ID;
			$link->SchemaID    = $id;
			$link->write();
		}

		$this->schemas = null;
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
	 * Returns a raw metadata value (i.e. not run through a process method).
	 *
	 * @param  MetadataSchema|string $schema
	 * @param  MetadataField|string $field
	 * @return string
	 */
	public function getRawMetadataValue($schema, $field) {
		$metadata = $this->getAllMetadata();

		if (!$schema instanceof MetadataSchema && !$schema = $this->getSchemas()->find('Name', $schema)) {
			return;
		}

		if (!$field instanceof MetadataField && !$field = $schema->Fields()->find('Name', $field)) {
			return;
		}

		if (isset($metadata[$schema->Name][$field->Name])) {
			return $metadata[$schema->Name][$field->Name];
		} else {
			return $field->Default;
		}
	}

	/**
	 * Returns a metadata value if it exists for a schema and field name, suitable
	 * for injection into a template.
	 *
	 * NOTE: This can potentially be quite expensive with default and cascading
	 * values, so results should be cached.
	 *
	 * @param  MetadataSchema|string $schema
	 * @param  MetadataField|string $field
	 * @return mixed
	 */
	public function Metadata($schema, $field) {
		if (!$schema instanceof MetadataSchema && !$schema = $this->getSchemas()->find('Name', $schema)) {
			return;
		}

		if (!$field instanceof MetadataField && !$field = $schema->Fields()->find('Name', $field)) {
			return;
		}

		$raw  = $this->getRawMetadataValue($schema, $field);
		$hier = $this->owner->hasExtension('Hierarchy');

		$parent = null;
		// if hierarchy is applicable, and we're a sitetree object, and at the root
		if ($hier && !$this->owner->ParentID && $this->owner instanceof SiteTree) {
			if (SiteConfig::current_site_config()->hasExtension('MetadataExtension')) {
				$parent = SiteConfig::current_site_config();
			}
		}
		if (!$raw && $hier && $field->Cascade && $parent) {
			return $parent->Metadata($schema, $field);
		}

		return $field->process($raw, $this->owner);
	}

	/**
	 * Returns all the metadata fields for a schema name encased in standard
	 * HTML <meta> tags.
	 *
	 * @param  string $schema
	 * @return string
	 */
	public function MetadataMetaTags($schema) {
		$result = '';
		$cache  = SS_Cache::factory('MetadataExtension');
		$key    = md5(implode('', array(
			'MetadataMetaTags', $this->owner->class, $this->owner->ID, $this->owner->LastEdited
		)));

		if ($cached = $cache->load($key)) {
			return $cached;
		}

		if (!$schema = $this->getSchemas()->find('Name', $schema)) {
			return;
		}

		foreach ($schema->Fields() as $field) {
			$value = $this->Metadata($schema, $field);

			if (!$value || ($value instanceof DBField && !$value->hasValue())) {
				continue;
			}

			if (is_object($value)) {
				$value = $value instanceof DBField ? $value->Nice() : $value->getTitle();
			}

			$result .= sprintf(
				"<meta name=\"%s\" content=\"%s\" />\n",
				Convert::raw2att($field->Name),
				Convert::raw2att($value)
			);
		}

		$cache->save($result, $key);
		return $result;
	}

	public function updateCMSFields(FieldSet $fields) {
		
		if (!$allSchemas = DataObject::get('MetadataSchema')) {
			return;
		}
		
		$tabName = 'Root.Metadata';
		$rootTab = $fields->fieldByName('Root');
		if (!$rootTab) {
			$tabName = 'BottomRoot.Metadata';
		}

		$fields->addFieldsToTab($tabName, array(
			new HeaderField('MetadataInfoHeader', 'Metadata Information'),
			new MetadataSetField($this->owner, 'MetadataRaw'),
			new HeaderField('MetadataSchemasHeader', 'Metadata Schemas'),
			$linkedSchemas = new CheckboxSetField('MetadataSchemas', '', $allSchemas)
		));

		$inherited = $this->getInheritedSchemas()->map('ID', 'ID');
		$linkedSchemas->setValue($this->getAttachedSchemas()->map('ID', 'ID'));
		$linkedSchemas->setDefaultItems($inherited);
		$linkedSchemas->setDisabledItems($inherited);

		$canApply = $this->owner->extendedCan('canApplySchemas', Member::currentUser());
		if ($canApply === false) {
			$linkedSchemas->setDisabled(true);
		}

		if ($this->owner->hasExtension('Hierarchy')) {
			$fields->addFieldToTab($tabName, new LiteralField(
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
			$this->addSchema($id);
		}

		if ($del) DB::query(sprintf(
			'DELETE FROM "MetadataSchemaLink" WHERE "SchemaID" IN (%s)'
			. ' AND "ParentClass" = \'%s\' AND "ParentID" = %d',
			implode(', ', $del),
			Convert::raw2sql(ClassInfo::baseDataClass($this->owner->class)),
			$this->owner->ID
		));
	}

	public function flushCache() {
		$this->schemas = null;
	}

}