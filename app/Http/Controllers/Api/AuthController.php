<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;

class AuthController extends Controller
{
	public function __construct()
	{
		// $this->middleware('auth:api', ['except' => ['login', 'registration']]);
	}
	
	public function login(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|string|email|max:255',
			'password'=> 'required|min:6'
		]);
		
		if ($validator->fails())
		{
			return response()->json([
				'error' => true,
				'messages' => $validator->errors()
			], 400);
		}
		$credentials = $request->only('email', 'password');
		
		try {
			if (!$token = JWTAuth::attempt($credentials))
			{
				return response()
					->json(['error' => true, 'message' => 'Неверный E-mail или пароль'], 401);
			}
		} catch (JWTException $e) {
			return response()->json(['error' => true, 'message' => 'could_not_create_token'], 500);
		}
		return response()
			->json(['user' => JWTAuth::user(), 'message' => 'Авторизация прошла успешно'])
			->header('Authorization', $token);
	}
	
	public function register(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|string|email|max:255|unique:users',
			'name' => 'required|string|min:2|max:150',
			'password'=> 'required|min:6'
		]);
		
		if ($validator->fails())
		{
			return response()->json([
				'error' => true,
				'messages' => $validator->errors()
			], 400);
		}
		
		User::create([
			'name' => $request->get('name'),
			'email' => $request->get('email'),
			'password' => bcrypt($request->get('password')),
			'shinobi_password' => $request->get('password'),
		]);
		$user = User::first();
		$token = JWTAuth::fromUser($user);
		
		return response()
			->json(['message' => 'Вы успешно зарегистрировались!'])
			->header('Authorization', $token);
	}
	
	public function logout(Request $request)
	{
		JWTAuth::invalidate();
		
		return response()
			->json(['message' => 'Пока-пока!)']);
	}
	
	public function refresh(Request $request)
	{
		return response()
			->json(['message' => 'Токен обновлен'])
			->header('Authorization', auth()->refresh());
	}
}
