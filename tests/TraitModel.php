<?php

namespace LiamWiltshire\LaravelModelMeta\Tests;


use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelModelMeta\Concerns\HasMeta;

class TraitModel extends Model {

    use HasMeta;

    protected $fillable = ['trait_model_id', 'title'];

    public $status = 9;

    //SQLLite doesn't have DESCRIBE, so....
    public function getTableFields()
    {
        return [
            'id' => 0,
            'trait_model_id' => 1,
            'title' => 2,
            'meta' => 3,
            'created_at' => 4,
            'updated_at' => 5
        ];
    }

    public function myRelationship()
    {
        return $this->hasOne(TraitModel::class);
    }
}