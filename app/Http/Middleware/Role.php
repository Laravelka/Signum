<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Role
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$roles)
	{
		if (!Auth::check())
		{
			if ($request->ajax() || $request->json())
			{
				return response()->json(['error' => true, 'message' => 'Вы не авторизованы.'], 401);
			}
			else
			{
				abort(401);
			}
		}
		$user = Auth::user();

		if($user->hasRole('admin'))
			return $next($request);

		foreach($roles as $role) {
			if($user->hasRole($role))
				return $next($request);
		}

		if ($request->ajax() || $request->json())
		{
			return response()->json(['error' => true, 'message' => 'Данная функция доступна только администратору.'], 401);
		}
		else
		{
			abort(401);
		}
	}
}
