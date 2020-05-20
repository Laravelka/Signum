<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\{User, Server};

class UsersController extends Controller
{
	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name'=> 'required|string|max:191',
			'email' => 'required|email|unique:users',
			'password' => 'required|min:6|max:191',
			'server_id' => 'required|numeric',
			'shinobi_ke' => 'required|string|max:191',
			'shinobi_password' => 'required|string|max:191'
		]);
		
		if ($validator->fails())
		{
			$status = 400;
			$response = [
				'error' => true,
				'messages' => $validator->errors()
			];
		}
		elseif (!Server::find($request->server_id))
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Сервер удален или не найден.'
			];
		}
		else
		{
			$user = User::create(array_merge($request->all(), ['password' => bcrypt($request->password)]));
			
			$status = 200;
			$response = [
				'user' => $user,
				'message' => 'Пользователь успешно добавлен!'
			];
		}
		return response()->json($response, $status);
	}
	
	public function getAll(Request $request) {
		$arrUsers = User::where('roles', 'user')->get();
		
		if ($arrUsers->isEmpty())
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Пользователей пока нет.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'users' => $arrUsers
			];
		}
		return response()->json($response, $status);
	}
	
	public function getById(Request $request) {
		$id = $request->id ?? 0;
		$user = User::where([
			['id', '=', $id],
			['roles', '=', 'user']
		])->first();
		
		if (!$user)
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Пользователь удален или не найден.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'user' => $user
			];
		}
		return response()->json($response, $status);
	}
	
	public function delete($id) {
		$user = User::where([
			['id', '=', $id],
			['roles', '=', 'user']
		])->delete();
		
		if (!$user)
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Ошибка удаления.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'message' => 'Пользователь удален успешно!'
			];
		}
		return response()->json($response, $status);
	}
}
