<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 28/02/19
 * Time: 10:48
 */

namespace LiamWiltshire\LaravelModelMeta\Tests\Concerns;

use LiamWiltshire\LaravelModelMeta\Tests\AltTraitModel;
use LiamWiltshire\LaravelModelMeta\Tests\TestCase;
use LiamWiltshire\LaravelModelMeta\Tests\TraitModel;

class HasMetaTest extends TestCase
{
    public function testUpdatingTableColumnUpdatesSuccessfully()
    {
        /**
         * @var TraitModel $traitModel
         */
        $traitModel = TraitModel::find(1);

        $traitModel->title = 'This is a new title';

        $this->assertEquals('This is a new title', $traitModel->getAttributes()['title']);

        $traitModel->save();

        $db = $traitModel->getConnection();

        $storedTitle = $db->select("SELECT title, meta FROM trait_models WHERE id = 1");

        $this->assertEquals('This is a new title', $storedTitle[0]->title);
        $this->assertEmpty(json_decode($storedTitle[0]->meta));
    }

    public function testUpdatingNonColumnUpdatesSuccessfully()
    {
        /**
         * @var TraitModel $traitModel
         */
        $traitModel = TraitModel::find(1);

        $traitModel->summary = 'This is a meta summary';

        $this->assertFalse(isset($traitModel->getAttributes()['summary']));

        $meta = json_decode($traitModel->getAttributes()['meta']);

        $this->assertEquals('This is a meta summary', $meta->summary);

        $traitModel->save();

        $db = $traitModel->getConnection();

        $storedMeta = $db->select("SELECT meta FROM trait_models WHERE id = 1");

        $jsonMeta = $storedMeta[0]->meta;

        $this->assertEquals('{"summary":"This is a meta summary"}', $jsonMeta);
    }

    public function testGettingRelationshipDoesntInvokeMeta()
    {
        /**
         * @var TraitModel $traitModel
         */
        $traitModel = TraitModel::find(1);

        $relatedModel = $traitModel->myRelationship;

        $this->assertInstanceOf(TraitModel::class, $relatedModel);
    }

    public function testCallingRelationshipMethodReturningNullDoesntInvokeMeta()
    {
        $targetModel = TraitModel::find(5);
        $targetModel->trait_model_id = 999999;
        $targetModel->save();

        unset($targetModel);

        $traitModel = TraitModel::find(1);


        $relatedModel = $traitModel->myRelationship;
        $this->assertNull($relatedModel);

        $this->assertTrue($traitModel->handleAsAttribute("myRelationship"));
    }

    public function testCallingPropertyOfModelDoesntInvokeMeta()
    {
        $traitModel = TraitModel::find(1);
        $traitModel->status = 5;

        $result = (array) $traitModel;


        $this->assertEquals(5, $traitModel->status);
        $this->assertEquals(5, $result['status']);
        $this->assertFalse(isset($traitModel->getAttributes()['status']));

        $this->assertTrue($traitModel->handleAsAttribute("status"));
    }

    public function testCallingAttributeThatDoesntExistReturnsNull()
    {
        $traitModel = TraitModel::find(1);
        $this->assertFalse($traitModel->handleAsAttribute("some_meta"));
        $this->assertNull($traitModel->some_meta);
    }

    public function testSettingAndGettingMetaReturnsCorrectValue()
    {
        $traitModel = TraitModel::find(1);
        $traitModel->some_meta = "testing meta";

        $meta = json_decode($traitModel->getAttributes()['meta']);

        $this->assertEquals("testing meta", $traitModel->some_meta);
        $this->assertEquals("testing meta", $meta->some_meta);

        $traitModel->save();

        $traitModel = TraitModel::find(1);
        $this->assertEquals("testing meta", $traitModel->some_meta);
    }

    public function testEditingMetaFieldDirectoryThrowsException()
    {
        $traitModel = AltTraitModel::find(1);
        $this->expectExceptionMessage("Field metaData shouldn't be manipulated directly");

        $traitModel->metaData = "test";
    }

    public function testMultipleMetaFieldsSetAndRetrieved()
    {
        $traitModel = TraitModel::find(1);

        // Set multiple meta fields
        $traitModel->field1 = 'value1';
        $traitModel->field2 = 'value2';
        $traitModel->field3 = 'value3';

        // Verify all fields are set before save
        $this->assertEquals('value1', $traitModel->field1);
        $this->assertEquals('value2', $traitModel->field2);
        $this->assertEquals('value3', $traitModel->field3);

        $traitModel->save();

        // Reload and verify persistence
        $reloaded = TraitModel::find(1);
        $this->assertEquals('value1', $reloaded->field1);
        $this->assertEquals('value2', $reloaded->field2);
        $this->assertEquals('value3', $reloaded->field3);
    }

    public function testSettingMetaToNullExplicitly()
    {
        $traitModel = TraitModel::find(1);

        // Set a meta field
        $traitModel->nullable_field = 'initial value';
        $traitModel->save();

        // Verify it's set
        $this->assertEquals('initial value', $traitModel->nullable_field);

        // Set to null explicitly
        $traitModel->nullable_field = null;
        $traitModel->save();

        // Verify null is stored
        $reloaded = TraitModel::find(1);
        $this->assertNull($reloaded->nullable_field);
    }

