<?php

namespace App\Traits;

/**
 * TODO: переделать
 */
trait RelationSyncable
{
    public function syncRelation($data, string $relation)
    {
        if (!isset($data[$relation])) {
            return;
        }
        $data = $data[$relation];
        $actualIds = array_filter(array_column($data, 'id'), fn ($val) => !is_null($val));
        $this->{$relation}()->whereNotIn('id', $actualIds)->each(fn ($model) => $model->delete());
        foreach ($data as $d) {
            if (isset($d['id']) && floor($d['id']) > 0) {
                $this->{$relation}()->find($d['id'])->update($d);
            } else {
                $this->{$relation}()->create($d);
            }
        }
    }
}
