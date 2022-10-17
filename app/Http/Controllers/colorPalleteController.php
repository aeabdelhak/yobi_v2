<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\colorPalette;
use Illuminate\Http\Request;

class colorPalleteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$palletes, ['only' => ['newPallete', 'edit', 'delete']]);

    }
    public function all()
    {
        return colorPalette::all();
    }

    public function newPallete(Request $req)
    {
        return colorPalette::create($req->all());
    }
    public function get(Request $req)
    {
        return colorPalette::findorfail($req->id);
    }
    public function edit(Request $req)
    {
        return;
    }
    public function delete(Request $req)
    {
        return;
    }

}