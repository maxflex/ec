<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientComplaintRequest;
use App\Http\Resources\ClientComplaintListResource;
use App\Http\Resources\ClientComplaintResource;
use App\Models\ClientComplaint;
use Illuminate\Http\Request;

class ClientComplaintController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'program'],
    ];

    public function index(Request $request)
    {
        $query = ClientComplaint::latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest(
            $request,
            $query,
            ClientComplaintListResource::class
        );
    }

    public function store(ClientComplaintRequest $request)
    {
        $clientComplaint = ClientComplaint::create($request->all());

        return new ClientComplaintListResource($clientComplaint);
    }

    public function update(ClientComplaint $clientComplaint, ClientComplaintRequest $request)
    {
        $clientComplaint->update($request->all());

        return new ClientComplaintListResource($clientComplaint);
    }

    public function show(ClientComplaint $clientComplaint)
    {
        return new ClientComplaintResource($clientComplaint);
    }

    public function destroy(ClientComplaint $clientComplaint)
    {
        $clientComplaint->delete();
    }
}
