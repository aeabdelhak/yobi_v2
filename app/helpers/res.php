<?php

function res($status, $message = null, $data = null)
{
    return response()->json([
        'status' => $status,
        "message" => $message,
        "data" => $data,
    ]);
}