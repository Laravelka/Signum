<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		
		<title>Signum</title>
		<link href="/manifest.json" rel="manifest">
		<!-- Fonts -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.1.1/vuetify.min.css" integrity="sha256-8lXpjKSi0rhttg2cIQGJOhWdN+hVAmW8MmXm70BjjVc=" crossorigin="anonymous" />
		<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
		<script src="{{ mix('css/app.css') }}"></script>
	</head>
	<body>
		<div id="app"></div>
		<script src="{{ mix('js/app.js') }}"></script>
		<script>
		// Check that service workers are supported
			if ('serviceWorker' in navigator) {
				// Use the window load event to keep the page load performant
				window.addEventListener('load', () => {
					navigator.serviceWorker.register('/service-worker.js');
				});
			}
		</script>
	</body>
</html>
