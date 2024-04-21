<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function test()
    {
        $listAddress = Address::all();
        return response()->json([
            'listAddress' =>  $listAddress,
        ]);
    }
    public function signup(Request $request)
    {
        User::create([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'phoneNumber' => $request->phoneNumber,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
            'gender' => $request->gender,
        ]);

        return response()->json([
            'status code' =>  "200",
        ]);
    }
}
