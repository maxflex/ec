<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\PubTeacherRequest;
use App\Http\Resources\TeacherPubResource;
use App\Mail\NewTeacherMail;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TeacherController extends Controller
{
    protected $filters = [
        'subject' => ['subject'],
        'ids' => ['ids'],
        'limit' => ['limit'],
    ];

    public function index(Request $request)
    {
        $query = Teacher::where('is_published', true);
        $this->filter($request, $query);

        return TeacherPubResource::collection(
            $query->get()
        );
    }

    /**
     * CV с сайта
     */
    public function store(PubTeacherRequest $request)
    {
        Mail::to('info@ege-centr.ru')->send(new NewTeacherMail($request->all()));
    }

    protected function filterIds($query, array $ids)
    {
        $idsString = implode(',', $ids);
        $query->whereIn('id', $ids)->orderByRaw("FIELD(id, $idsString)");
    }

    protected function filterLimit($query, $limit)
    {
        $query->take($limit);
    }

    protected function filterSubject($query, string $subject)
    {
        $query->whereRaw('FIND_IN_SET(?, `subjects`)', [
            $subject,
        ]);
    }
}
