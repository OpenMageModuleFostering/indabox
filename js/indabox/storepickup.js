var IndaboxStorePickup = Class.create();
IndaboxStorePickup.prototype = {

    initialize: function(changeMethodUrl, searchUrl, locationUrl, mediaUrl) {
        this.changeMethodUrl = changeMethodUrl;
        this.searchUrl = searchUrl;
        this.locationUrl = locationUrl;
        this.mediaUrl = mediaUrl;
        
		this.points = [];
        this.markers = [];
        this.location = null;
        this.map = null;
        this.clusterer = null;
        this.infoWindow = null;
        
        this.onInit = null;
        this.onSearchStart = null;
        this.onSearchEnd = null;
        this.onSelectPoint = null;
        
        this.setUpHook();
    },
    
    setUpHook: function () {
        var fallbackValidate = ShippingMethod.prototype.validate,
            fallbackNextStep = ShippingMethod.prototype.nextStep;
        ShippingMethod.prototype.validate = function () {
            var result,
                pointId,
                methods;
                
            result = fallbackValidate.call(this);
            if ( ! result)
                return false;
                
            methods = document.getElementsByName('shipping_method');
            for (var i=0; i<methods.length; i++) {
                if (methods[i].checked && methods[i].value !== 'ibstorepickup_ibstorepickup') {
                    return true;
                }
            }
            
            if ( ! $('indabox_accept_terms').checked) {
                alert(Translator.translate('You should accept IndaBox terms and conditions').stripTags());
                return false;
            }
            pointId = parseInt($('indabox_point_id').value, 10);
            if (pointId <= 0) {
                alert(Translator.translate('You should select one of available pick-up points to continue').stripTags());
                return false;
            }
            
            return true;
        };
        
        ShippingMethod.prototype.nextStep = function (transport) {
            fallbackNextStep.call(this, transport);
            checkout.reloadProgressBlock('shipping');
        };
    },
    
    rebound: function () {
        var i,
            bounds;
            
        if ( ! this.markers.length)
            return;
            
        bounds = new google.maps.LatLngBounds();
            
        if (this.location)
            bounds.extend(this.location.getPosition());
            
        for (i = 0; i < this.markers.length; i++)
            bounds.extend(this.markers[i].getPosition());
            
        this.map.fitBounds(bounds);
    },
    
    showPointInfo: function (marker, idx) {
        var point = this.points[idx];
        this.infoWindow.setContent(
            '<div class="indabox-info-window">' +
            '<div class="row title">' + point.name + '</div>' +
            '<div class="row"></div>' +
            '<div class="row distance"><strong>' + Translator.translate('Distance') + ': </strong>' + point.distance + ' km </div>' +
            '<div class="row telephone"><strong>' + Translator.translate('Telephone') + ': </strong>' + point.telephone + '</div>'+
            '<div class="row hours"><strong>' + Translator.translate('Hours') + ': </strong>' + point.hours + '</div>'+
            '<div class="row select"><a class="indabox-select-me" rel="' + idx + '" href="#">' + Translator.translate('Select this pick-up point') + '</a></div>'
        );
        this.infoWindow.open(this.map, marker);  
    },
	
	setPoints: function (points) {
        var i, marker, point, caller = this;
        for (i = 0; i < this.markers.length; i++)
            this.markers[i].setMap(null);
            
        this.clusterer.clearMarkers();
        this.markers.length = 0;
    
		this.points = points;
        if ( ! this.points.length)
            return;
        
        for (i = 0; i < this.points.length; i++) {
            point = this.points[i];
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(point.latitude, point.longitude),
                map: this.map,
                icon: this.mediaUrl + 'marker_point.png',
                shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'       
            });
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function () {
                    caller.showPointInfo(marker, i);
                }
            })(marker, i));
            this.markers.push(marker);
        }
        
        this.clusterer.addMarkers(this.markers);
        
        this.rebound();
	},
    
    choosePoint: function (idx) {
        this.infoWindow.close();
        if (typeof(this.onSelectPoint) === 'function')
            this.onSelectPoint.call(this, this.points[idx]);
    },
	
    initMap: function (container) {
        var mapOptions = {
                zoom: 5,
                center: new google.maps.LatLng(41.9000, 12.4833)
            },
            caller = this;
        
        Event.observe(container, 'click', function (event) { 
            var target = Event.findElement(event);
            
            if (target.hasClassName('indabox-select-me')) {
                Event.stop(event);
                caller.choosePoint(parseInt(target.rel), 10);
            }
        });
        
        this.infoWindow = new google.maps.InfoWindow();
        this.map = new google.maps.Map(document.getElementById(container), mapOptions);
        this.clusterer = new MarkerClusterer(this.map, this.markers);
          
        if (typeof(this.onInit) === 'function')
            this.onInit.call(this);
    },
    
    setLocation: function (lat, lng) {
        this.location = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lng),
            map: this.map,
            icon: this.mediaUrl + 'marker_location.png',
            shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'       
        });
        
        this.rebound();
    },
    
    locate: function (address) {
        var url = this.locationUrl,
            caller = this;
        
        if (this.location) {
            this.location.setMap(null);
            this.location = null;
        }
        
        url = url + 'address/' + address;
    
        new Ajax.Request(url, {
            onSuccess: function(transport) {
                var json = transport.responseText.evalJSON();
                
                if (json.error) {
                    return;
                }
                
                caller.setLocation(json.latitude, json.longitude);
            }
        });
    },
    
    search: function (address, radius) {
        var url = this.searchUrl,
            caller = this;
            
        if (typeof(this.onSearchStart) === 'function')
            this.onSearchStart.call(this);
        
        this.locate(address);
        
        url = url + 'address/' + address + '/radius/' + radius;
        
        new Ajax.Request(url, {
            onComplete: function () {
                if (typeof(caller.onSearchEnd) === 'function')
                    caller.onSearchEnd.call(caller, caller.points);
            },
            onSuccess: function(transport) {
                var json = transport.responseText.evalJSON();
                
                if (json.error) {
                    alert(json.message ? json.message : Translator.translate('Error'));
                    return;
                }
                
                caller.setPoints(json.points);
            }
        });
    },
    
    initGoogleMap: function (scriptUrl, container) {
        if ( ! window.google || ! google.maps)
            this.loadGoogleMap(scriptUrl, container);
        else
            this.initMap(container);
    },
    
    loadGoogleMap: function (scriptUrl, container) {
        var script = document.createElement('script'),
            functionName = 'initMap' + Math.floor(Math.random() * 1000001),
            caller = this;
            
        window[functionName] = function () {
            caller.initMap(container);
        }
        
        script.type = 'text/javascript';
        script.src = scriptUrl + '&callback=' + functionName;
        document.body.appendChild(script);
    },
	
	setUseStorePickup: function(flag)
	{
		var url = this.changeMethodUrl;	
		
		if (flag)
			url += 'flag/1';
		else
			url += 'flag/0';
		
		var request = new Ajax.Request(url, {method: 'get', onFailure: ""}); 			
	}
	
}
