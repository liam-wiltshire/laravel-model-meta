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
}