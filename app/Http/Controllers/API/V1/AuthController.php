<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use HttpResponses;
    /**
     * @OA\POST(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login",
     *     description="Login",
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="admin@example.com"),
     *              @OA\Property(property="password", type="string", example="123456")
     *          ),
     *      ),
     *      @OA\Response(response=200, description="Login"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    function login(Request $request) 
    {
        //validation data form request body
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        //try and catch, hendle error message
        try {
            // fetch user data from database based on request(email)
            $user = User::where('email', $request->email)->first();
            // cek user berdasarkan email (availability user)
            if ($user == null) {
                return response()->json([
                    "success" => false,
                    "message" => "email tidak ditemukan",
                    "data" => null
                ]);
            }
            // check user login data
            if (Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login Failed, Invalid Email and Password!'
                ], Response::HTTP_UNAUTHORIZED);
            }
            // create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            // response success
            return response()->json([
                'success' => true,
                'message' => 'Logged In Successfully !',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function logout(Request $request) {
        try {
            // data user login
            $user = $request->user();
            // deletes all tokens based on user login
            $token = $user->tokens()->where('tokenable_id', $user->id)->delete();

            // response success
            return response()->json([
                'success'           => true,
                'massage'           => 'Logged out successfully !',
                'tokenOnDeleted'    => $token,
            ], 200);

            // catch QueryException, used to handle RDBMS errors
        } catch (\Exception $e) {
            // response server error
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function me(Request $request)
    {
        //show which user
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data'  => $user
        ]);
    }

    public function gantipassword(Request $request)
    {
        $user = User::query()->where("id", $id)->first();
        if (!isset($user)) {
            return response()->json([
                "status" => false,
                "message" => "data kosong",
                "data" => null
            ]);
        }

        $payload = $request->all();

        $user->fill($payload);
        $user->save();

        return response()->json([
            "status" => true,
            "message" => "perubahan data tersimpan",
            "data" => $user
        ]);
        //show which user
        $user = $request->user();
        $user->password;
        return response()->json([
            'success' => true,
            'data'  => $user
        ]);
    }
}
