
<div id="location" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title title back"><i class="fa fa-long-arrow-left"></i> <span></span></h4>
			</div>
			<div class="modal-body">
				<input type="text" value="" id="autocomplete" autocomplete="off" onfocus="geolocate()" class="form-control" />

				<div id="map"></div>

				<div id="infowindow-content">
					<img src="" width="16" height="16" id="place-icon">
					<span id="place-name"  class="title"></span><br>
					<span id="place-address"></span>
				</div>

				<button class="btn btn-primary btn-block hide" id="confirm-location">Confirm Location</button>

				<div id="current-location" onclick="getUserLocation();"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/></svg> <span>Current Location</span></div>
			</div>
		</div>
	</div>
</div>

<script>
$(function() {
	$('.location-auto').on('focus', function() {
		$el = $(this);

		$('#location').modal('show');
		$('#confirm-location').addClass('hide');

		$('#location .title>span').text($el.attr('placeholder'));

		if ($el.siblings('input[type="hidden"]').val()) {
			var split = $el.siblings('input[type="hidden"]').val().split(',');
			var geolocation = {
				lat: parseFloat(split.shift()),
				lng: parseFloat(split.shift())
			};

			map.setCenter(geolocation);
			marker.setPosition(geolocation);
		}
	});

	$('#location .title>i, #confirm-location').on('click', function() {
		$('#location').modal('hide');
	});
});

var placeSearch, autocomplete, infowindow, infowindowContent, marker, map, service, geocoder;

function initMap() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: {lat: 28.6129167, lng: 77.1594719},
		zoom: 13
	});

	service = new google.maps.places.PlacesService(map);
	geocoder = new google.maps.Geocoder();

	autocomplete = new google.maps.places.Autocomplete((document.getElementById('autocomplete')));

	autocomplete.bindTo('bounds', map);

	infowindow = new google.maps.InfoWindow();
	infowindowContent = document.getElementById('infowindow-content');
	infowindow.setContent(infowindowContent);

	marker = new google.maps.Marker({
		map: map,
		anchorPoint: new google.maps.Point(0, -29)
	});

	google.maps.event.addListener(map, "click", function (e) {
		updateMap(e.latLng);
	});

	autocomplete.addListener('place_changed', fillInAddress);

	/* iphone bugs */
	if (navigator.userAgent.match(/(iPad|iPhone|iPod)/g)) {
		setTimeout(function() {
			var container = document.getElementsByClassName('pac-container')[0];
			container.addEventListener('touchend', function(e) {
				e.stopImmediatePropagation();
			});
		}, 500);
	}
}

function fillInAddress() {
	var place = autocomplete.getPlace();

	infowindow.close();
	marker.setVisible(false);

	var place = autocomplete.getPlace();

	if (!place.geometry) {
		return;
	}

	if (place.geometry.viewport) {
		map.fitBounds(place.geometry.viewport);
	} else {
		map.setCenter(place.geometry.location);
		map.setZoom(17);
	}

	marker.setPosition(place.geometry.location);
	marker.setVisible(true);

	infowindowContent.children['place-icon'].src = place.icon;
	infowindowContent.children['place-name'].textContent = place.name;
	infowindowContent.children['place-address'].textContent = place.formatted_address;

	infowindow.open(map, marker);

	$('body').trigger('updateLocation', {address: place.formatted_address, latLng: place.geometry.location.lat() + ',' + place.geometry.location.lng()});

	$('#location .title>i').trigger('click');
}

$('body').on('updateLocation', function(e, data) {
	$el.siblings('input[type="hidden"]').val(data.latLng);
	$el.val(data.address);
});

function updateMap(latLng) {
	map.setCenter(latLng);
	marker.setPosition(latLng);

	geocoder.geocode({'latLng': latLng}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			if (results[0]) {
				service.getDetails({placeId: results[0].place_id}, function(place, status) {
					if (status === google.maps.places.PlacesServiceStatus.OK) {
						infowindowContent.children['place-icon'].src = place.icon;
						infowindowContent.children['place-name'].textContent = place.name;
						infowindowContent.children['place-address'].textContent = place.formatted_address;
						infowindow.setContent(infowindowContent);
						infowindow.open(map, marker);

						$('#autocomplete').val(place.formatted_address);

						$('body').trigger('updateLocation', {address: place.formatted_address, latLng: results[0].geometry.location.lat() + ',' + results[0].geometry.location.lng()});
						$('body').trigger('fetchCar');

						$('#confirm-location').removeClass('hide');
						$('#current-location').find('i.fa-spinner').remove();
					}
				});
			}
		}
	});
}

function geolocate() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			var geolocation = {
				lat: position.coords.latitude,
				lng: position.coords.longitude
			};

			var circle = new google.maps.Circle({
				center: geolocation,
				radius: position.coords.accuracy
			});

			autocomplete.setBounds(circle.getBounds());
		});
	}
}

function getUserLocation() {
	$('#current-location').append('<i class="fa fa-spinner fa-spin fa-2x pull-right"></i>');

	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition((position) => {
			updateMap({
				lat: position.coords.latitude,
				lng: position.coords.longitude
			});
		}, (error) => console.warn(error), {
			enableHighAccuracy: true,
			timeout: 5000,
			maximumAge: 0
		});
	}
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDc1u40Pid921JdsFjSjYmFpuX-B-iCryM&libraries=places&callback=initMap" async defer></script>
<script>
var css = `.pac-container {
	z-index: 2000;
}
#map {
	min-height: 360px;
}
#current-location {
    background: #f1f1f1;
    margin-top: 20px;
    padding: 10px;
    cursor: pointer;
    box-shadow: 0 0 4px -2px #000;
	transition: all 0.3s ease-in-out;
}
#current-location:hover {
	background: #fdfdfd;
}
#current-location span {
    vertical-align: super;
    margin-left: 15px;
    font-size: 110%;
}
`;
$(function() {
	$('head').append(`<style>${css}</style>`);
})
</script>
