<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function walletFromUser()
    {
        $id = Auth::id();
        $wallet = User::find($id)->wallet()->get();
        if ($wallet) {
            return response()->json(['message' => 'Esta es tu wallet', 'data' => $wallet], 200);
        } else {
            return response()->json(['No tienes wallet asociada']);
        }
    }
}
