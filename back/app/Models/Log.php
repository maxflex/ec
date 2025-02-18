<?php

namespace App\Models;

use App\Enums\LogType;
use App\Utils\Session;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    const DISABLE_LOGS = true;

    protected $fillable = [
        'table', 'row_id', 'data', 'type', 'entity_type', 'entity_id',
    ];

    protected $casts = [
        'data' => 'array',
        'type' => LogType::class,
    ];

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

    public static function booted()
    {
        static::creating(function ($model) {
            if (auth()->user()) {
                $model->entity_type = get_class(auth()->user());
                $model->entity_id = auth()->id();
            }
            if (request()->header('Preview')) {
                $user = Session::get(request()->header('Preview'));
                if ($user) {
                    $model->emulation_user_id = $user->id;
                }
            }
            if (request()->header('Client-Parent-Id')) {
                $model->client_parent_id = request()->header('Client-Parent-Id');
            }
            $model->ip = request()->ip();
        });
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function clientParent(): BelongsTo
    {
        return $this->belongsTo(ClientParent::class);
    }

    public function emulationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emulation_user_id');
    }
}
