<?php

namespace App\Http\Controllers;
use App\Models\Address;

abstract class Controller
{
    public function test()
    {
        $listAddress = Address::all();
        return response()->json([
            'listAddress' =>  $listAddress,
        ]);
    }
}