    public function testComplexNestedObjectsAndArraysInMeta()
    {
        $traitModel = TraitModel::find(1);

        // Complex nested structure
        $complexData = [
            'nested' => [
                'level1' => [
                    'level2' => [
                        'value' => 'deep value',
                        'numbers' => [1, 2, 3, 4, 5]
                    ]
                ]
            ],
            'array_of_objects' => [
                ['id' => 1, 'name' => 'First'],
                ['id' => 2, 'name' => 'Second'],
            ]
        ];

        $traitModel->complex_data = $complexData;
        $traitModel->save();

        // Reload and verify structure preservation
        $reloaded = TraitModel::find(1);
        $retrieved = $reloaded->complex_data;

        // Laravel JSON casting returns arrays
        $this->assertIsArray($retrieved);
        $this->assertEquals('deep value', $retrieved['nested']['level1']['level2']['value']);
        $this->assertIsArray($retrieved['nested']['level1']['level2']['numbers']);
        $this->assertCount(5, $retrieved['nested']['level1']['level2']['numbers']);
        $this->assertIsArray($retrieved['array_of_objects']);
        $this->assertEquals(1, $retrieved['array_of_objects'][0]['id']);
        $this->assertEquals('Second', $retrieved['array_of_objects'][1]['name']);
    }

    public function testUnicodeAndSpecialCharactersInMeta()
    {
        $traitModel = TraitModel::find(1);

        // Various unicode and special characters
        $traitModel->emoji = '🎉🚀✨';
        $traitModel->chinese = '你好世界';
        $traitModel->arabic = 'مرحبا بالعالم';
        $traitModel->special = 'quotes"and\'slashes/backslash\\tabs	newlines
test';

        $traitModel->save();

        $reloaded = TraitModel::find(1);
        $this->assertEquals('🎉🚀✨', $reloaded->emoji);
        $this->assertEquals('你好世界', $reloaded->chinese);
        $this->assertEquals('مرحبا بالعالم', $reloaded->arabic);
        $this->assertEquals('quotes"and\'slashes/backslash\\tabs	newlines
test', $reloaded->special);
    }

    public function testLargeMetaPayload()
    {
        $traitModel = TraitModel::find(1);

        // Create a large payload with 100 fields (smaller to avoid memory issues)
        $largeData = [];
        for ($i = 0; $i < 100; $i++) {
            $largeData["field_$i"] = str_repeat("Lorem ipsum dolor sit amet. ", 5);
        }

        $traitModel->large_payload = $largeData;
        $traitModel->save();

        $reloaded = TraitModel::find(1);
        $retrieved = $reloaded->large_payload;

        // Laravel JSON casting returns arrays with string keys
        $this->assertIsArray($retrieved);
        $this->assertEquals($largeData['field_0'], $retrieved['field_0']);
        $this->assertEquals($largeData['field_99'], $retrieved['field_99']);

        // Verify all 100 fields exist
        $this->assertCount(100, $retrieved);
    }

    public function testEmptyStringVsNull()
    {
        $traitModel = TraitModel::find(1);

        // Set empty string
        $traitModel->empty_field = '';
        $traitModel->null_field = null;
        $traitModel->zero_field = 0;
        $traitModel->false_field = false;

        $traitModel->save();

        $reloaded = TraitModel::find(1);

        // Empty string should be preserved
        $this->assertSame('', $reloaded->empty_field);

        // Null should be null
        $this->assertNull($reloaded->null_field);

        // Zero and false should be preserved
        $this->assertSame(0, $reloaded->zero_field);
        $this->assertSame(false, $reloaded->false_field);
    }

    public function testOverwritingExistingMetaValues()
    {
        $traitModel = TraitModel::find(1);

        // Set initial value
        $traitModel->overwrite_test = 'original';
        $traitModel->save();

        // Verify initial value
        $this->assertEquals('original', TraitModel::find(1)->overwrite_test);

        // Overwrite with new value
        $traitModel = TraitModel::find(1);
        $traitModel->overwrite_test = 'updated';
        $traitModel->save();

        // Verify update
        $this->assertEquals('updated', TraitModel::find(1)->overwrite_test);

        // Overwrite with different type
        $traitModel = TraitModel::find(1);
        $traitModel->overwrite_test = 12345;
        $traitModel->save();

        // Verify type change
        $this->assertSame(12345, TraitModel::find(1)->overwrite_test);
    }

    public function testMixedTableColumnsAndMetaFields()
    {
        $traitModel = TraitModel::find(1);

        // Mix table column updates with meta updates
        $traitModel->title = 'Updated Title';
        $traitModel->custom_meta = 'Meta Value';
        $traitModel->another_meta = 'Another Meta';

        $traitModel->save();

        $db = $traitModel->getConnection();
        $stored = $db->select("SELECT title, meta FROM trait_models WHERE id = 1");

        // Title should be in column
        $this->assertEquals('Updated Title', $stored[0]->title);

        // Meta fields should be in meta JSON
        $meta = json_decode($stored[0]->meta);
        $this->assertEquals('Meta Value', $meta->custom_meta);
        $this->assertEquals('Another Meta', $meta->another_meta);

        // Verify via model
        $reloaded = TraitModel::find(1);
        $this->assertEquals('Updated Title', $reloaded->title);
        $this->assertEquals('Meta Value', $reloaded->custom_meta);
        $this->assertEquals('Another Meta', $reloaded->another_meta);
    }
}
