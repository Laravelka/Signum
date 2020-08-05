<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Facades\{Auth, Validator};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class AdminController extends Controller
{
	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name'=> 'required|string|max:120',
			'email' => 'required|email|unique:users',
			'password' => 'required|min:6',
		]);
		
		if ($validator->fails())
		{
			$status = 400;
			$response = [
				'error' => true,
				'messages' => $validator->errors()
			];
		}
		else
		{
			$admin = User::create(array_merge($request->all(), [
				'roles' => 'admin',
				'password' => bcrypt($request->password)
			]));
			
			$status = 200;
			$response = [
				'admin' => $admin,
				'message' => 'Администратор успешно добавлен!'
			];
		}
		return response()->json($response, $status);
	}
	
	public function getAll(Request $request) {
		$arrAdmins = User::where('roles', 'admin')->get();
		
		if ($arrAdmins->isEmpty())
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Админов пока нет.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'admins' => $arrAdmins
			];
		}
		return response()->json($response, $status);
	}
	
	public function getById(Request $request) {
		$id = $request->id ?? 0;
		$admin = User::find($id)->where('roles', 'admin')->first();
		
		if (!$admin)
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Админ удален или не найден.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'admin' => $admin
			];
		}
		return response()->json($response, $status);
	}
	
	public function delete(Request $request) {
		$id = $request->id ?? 0;
		$current = Auth::id();
		
		if ($id == $current)
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Нельзя удалить самого себя.'
			];
		}
		elseif (!User::find($id)->where('roles', 'admin')->delete())
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
				'message' => 'Админ удален успешно!'
			];
		}
		return response()->json($response, $status);
	}
}
