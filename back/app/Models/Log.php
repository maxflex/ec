<?php

namespace App\Models;

use App\Enums\LogType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    const DISABLE_LOGS = true;

    protected $fillable = [
        'table', 'row_id', 'data', 'type', 'entity_type', 'entity_id'
    ];

    protected $casts = [
        'data' => 'array',
        'type' => LogType::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function add(LogType $type, $model)
    {
        switch ($type) {
            case LogType::update:
                $changed = [];
                foreach ($model->getDirty() as $field => $new) {
                    if ($field === 'updated_at') {
                        continue;
                    }
                    $old = $model->getOriginal($field);
                    if ($old instanceof Carbon) {
                        $old = $old->format('Y-m-d H:i:s');
                    }
                    $changed[] = compact('field', 'old', 'new');
                }
                self::create([
                    'type' => $type,
                    'row_id' => $model->id,
                    'data' => $changed,
                    'table' => $model->getTable(),
                ]);
                break;
            default:
                self::create([
                    'type' => $type,
                    'row_id' => $model->id,
                    'data' => $model->attributes,
                    'table' => $model->getTable(),
                ]);
        }
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->user()) {
                $model->entity_type = auth()->user()->entity_type;
                $model->entity_id = auth()->user()->entity_id;
            }
            $model->ip = Request::ip();
        });
    }
}
