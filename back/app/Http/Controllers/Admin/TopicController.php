<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TopicListResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    protected $filters = [
        'status' => ['status'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        $request->validate(['year' => ['required']]);
        $query = Lesson::query()
            ->whereRaw('(`topic` IS NOT NULL OR (`topic` IS NULL AND `status` = ?))', [
                LessonStatus::conducted,
            ])
            ->where('status', '<>', LessonStatus::cancelled)
            ->with(['teacher'])
            ->orderBy('date', 'desc');
        $this->filter($request, $query);

        return $this->handleIndexRequest(
            $request,
            $query,
            TopicListResource::class
        );
    }

    protected function filterGroup(&$query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }

    protected function filterStatus(&$query, $status)
    {
        switch ($status) {
            case 'noTopic':
                $query->whereNull('topic');
                break;

            case 'notVerified':
                $query->where('is_topic_verified', false);
                break;

            case 'verified':
                $query->where('is_topic_verified', true);
                break;
        }
    }
}
