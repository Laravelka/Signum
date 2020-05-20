<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Notification as Notify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Validator, Auth};
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\AndroidConfig;

class NotificationController extends Controller
{
	public function getAll(Request $request)
	{
		$arrNotify = Notify::get();
		
		if ($arrNotify->isEmpty())
		{
			return response()->json(['error' => true, 'message' => 'Уведомлений еще нет.'], 403);
		}
		else
		{
			return response()->json(['notify' => $arrNotify]);
		}
	}
	
	public function sendToUser(Request $request)
	{
		$status = 200;
		$validator = Validator::make($request->all(), [
			'id' => 'numeric',
			'body' => 'required|string|max:120',
			'type' => 'required|in:topic,token',
			'message'=> 'required|string|max:100',
		]);
		$imageUrl = $request->icon ?? config('app.url').'/images/push-logo.png';

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
			if ($request->type == 'token')
			{
				$user = User::find($request->id);
				
				if (empty($user->device_token))
				{
					$status = 500;
					$response = [
						'error' => true,
						'message' => 'Пользователь не подписан или не найден.'
					];
				}
				else
				{
					$withValue = $user->device_token;
				}
			}
			else
			{
				$withValue = $request->with ?? 'userNotify';
			}
			$sended = $this->sendNotify($request->message, $request->body, $imageUrl, $request->type, $withValue);
			
			if (!empty($sended['name']))
			{
				$notify = Notify::create(array_merge($request->all(), [
					'icon' => $imageUrl
				]));
				
				$response = [
					'notify' => $notify,
					'message' => 'Уведомление успешно отправлено.'
				];
			}
			else
			{
				$status = 500;
				$response = [
					'error' => true,
					'message' => 'Уведомление не было отправлено.'
				];
			}
		}
		return response()->json($response, $status);
	}
	
	public function subscribe(Request $request)
	{
		if (Auth::check() && $request->has('token'))
		{
			$user = Auth::user();
			$messaging = app('firebase.messaging');
			
			$user->device_token = $request->token;
			$user->save();
			
			$topic = $user->roles.'Notify';
			
			return $messaging->subscribeToTopic($topic, $request->token);
		}
		else
		{
			return response()->json(['error' => true, 'message' => 'Вы не авторизованы или не был передан subscribe токен.'], 500);
		}
	}
	
	protected function sendNotify($title, $body, $imageUrl, $type = 'topic', $value = 'userNotify')
	{
		try {
			$messaging = app('firebase.messaging');
			$apnsNotify = ApnsConfig::fromArray([
				'headers' => [
					'apns-priority' => '10',
				],
				'payload' => [
					'aps' => [
						'alert' => [
							'title' => $title,
							'body' => $body,
						],
						'badge' => 1,
					],
				],
			]);
			$androidNotify = AndroidConfig::fromArray([
				'ttl' => '3600s',
				'priority' => 'normal',
				'notification' => [
					'title' => $title,
					'body' => $body,
					'image' => $imageUrl,
					'color' => '#f45342',
				]
			]);
			$webNotify = Notification::fromArray([
				'title' => $title,
				'body' => $body,
				'image' => $imageUrl,
				'actions' => [
					[
						'action' => 'mark_read',
						'title' => 'Прочитано',
					],
					[
						'action' => 'delete',
						'title' => 'Удалить',
					]
					
				]
			]);
			$webPushNotify = WebPushConfig::fromArray([
				'notification' => [
					'title' => $title,
					'body' => $body,
					'image' => $imageUrl,
					'color' => '#000',
				],
				'fcm_options' => [
					'link' => config('app.url').'/notifications'
				],
			]);
			$rand = rand(1111, 9999);
			
			$data = [
				'test' => true,
				'actions' => [
					'delete' => [
						'type' => 'post_url',
						'url' => config('app.url').'/api/notifications/read?id='.$rand
					],
					'default' => [
						'type' => 'open_url',
						'url' => config('app.url').'/notifications'
					],
					'mark_read' => [
						'type' => 'post_url',
						'url' => config('app.url').'/api/notifications/read?id='.$rand
					]
				]
			];
			
			$message = CloudMessage::withTarget($type, $value)
				->withApnsConfig($apnsNotify)
				->withNotification($webNotify)
				->withWebPushConfig($webPushNotify)
				->withAndroidConfig($androidNotify);
					
			return $messaging->send($message);
		} catch(\Exception $e) {
			return $e;
		}
	}
}

