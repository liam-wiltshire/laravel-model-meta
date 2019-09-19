<?php

namespace LiamWiltshire\LaravelModelMeta\Tests;


use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelModelMeta\Concerns\HasMeta;

class AltTraitModel extends Model {

    use HasMeta;

    public $table = "trait_models";
    protected $metaDbField = 'metaData';
    protected $fillable = ['trait_model_id', 'title'];

    public $status = 9;

    public function myRelationship()
    {
        return $this->hasOne(AltTraitModel::class);
    }
}