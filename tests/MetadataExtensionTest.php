<?php
/**
 * @package    silverstripe-metadata
 * @subpackage tests
 */
class MetadataExtensionTest extends SapphireTest
{

    public static $fixture_file = 'metadata/tests/MetadataExtensionTest.yml';

    public function testAttachingAndGettingSchemas()
    {
        $rootSchema  = $this->objFromFixture('MetadataSchema', 'root');
        $childSchema = $this->objFromFixture('MetadataSchema', 'child');

        $parent = $this->objFromFixture('MetadataExtensionTest_Parent', 'parent');
        $child1 = $this->objFromFixture('MetadataExtensionTest_Parent', 'child_1');
        $child2 = $this->objFromFixture('MetadataExtensionTest_Parent', 'child_2');

        $this->assertEquals(0, count($parent->getSchemas()));
        $this->assertEquals(0, count($child1->getSchemas()));
        $this->assertEquals(0, count($child2->getSchemas()));
        $parent->flushCache();
        $child1->flushCache();
        $child2->flushCache();

        $parent->addSchema($rootSchema);
        $expect = array(array('Name' => $rootSchema->Name));

        $this->assertDOSEquals($expect, $parent->getSchemas());
        $this->assertDOSEquals($expect, $child1->getSchemas());
        $this->assertDOSEquals($expect, $child2->getSchemas());

        $this->assertEquals(1, count($parent->getAttachedSchemas()));
        $this->assertEquals(0, count($child1->getAttachedSchemas()));
        $this->assertEquals(0, count($child2->getAttachedSchemas()));

        $this->assertEquals(0, count($parent->getInheritedSchemas()));
        $this->assertEquals(1, count($child1->getInheritedSchemas()));
        $this->assertEquals(1, count($child2->getInheritedSchemas()));

        $parent->flushCache();
        $child1->flushCache();
        $child2->flushCache();

        $child1->addSchema($childSchema);
        $childExpect = array(
            array('Name' => $rootSchema->Name),
            array('Name' => $childSchema->Name)
        );

        $this->assertDOSEquals($expect, $parent->getSchemas());
        $this->assertDOSEquals($childExpect, $child1->getSchemas());
        $this->assertDOSEquals($childExpect, $child2->getSchemas());

        $this->assertEquals(0, count($parent->getInheritedSchemas()));
        $this->assertEquals(1, count($child1->getInheritedSchemas()));
        $this->assertEquals(2, count($child2->getInheritedSchemas()));
    }
}

/**
 * @ignore
 */
class MetadataExtensionTest_Parent extends DataObject
{
    public static $db = array('Title' => 'Varchar');
    public static $extensions = array('Hierarchy', 'MetadataExtension');
}
