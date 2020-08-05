importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.1/workbox-sw.js');
importScripts('https://www.gstatic.com/firebasejs/7.12.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.12.0/firebase-messaging.js');

if (workbox) {
    workbox.precaching.precacheAndRoute([
        '/js/app.js',
        '/css/app.css'
    ]);
}

/*
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

messaging.setBackgroundMessageHandler(async function(payload) {
	let data = await axios.post('/logger', payload);
	
	console.log('[firebase-messaging-sw.js] Received background message ', payload);
	// Customize notification here
	const notificationTitle = 'Тестовое уведомление';
	const notificationOptions = {
		notification: {
			body: 'Тест',
			icon: '/images/logo-512x512.png'
		}
	};

	return self.registration.showNotification(notificationTitle, notificationOptions.notification);
});
*/

self.addEventListener('push', function(event) {
	let data = {};
	
	try {
		data = event.data.json();
	} catch (e) {
		data = {
			notification: {
				title: 'Обычный заголовок',
				body: 'Текст уведомления',
				icon: '/images/logo-512x512.png'
			}
		};
	}
	event.waitUntil(
		self.registration.showNotification(data.notification.title, {
			body: data.notification.body,
			icon: data.notification.image
		})
	);
});

self.addEventListener('notificationclick', function(event) {
	console.log("click:", event);
	
	event.notification.close();
	
	event.waitUntil(
		self.clients.matchAll().then(function(clientList) {
			if (clientList.length > 0) {
				return clientList[0].focus();
			}
			return self.clients.openWindow('/');
		})
	);
});