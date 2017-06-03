$(document).ready(function() {
	if (!window.localStorage.getItem('clientUpdateDone')) {
		console.log('Updating client data');
		$.get('api/v1/update_access.php?id=1lu5eIWgQjth0_-vgOsyLsraTIg92x4bZ4O-hWeDD0YQ',function(data) {
			//console.log(data);
			console.log('Data update complete');
			window.localStorage.setItem('clientUpdateDone',true);
		});
	}
});

