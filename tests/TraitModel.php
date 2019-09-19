<?php

namespace LiamWiltshire\LaravelModelMeta\Tests;


use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelModelMeta\Concerns\HasMeta;

class TraitModel extends Model {

    use HasMeta;

    protected $fillable = ['trait_model_id', 'title'];

    public $status = 9;

    public function myRelationship()
    {
        return $this->hasOne(TraitModel::class);
    }
}