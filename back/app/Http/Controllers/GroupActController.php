<?php

namespace App\Http\Controllers;

    use App\Models\GroupAct;
    use Illuminate\Http\Request;
    
    class GroupActController extends Controller {
        public function index()
        {
        return GroupAct::all();
        }
        
        public function store(Request $request)
        {
        $data = $request->validate([
        
        ]);
        
        return GroupAct::create($data);
        }
        
        public function show(GroupAct $groupAct)
        {
        return $groupAct;
        }
        
        public function update(Request $request, GroupAct $groupAct)
        {
        $data = $request->validate([
        
        ]);
        
        $groupAct->update($data);
        
        return $groupAct;
        }
        
        public function destroy(GroupAct $groupAct)
        {
        $groupAct->delete();
        
        return response()->json();
        }
    }
