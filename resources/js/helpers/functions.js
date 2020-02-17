function isJson(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}

function fullScreen(element) {
	if(element.requestFullscreen) {
		element.requestFullscreen();
	} else if(element.webkitrequestFullscreen) {
		element.webkitRequestFullscreen();
	} else if(element.mozRequestFullscreen) {
		element.mozRequestFullScreen();
	}
}

function fullScreenCancel() {
	if(document.requestFullscreen) {
		document.requestFullscreen();
	} else if(document.webkitRequestFullscreen ) {
		document.webkitRequestFullscreen();
	} else if(document.mozRequestFullscreen) {
		document.mozRequestFullScreen();
	}
}

function ucFirst(str) {
  if (!str) return str;

  return str[0].toUpperCase() + str.slice(1);
}

export { isJson, ucFirst, fullScreen, fullScreenCancel };