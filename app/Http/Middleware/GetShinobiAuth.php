<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GetShinobiAuth
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (Auth::check())
		{
			$user = Auth::user();
			
			if ($user->roles !== 'admin')
			{
				$client = new \GuzzleHttp\Client();
				$server = 'http://'.$user->server()->ip.'/';

				if (empty($user->shinobi_ke))
				{
					$arguments = [
						'timeout' => 10,
						'connect_timeout' => 10,
						'form_params' => [
							'machineID' => md5($user->email),
							'mail' => $user->email,
							'pass' => $user->shinobi_password,
							'function' => 'dash'
						]
					];

					$response = $client->request(
						'POST',
						$server.'?json=true',
						$arguments
					);
					$data = json_decode($response->getBody()->getContents(), true);

					if (!empty($data['$user']['ok']))
					{
						$user->shinobi_ke = $data['$user']['ke'];
						$user->shinobi_time = Carbon::now()->addHours(1);
						$user->shinobi_token = $data['$user']['auth_token'];
						$user->save();
					}
					else
					{
						return response()->json([
							'args' => [$arguments, $server],
							'error' => true,
							'message' => 'Авторизация не прошла.',
							'response' => $data
						], 403);
					}
				}
				else
				{
					if ($user->shinobi_time < Carbon::now())
					{
						$arguments = [
							'timeout' => 10,
							'connect_timeout' => 10,
							'form_params' => [
								'machineID' => md5($user->email),
								'mail' => $user->email,
								'pass' => $user->shinobi_password,
								'function' => 'dash'
							]
						];

						$response = $client->request(
							'POST',
							$server.'?json=true',
							$arguments
						);
						$data = json_decode($response->getBody()->getContents(), true);

						if (!empty($data['$user']['ok']))
						{
							$user->shinobi_ke = $data['$user']['ke'];
							$user->shinobi_time = Carbon::now()->addHours(1);
							$user->shinobi_token = $data['$user']['auth_token'];
							$user->save();
						}
						else
						{
							return response()->json([
								'args' => [$arguments, $server, bcrypt('signum56')],
								'error' => true, 
								'message' => 'Авторизация не прошла.',
								'response' => $data
							], 403);
						}
					}
				}
			}
		}
		return $next($request);
	}
}
