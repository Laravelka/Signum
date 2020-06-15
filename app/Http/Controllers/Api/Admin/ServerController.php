<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Auth, Validator};
use Illuminate\Http\Request;
use App\Server;

class ServerController extends Controller
{
	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'ip'=> 'required|ip|unique:servers',
			'port'=> 'required|numeric|max:65535',
			'name'=> 'required|string|max:120',
			'super_email' => 'required|email',
			'super_password' => 'required|min:5',
		], [
			'super_email.email' => 'E-mail от админа shinobi должно быть корректным.',
			'super_email.required' => 'E-mail от админа shinobi обязателен для заполнения.',
			'super_password.required' => 'Пароль от админа shinobi обязателен для заполнения.'
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
			$server = Server::create(array_merge($request->all(), [
				'ip' => $request->ip.':'.$request->port
			]));
			
			$status = 200;
			$response = [
				'server' => $server,
				'message' => 'Сервер успешно добавлен!'
			];
		}
		return response()->json($response, $status);
	}
	
	public function getAll(Request $request)
	{
		$arrServers = Server::get();
		
		if ($arrServers->isEmpty())
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Серверов пока нет.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'servers' => $arrServers
			];
		}
		return response()->json($response, $status);
	}
	
	public function getById(Request $request)
	{
		$id = $request->id ?? 0;
		$server = Server::find($id)->first();
		
		if (!$server)
		{
			$status = 400;
			$response = [
				'error' => true,
				'message' => 'Сервер удален или не найден.'
			];
		}
		else
		{
			$status = 200;
			$response = [
				'server' => $server
			];
		}
		return response()->json($response, $status);
	}
}
