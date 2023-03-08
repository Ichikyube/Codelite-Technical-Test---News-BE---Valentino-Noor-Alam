<?php

namespace App\Http\Controllers\API\V1;

use App\Repositories\AuthRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use HttpResponses;
    public $authRepository;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     *     @OA\Response(response=200, description="Success" ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
    */

    public function register(RegisterRequest $request)
    {
        try {
            $requestData = $request->only('name', 'email', 'password', 'password_confirmation');
            $user = $this->authRepository->register($requestData);
            if ($user) {
                if ($token = $user->attempt($requestData)) {
                    $data =  $this->respondWithToken($token);
                    return $this->responseSuccess($data, 'User Registered and Logged in Successfully', Response::HTTP_OK);
                }
            }
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
            // fetch user data from database based on request(email)
            $user = User::query()
                ->where("email", $credentials['email'])
                ->firstOr(fn() => response()->json([
                    "success" => false,
                    "message" => "email is not registered",
                    "data" => null
                ]));
            // check user login data
            if ($credentials['password'] == $user->password) {
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

            }
            return response()->json([
                'success' => false,
                'message' => 'Login Failed, Invalid Email and Password!'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request) {
        try {
            // data user login
            $user = $request->user();
            // deletes all tokens based on user login
            $token = $user->tokens()->where('tokenable_id', $user->id)->delete();

            // response success
            return response()->json([
                'success'           => true,
                'message'           => 'Logged out successfully !',
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
        try {
            $user = $request->user();
            return $this->responseSuccess($user, 'Profile Fetched Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function gantipassword(Request $request)
    {
        #Match The Old Password
        if(!Hash::check($request->old_password, auth()->user()->password)){
            return back()->with("error", "Wrong Old Password!");
        }
        #Update the new Password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->responseSuccess(null, 'Password successfully updated!');

    }
}
