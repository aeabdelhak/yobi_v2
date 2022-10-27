<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\userResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class userResultsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$landing);

    }
    public function newResult(Request $req)
    {
        $result = new userResult();
        $result->id_landing_page = $req->id_landing_page;
        $result->id_image = FilesController::store($req->image);
        $result->save();
        $result = userResult::join('files', 'files.id', 'user_results.id_image')->where('user_results.id', $result->id)->first(DB::raw('*,user_results.id as id'));

        return res('success', 'created successfully', $result);
    }

    public function delete($id)
    {
        $res = userResult::find($id);
        if (!$res) {
            return false;
        }
        $id_file = $res->id_image;

        if ($res->delete()) {
            FilesController::delete($id_file);
            return res('success');
        }

    }
}