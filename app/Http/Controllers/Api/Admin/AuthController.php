<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
	public function login(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'password'=> 'required|min:6'
		]);
		
		if ($validator->fails())
		{
			$code = 400;
			$response = [
				'error' => true,
				'messages' => $validator->errors()
			];
		}
		else
		{
			if ($request->password !== env('ADMIN_PASSWORD'))
			{
				$code = 401;
				$response = [
					'error' => true,
					'message' => 'Неверный пароль'
				];
			}
			else
			{
				$cookies = cookie('admin_token', bcrypt(microtime()), 60 * 48);
				
				$code = 200;
				$response = [
					'message' => 'Авторизация прошла успешно'
				];
			}
		}
		return response()->json($response, $code)->cookie($cookies);
}
