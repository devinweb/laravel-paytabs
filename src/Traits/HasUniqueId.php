<?php

namespace Devinweb\LaravelPaytabs\Traits;

trait HasUniqueID
{
    public static function bootHasUniqueID()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = uniqid();
            }
        });
    }
}
