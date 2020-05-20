
function arrSearch(array, value) {
	let response = false;
	let i = 0;
	
	array.filter(function(object) {
		const newArray = Object.values(object);
		
		if (newArray.indexOf(value) !== -1) response = i;
		
		i++;
	});
	return response;
}

function arrUnset(array, value) {
	let i = 0;
	let response = false;
	
	array.filter(function(object) {
		const newArray = Object.values(object);
		
		if (newArray.indexOf(value) !== -1)
		{
			array.splice(i, 1);
			response = true;
		}
		i++;
	});
	return response;
}

module.exports = { arrSearch, arrUnset };
