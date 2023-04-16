<?php

namespace App\Http\Controllers;

use Exception;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\DataErrorException;
use Dotenv\Exception\ValidationException;
use App\Http\Controllers\Controller;
use App\Traits\Controller\RestControllerTrait;

class AuthController extends Controller
{
    use RestControllerTrait;
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;

        return $this->successResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // PREVIOUS CODE
        // if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        //     $user = Auth::user();
        //     $success['token'] =  $user->createToken('MyApp')->accessToken;
        //     $success['name'] =  $user->name;

        //     return $this->successResponse($success, 'User login successfully.');
        // } else {
        //     return $this->errorResponse('Unauthorised.', ['error' => 'Unauthorised']);
        // }

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'username' => 'required|max:255',
                    'password' => 'required|min:6',
                ],
                [
                    'username.required' => 'The Email or Phone field is required.'
                ]
            );

            if ($validator->passes()) {
                $credentials = $request->only('username', 'password');
                $username = $credentials['username'];

                if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $userInfo = User::where('email', $username)->first();
                    $this->validateLoginWithEmail($request, $userInfo, $credentials);
                } else {
                    $userInfo = User::where('phone', $username)->first();
                    $this->validateLoginWithPhone($request, $userInfo, $credentials);
                }

                $user = Auth::user();
                $success['token'] =  $user->createToken('MyApp')->accessToken;
                $success['name'] =  $user->name;
                return $this->successResponse($success, 'User login successfully.');
            } else {
                throw new DataErrorException($validator);
            }
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            throw $e;
        }
    }


    public function validateLoginWithEmail(Request $request, $userInfo, $credentials)
    {
        $this->validate(
            $request,
            [
                'username' => 'required|email|max:255',
                'password' => 'required|min:6'
            ],
            [
                'username.max' => 'The email address may not be greater than 255 characters.'
            ]
        );

        if (!$userInfo) {
            throw new DataErrorException(['username' => 'Sorry, we do not recognize this email address.']);
        }

        if (!Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']])) {
            throw new DataErrorException(['password' => 'Password is not valid']);
        }

        return true;
    }

    public function validateLoginWithPhone(Request $request,  $userInfo, $credentials)
    {
        $this->validate(
            $request,
            [
                'username' => 'required|max:20',
                'password' => 'required|min:6'
            ],
            [
                'username.max' => 'The phone number may not be greater than 20 characters.'
            ]
        );

        if (!$userInfo) {
            throw new DataErrorException(['username' => 'Sorry, we do not recognize this phone number.']);
        }

        if (!Auth::attempt(['phone' => $credentials['username'], 'password' => $credentials['password']])) {
            throw new DataErrorException(['password' => 'Password is not valid']);
        }

        return true;
    }
}
