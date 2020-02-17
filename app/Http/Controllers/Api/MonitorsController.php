<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitorsController extends Controller
{
	protected $server = false;

	public function __construct() {
		if (Auth::check())
			$this->server = 'http://'.auth()->user()->server()->ip;
	}
	
	public function getAll(Request $request)
	{
		$user = Auth::user();
		$client = new \GuzzleHttp\Client();
		
		if ($this->server && !empty($user->shinobi_ke))
		{
			$url = $this->server.'/'.$user->shinobi_token.'/monitor/'.$user->shinobi_ke;
			
			$response = $client->request('GET', $url, [
				'headers' => ['Content-Length' => 1]
			]);
			$json = json_decode($response->getBody()->getContents(), true);

			foreach($json as $key => $value)
			{
				$json[$key]['hash'] = md5(time());
				$json[$key]['screen'] = $this->server.'/'.$user->shinobi_token.'/jpeg/'.$user->shinobi_ke.'/'.$value['mid'].'/s.jpg';
			}
			$code = 200;
		}
		else
		{
			$json = [
				'error' => true,
				'message' => 'Запрос не прошел, были отправлены не все параметры, обновите страницу и попытайтесь еще раз.'
			];
			$code = 403;
		}
		return response()->json($json, $code);
	}

	public function getById(Request $request)
	{
		$user = Auth::user();
		$client = new \GuzzleHttp\Client();
		
		if ($this->server && !empty($user->shinobi_ke))
		{
			$url = $this->server.'/'.$user->shinobi_token.'/monitor/'.$user->shinobi_ke.'/'.$request->input('id');
			
			$response = $client->request('GET', $url, [
				'headers' => ['Content-Length' => 1]
			]);
			$json = json_decode($response->getBody()->getContents(), true);
			$json['server'] = $this->server;
			$code = 200;
		}
		else
		{
			$json = [
				'error' => true,
				'message' => 'Запрос не прошел, были отправлены не все параметры, обновите страницу и попытайтесь еще раз.'
			];
			$code = 403;
		}
		return response()->json($json, $code);
	}
}
