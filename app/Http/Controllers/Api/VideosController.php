<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use \Carbon\Carbon;

class VideosController extends Controller
{
	protected $server = false;

	public function __construct() {
		if (Auth::check())
			$this->server = 'http://'.auth()->user()->server()->ip;
	}
	
	public function getByMonitorId(Request $request)
	{
		$user = Auth::user();
		$client = new \GuzzleHttp\Client();
		
		if (
			$this->server && 
			!empty($user->shinobi_ke) && 
			!empty($request->monitor_id) && 
			!empty($request->date)
		) {
			$data = Carbon::create($request->date);
			
			$url = $this->server.'/'.
				$user->shinobi_token.
				'/videos/'.
				$user->shinobi_ke.'/'.
				$request->monitor_id.
				'?start='.
				$data.
				'&end='.
				$data->addMinutes(10).
				'&startOperator=>=';
			
			$response = $client->request('GET', $url);
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
