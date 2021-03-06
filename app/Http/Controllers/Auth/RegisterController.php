<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use JWTAuth;
use App\Tools\RsaUtils;

class RegisterController extends Controller {
  /*
  |--------------------------------------------------------------------------
  | Register Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users as well as their
  | validation and creation. By default this controller uses a trait to
  | provide this functionality without requiring any additional code.
  |
  */

  //use RegistersUsers;

  /**
   * Where to redirect users after registration.
   *
   * @var string
   */
  protected $redirectTo = '/home';

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest');
  }

  /**
   * Get a validator for an incoming registration request.
   *
   * @param  array $data
   * @return \Illuminate\Contracts\Validation\Validator
   */
  protected function validator(array $data)
  {
    return Validator::make($data, [
      'name'     => 'required|string|max:255',
      'email'    => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:6', //|confirmed 客户端完成confirm
    ]);
  }

  /**
   * Create a new user instance after a valid registration.
   *
   * @param  array $data
   * @return User
   */
  protected function create(array $data)
  {
    return User::create([
      'name'     => $data['name'],
      'email'    => $data['email'],
      'password' => bcrypt($data['password']),
    ]);
  }

  public function register(Request $request)
  {
    $this->validator($request->all())->validate();

    $user  = $this->create($request->all());
    $token = JWTAuth::fromUser($user);
    return ["token" => $token];
  }

  public function encryptedRegister(Request $request)
  {
    $this->validator($request->all())->validate();
    $params = $request->all();
    $password = RsaUtils::dePrivate($params['password']);
    if($password == null) {
      return response()->json(['message' => 'Encryption error'], 400);
    }
    $params['password'] = $password;

    $user  = $this->create($params);
    $token = JWTAuth::fromUser($user);
    return ["token" => $token];
  }

  public function rsaTest(Request $request)
  {
    $data = $request->get('data','abcdef');
    echo 'base64:'.base64_encode($data).'<br>';
    echo $data . '<br>';
    echo 'encrypted by public key<br>';
    $encrypted = RsaUtils::enPublic($data);
    echo $encrypted . '<br>';
    $decrypted = RsaUtils::dePrivate($encrypted);
    echo $decrypted . '<br>';
    echo 'encrypted by private key<br>';
    $encrypted = RsaUtils::enPrivate($data);
    echo $encrypted . '<br>';
    $decrypted = RsaUtils::dePublic($encrypted);
    echo $decrypted . '<br>';
  }
}
