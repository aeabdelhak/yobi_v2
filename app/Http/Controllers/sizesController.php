<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\size;
use Illuminate\Http\Request;

class sizesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$landing, ['only' => ['newSize', 'edit', 'toggleStatus']]);

    }

    public function newSize(Request $req)
    {
        $all = $req->all();
        $size = size::create($all);
        return response()->json([
            "status" => "success",
            'data' => $size->fresh(),
        ]);

    }
    public function toggleStatus(Request $req)
    {
        $size = size::find($req->id);
        $size->status = $size->status == sharedStatus::$active ? sharedStatus::$inActive : sharedStatus::$active;
        $size->save();
        return response()->json([
            'status' => 'Success',
            'data' => $size,
        ]);
    }
    public function edit(Request $req)
    {

        size::where('id', $req->id)->update($req->all());
        return response()->json([
            'status' => 'Success',
            'data' => size::where('id', $req->id)->first(),
        ]);
    }

    public function delete(Request $req)
    {
        size::whereid($req->id)->update(['status' => sharedStatus::$deleted]);
        return res('success', 'color successfully deleted ', true);
    }

}