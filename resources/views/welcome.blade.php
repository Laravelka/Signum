<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		
		<title>Signum.video</title>
		<link href="/manifest.json" rel="manifest">
		<!-- Fonts -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.3.0-beta.7/vuetify.min.css" />
		<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
		<link href="{{ mix('css/app.css') }}" rel="stylesheet">
		
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link href="/image/apple_splash_2048.png" sizes="2048x2732" rel="apple-touch-startup-image" />
		<link href="/image/apple_splash_1668.png" sizes="1668x2224" rel="apple-touch-startup-image" />
		<link href="/image/apple_splash_1536.png" sizes="1536x2048" rel="apple-touch-startup-image" />
		<link href="/image/apple_splash_1125.png" sizes="1125x2436" rel="apple-touch-startup-image" />
		<link href="/image/apple_splash_1242.png" sizes="1242x2208" rel="apple-touch-startup-image" />
		<link href="/image/apple_splash_750.png" sizes="750x1334" rel="apple-touch-startup-image" />
		<link href="/image/apple_splash_640.png" sizes="640x1136" rel="apple-touch-startup-image" />
		<style>
			.buttonInstall {
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				padding: 8px;
			}
		</style>
	</head>
	<body>
		<noscript>
			This application needs JavaScript to work, please enable JavaScript on your browser.
			Это приложение не работает без JavaScript, пожалуйста включите его.
		</noscript>
		<div id="app"></div>
		<script src="{{ mix('js/app.js') }}"></script>
		<script src="https://www.gstatic.com/firebasejs/7.12.0/firebase-app.js"></script>
		<script src="https://www.gstatic.com/firebasejs/7.12.0/firebase-messaging.js"></script>
		<script src="https://www.gstatic.com/firebasejs/7.12.0/firebase-analytics.js"></script>

		<script>
			window.addEventListener('load', async() => {
				if ('serviceWorker' in navigator) {
					const appServerKey = 'BPgd3KsZ41qhg06sMt00c_iKOge-m4HBst0AuH1dRxi3ZoArHEAOskOJeCStDJi8V51oBzTXy7K7JTfMGb78Ut8';
					
					function urlBase64ToUint8Array(base64String) {
						var padding = '='.repeat((4 - base64String.length % 4) % 4);
						var base64 = (base64String + padding)
							.replace(/\-/g, '+')
							.replace(/_/g, '/');
						var rawData = window.atob(base64);
						var outputArray = new Uint8Array(rawData.length);

						for (var i = 0; i < rawData.length; ++i) {
							outputArray[i] = rawData.charCodeAt(i);
						}
						return outputArray;
					}
					
					if (!('PushManager' in window)) {  
						console.log('Push messaging isn\'t supported.');  
						return;  
					}
					
					if (Notification.permission === 'denied') {  
						console.log('The user has blocked notifications.');  
						return;  
					}
					
					navigator.serviceWorker.register('/firebase-messaging-sw.js', {scope: "/"})
					.then(async(reg) => {
						console.log(reg);
						
						if (reg.active)
						{
							var firebaseConfig = {
								apiKey: "AIzaSyCMMoRtxuv5-0Y-Pk3HhrtAjo-73WS-E5U",
								authDomain: "signum-271912.firebaseapp.com",
								databaseURL: "https://signum-271912.firebaseio.com",
								projectId: "signum-271912",
								storageBucket: "signum-271912.appspot.com",
								messagingSenderId: "478617400236",
								appId: "1:478617400236:web:51dcd45879199cff0d4009",
								measurementId: "G-48ZKLT360S"
							};
							// Initialize Firebase
							firebase.initializeApp(firebaseConfig);

							const messaging = firebase.messaging();
							
							messaging.usePublicVapidKey(appServerKey);
							messaging.requestPermission()
							.then(() => {
								return messaging.getToken();
							})
							.then((token) => {
								axios.post('/subscribe', {token: token})
								.then(async(result) => {
									console.log(result.data);
									
									const subscription = await reg.pushManager.subscribe({
										userVisibleOnly: true,
										applicationServerKey: urlBase64ToUint8Array(appServerKey)
									});

									console.log('sub: ', subscription);
								})
								.catch((error) => {
									console.error(error)
								});
							})
							.catch((error) => {
								console.error(error)
							});
							
							messaging.onMessage(function(payload){
							  console.log('onMessage:', payload);
							});
						}
					});
				}
				/*
				let deferredPrompt;
				let buttonInstall = document.getElementById('buttonInstall');
				
				window.addEventListener('beforeinstallprompt', (e) => {
					// Prevent the mini-infobar from appearing on mobile
					e.preventDefault();
					// Stash the event so it can be triggered later.
					deferredPrompt = e;
					// Update UI notify the user they can install the PWA
					showInstallPromotion();
				});
				
				buttonInstall.onclick = function(e) {
					console.log(e);
					// Hide the app provided install promotion
					console.log('hideMyInstallPromotion() ');
					// Show the install prompt
					deferredPrompt.prompt();
					// Wait for the user to respond to the prompt
					deferredPrompt.userChoice.then((choiceResult) => {
						if (choiceResult.outcome === 'accepted') {
							console.log('User accepted the install prompt');
						} else {
							console.log('User dismissed the install prompt');
						}
					});
				};
				*/
				
				window.addEventListener('appinstalled', (evt) => {
					// Log install to analytics
					console.log('INSTALL: Success', evt);
				});
			});
		</script>
	</body>
</html>
