<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\card;
use Illuminate\Http\Request;
use Throwable;

class cardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$landing, ['only' => ['newCard', 'edit', 'delete']]);

    }

    public function newCard(Request $req)
    {

        $card = new card();
        $card->title = $req->title;
        $card->body = $req->body;
        $card->id_landing_page = $req->id_landing_page;
        if (
            $card->save()
        ) {
            return response()->json(
                ['status' => "Success",
                    "data" => $card,
                ]
            );
        }
    }
    public function get(Request $req)
    {
        try {
            return card::findorfail($req->id);

        } catch (Throwable $e) {
            return response(null, 404);
        }

    }
    public function all(Request $req)
    {
        return card::ofLanding($req->id)->get();
    }

    public function delete(Request $req)
    {
        try {
            $card = card::findorfail($req->id);

        } catch (Throwable $e) {
            return response(null, 404);
        }
        $card = card::findorfail($req->id);
        if ($card->delete()) {
            return true;
        }
        return false;

    }

    public function edit(Request $req)
    {
        card::where('id', $req->id)->update($req->all());
        return response()->json([
            'status' => 'Success',
            'data' => card::where('id', $req->id)->first(),
        ]);
    }
}