<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\color;
use App\Models\landingPage;
use App\Models\shape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class shapesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$landing, ['only' => ['newShape', 'edit', 'toggleStatus']]);

    }

    public function newShape(Request $req)
    {
        $shape = new shape();
        $shape->name = $req->name;
        $shape->description = $req->description;
        $shape->original_price = $req->original_price;
        $shape->promotioned_price = $req->promotioned_price;
        $shape->id_landing_page = $req->id_landing_page;
        if ($shape->save()) {
            return response()->json([
                'status' => "Success",
                'data' => $shape->refresh(),
            ]);
        }}

    public function get(Request $req)
    {
        $landing = landingPage::findorfail($req->id);
        $shape = shape::landing($req->id)->whereid($req->shapeId)->firstorfail();
        $colors = color::leftJoin('files', 'files.id', 'id_image')->ofshape($shape->id)->get(DB::raw('colors.name,color_code,colors.id,url,status'));
        return response()->json([
            'landing' => $landing,
            'shape' => $shape,
            'colors' => $colors,
        ]);
    }
    public function edit(Request $req)
    {

        $shape = shape::where('id', $req->id)->update($req->all());
        return response()->json([
            'status' => 'Success',
            'data' => shape::where('id', $req->id)->first(),
        ]);
    }
    public function toggleStatus(Request $req)
    {
        $shape = shape::find($req->id);
        $shape->status = $shape->status == sharedStatus::$active ? sharedStatus::$inActive : sharedStatus::$active;
        $shape->save();
        return response()->json([
            'status' => 'Success',
            'data' => $shape,
        ]);
    }

    public function delete(Request $req)
    {
        shape::whereid($req->id)->update(['status' => sharedStatus::$deleted]);
        return res('success', 'shape successfully deleted ', true);
    }
}