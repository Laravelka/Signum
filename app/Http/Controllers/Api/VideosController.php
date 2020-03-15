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
			
			// $timezone = $this->getTimezone($user->shinobi_token, $user->shinobi_ke, $request->monitor_id);
			// $data->setTimezone($timezone);

			$data->setTimezone(5);

			$url = $this->server.'/' .
				$user->shinobi_token .
				'/videos/' .
				$user->shinobi_ke . '/' .
				$request->monitor_id . 
				'?end='.
				$data .
				'&limit=' .
				config('shinobi.videos.limit') .
				'&endOperator=>=';
			
			$response = $client->request('GET', $url);
			$json = json_decode($response->getBody()->getContents(), true);

			$json['server'] = $this->server;
			$json['url'] = $url;
			//$json['timezone'] = $timezone;
			$json['data'] = $data->toString();
			
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
	
	public function getByMonitor(Request $request)
	{
		$user = Auth::user();
		$client = new \GuzzleHttp\Client();
		
		if (
			$this->server && 
			!empty($user->shinobi_ke) && 
			!empty($request->monitor_id)
		) {
			$url = $this->server.'/' .
				$user->shinobi_token .
				'/videos/' .
				$user->shinobi_ke . '/' .
				$request->monitor_id .
				'?limit=' .
				config('shinobi.videos.limit');
			
			$httpResponse = $client->request('GET', $url);
			$response = json_decode($httpResponse->getBody()->getContents(), true);
			
			$json = [
				'message' => 'Вродь работает',
				'response' => $response
			];
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
	
	private function getTimezone($shinobi_token, $shinobi_ke, $monitor_id) {

		$client = new \GuzzleHttp\Client();
	
		$url = $this->server.'/'.
			$shinobi_token.
			'/videos/'.
			$shinobi_ke.'/'.
			$monitor_id .
			'?limit=' .
			config('shinobi.videos.limit');
			
		$response = json_decode($client->request('GET', $url)->getBody()->getContents(), true);

		if (isset($response['videos'][0])) {
			preg_match('~([+-]\d\d):\d{2}$~', $response['videos'][0]['time'], $matches, PREG_OFFSET_CAPTURE);
			if (isset($matches[1])) {
				$ret = (($matches[1][0][0] == '+') ? 1 : -1) * ($matches[1][0][1] * 10 + $matches[1][0][2]);				
				return $ret;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
