<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 24/02/19
 * Time: 18:17
 */

namespace LiamWiltshire\LaravelModelMeta\Tests;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Capsule\Manager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->configureDatabase();
        $this->migrateIdentitiesTable();
    }
    protected function configureDatabase()
    {
        $db = new Manager();
        $db->addConnection(array(
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ));
        $db->bootEloquent();
        $db->setAsGlobal();
    }

    public function migrateIdentitiesTable()
    {
        Manager::schema()->create('trait_models', function($table) {
            $table->increments('id');
            $table->integer('trait_model_id');
            $table->string('title');
            $table->json('meta')->nullable()->default(null);
            $table->timestamps();
        });
        TraitModel::create(array('trait_model_id' => 5, 'title' => 'Trait Title 1'));
        TraitModel::create(array('trait_model_id' => 4, 'title' => 'Trait Title 2'));
        TraitModel::create(array('trait_model_id' => 3, 'title' => 'Trait Title 3'));
        TraitModel::create(array('trait_model_id' => 2, 'title' => 'Trait Title 4'));
        TraitModel::create(array('trait_model_id' => 1, 'title' => 'Trait Title 5'));

    }
}