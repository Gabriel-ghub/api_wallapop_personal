<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *      title="Wallapop_API",
 *      version="1.0.0",
 *      description="API DE WALLAPOP",
 *      @OA\Contact(
 *          email="daw011.2022@gmail.com"
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @OA\Post(
     * path="/register",
     * summary="register a new user",
     * description="Register by email, password and name",
     * operationId="register",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Register user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *       @OA\Property(property="name", type="string", format="password", example="Juancito Perez"),
     *       @OA\Property(property="persistent", type="boolean", example="true"),
     *    ),
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */

     

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 0,
        ]);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'amount' => 0
        ]);
        //$token = Auth::login($user);
        return response()->json([
            'message' => "User successfully registered",
            'user' => $user,
            'wallet' => $wallet
        ]);
    }


    /**
     * @OA\Post(
     * path="/login",
     * summary="login user",
     * description="Login by email, password",
     * operationId="Login",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Login user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *       @OA\Property(property="persistent", type="boolean", example="true"),
     *    ),
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // $request->validate();
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                /*'status' => 'error',
'message' => 'Unauthorized',*/
                'error' => 'Unauthorized. Either email or password is
wrong.',
            ], 401);
        }
        $user = Auth::user();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => env('JWT_TTL') * 3600, //auth()->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }


    public function me()
    {
        return response()->json(
            Auth::user(),
        );
    }



    /**
     * @OA\post(
     * path="/logout",
     * summary="logout user",
     * description="Logout User",
     * operationId="Logout",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=false,
     *    description="Logout",
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Logout OK",
     *     )
     * )
     */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'User successfully signed out',
        ]);
    }


    public function refresh()
    {
        $user = Auth::user();
        return response()->json([
            'access_token' => Auth::refresh(),
            'token_type' => 'bearer',
            'expires_in' => env('JWT_TTL') * 3600,
            'user' => $user,
        ]);
    }
}
