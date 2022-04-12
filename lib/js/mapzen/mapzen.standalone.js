(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.Mapzen = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
function corslite(url, callback, cors) {
    var sent = false;

    if (typeof window.XMLHttpRequest === 'undefined') {
        return callback(Error('Browser not supported'));
    }

    if (typeof cors === 'undefined') {
        var m = url.match(/^\s*https?:\/\/[^\/]*/);
        cors = m && (m[0] !== location.protocol + '//' + location.hostname +
                (location.port ? ':' + location.port : ''));
    }

    var x = new window.XMLHttpRequest();

    function isSuccessful(status) {
        return status >= 200 && status < 300 || status === 304;
    }

    if (cors && !('withCredentials' in x)) {
        // IE8-9
        x = new window.XDomainRequest();

        // Ensure callback is never called synchronously, i.e., before
        // x.send() returns (this has been observed in the wild).
        // See https://github.com/mapbox/mapbox.js/issues/472
        var original = callback;
        callback = function() {
            if (sent) {
                original.apply(this, arguments);
            } else {
                var that = this, args = arguments;
                setTimeout(function() {
                    original.apply(that, args);
                }, 0);
            }
        }
    }

    function loaded() {
        if (
            // XDomainRequest
            x.status === undefined ||
            // modern browsers
            isSuccessful(x.status)) callback.call(x, null, x);
        else callback.call(x, x, null);
    }

    // Both `onreadystatechange` and `onload` can fire. `onreadystatechange`
    // has [been supported for longer](http://stackoverflow.com/a/9181508/229001).
    if ('onload' in x) {
        x.onload = loaded;
    } else {
        x.onreadystatechange = function readystate() {
            if (x.readyState === 4) {
                loaded();
            }
        };
    }

    // Call the callback with the XMLHttpRequest object as an error and prevent
    // it from ever being called again by reassigning it to `noop`
    x.onerror = function error(evt) {
        // XDomainRequest provides no evt parameter
        callback.call(this, evt || true, null);
        callback = function() { };
    };

    // IE9 must have onprogress be set to a unique function.
    x.onprogress = function() { };

    x.ontimeout = function(evt) {
        callback.call(this, evt, null);
        callback = function() { };
    };

    x.onabort = function(evt) {
        callback.call(this, evt, null);
        callback = function() { };
    };

    // GET is the only supported HTTP Verb by XDomainRequest and is the
    // only one supported here.
    x.open('GET', url, true);

    // Send the request. Sending data is not supported.
    x.send(null);
    sent = true;

    return x;
}

if (typeof module !== 'undefined') module.exports = corslite;

},{}],2:[function(require,module,exports){
(function (global){
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null),
	Util = require('../util');

module.exports = {
	class: L.Class.extend({
		options: {
			serviceUrl: 'https://search.mapzen.com/v1',
			geocodingQueryParams: {},
			reverseQueryParams: {}
		},

		initialize: function(apiKey, options) {
			L.Util.setOptions(this, options);
			this._apiKey = apiKey;
			this._lastSuggest = 0;
		},

		geocode: function(query, cb, context) {
			var _this = this;
			Util.getJSON(this.options.serviceUrl + "/search", L.extend({
				'api_key': this._apiKey,
				'text': query
			}, this.options.geocodingQueryParams), function(data) {
				cb.call(context, _this._parseResults(data, "bbox"));
			});
		},

		suggest: function(query, cb, context) {
			var _this = this;
			Util.getJSON(this.options.serviceUrl + "/autocomplete", L.extend({
				'api_key': this._apiKey,
				'text': query
			}, this.options.geocodingQueryParams), L.bind(function(data) {
				if (data.geocoding.timestamp > this._lastSuggest) {
					this._lastSuggest = data.geocoding.timestamp;
					cb.call(context, _this._parseResults(data, "bbox"));
				}
			}, this));
		},

		reverse: function(location, scale, cb, context) {
			var _this = this;
			Util.getJSON(this.options.serviceUrl + "/reverse", L.extend({
				'api_key': this._apiKey,
				'point.lat': location.lat,
				'point.lon': location.lng
			}, this.options.reverseQueryParams), function(data) {
				cb.call(context, _this._parseResults(data, "bounds"));
			});
		},

		_parseResults: function(data, bboxname) {
			var results = [];
			L.geoJson(data, {
				pointToLayer: function (feature, latlng) {
					return L.circleMarker(latlng);
				},
				onEachFeature: function(feature, layer) {
					var result = {},
						bbox,
						center;

					if (layer.getBounds) {
						bbox = layer.getBounds();
						center = bbox.getCenter();
					} else {
						center = layer.getLatLng();
						bbox = L.latLngBounds(center, center);
					}

					result.name = layer.feature.properties.label;
					result.center = center;
					result[bboxname] = bbox;
					result.properties = layer.feature.properties;
					results.push(result);
				}
			});
			return results;
		}
	}),

	factory: function(apiKey, options) {
		return new L.Control.Geocoder.Mapzen(apiKey, options);
	}
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../util":3}],3:[function(require,module,exports){
(function (global){
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null),
	lastCallbackId = 0,
	htmlEscape = (function() {
		// Adapted from handlebars.js
		// https://github.com/wycats/handlebars.js/
		var badChars = /[&<>"'`]/g;
		var possible = /[&<>"'`]/;
		var escape = {
		  '&': '&amp;',
		  '<': '&lt;',
		  '>': '&gt;',
		  '"': '&quot;',
		  '\'': '&#x27;',
		  '`': '&#x60;'
		};

		function escapeChar(chr) {
		  return escape[chr];
		}

		return function(string) {
			if (string == null) {
				return '';
			} else if (!string) {
				return string + '';
			}

			// Force a string conversion as this will be done by the append regardless and
			// the regex test will do this transparently behind the scenes, causing issues if
			// an object's to string has escaped characters in it.
			string = '' + string;

			if (!possible.test(string)) {
				return string;
			}
			return string.replace(badChars, escapeChar);
		};
	})();

module.exports = {
	jsonp: function(url, params, callback, context, jsonpParam) {
		var callbackId = '_l_geocoder_' + (lastCallbackId++);
		params[jsonpParam || 'callback'] = callbackId;
		window[callbackId] = L.Util.bind(callback, context);
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = url + L.Util.getParamString(params);
		script.id = callbackId;
		document.getElementsByTagName('head')[0].appendChild(script);
	},

	getJSON: function(url, params, callback) {
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.onreadystatechange = function () {
			if (xmlHttp.readyState !== 4){
				return;
			}
			if (xmlHttp.status !== 200 && xmlHttp.status !== 304){
				callback('');
				return;
			}
			callback(JSON.parse(xmlHttp.response));
		};
		xmlHttp.open('GET', url + L.Util.getParamString(params), true);
		xmlHttp.setRequestHeader('Accept', 'application/json');
		xmlHttp.send(null);
	},

	template: function (str, data) {
		return str.replace(/\{ *([\w_]+) *\}/g, function (str, key) {
			var value = data[key];
			if (value === undefined) {
				value = '';
			} else if (typeof value === 'function') {
				value = value(data);
			}
			return htmlEscape(value);
		});
	},

	htmlEscape: htmlEscape
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],4:[function(require,module,exports){
arguments[4][1][0].apply(exports,arguments)
},{"dup":1}],5:[function(require,module,exports){
// Console-polyfill. MIT license.
// https://github.com/paulmillr/console-polyfill
// Make it safe to do console.log() always.
(function(global) {
  'use strict';
  if (!global.console) {
    global.console = {};
  }
  var con = global.console;
  var prop, method;
  var dummy = function() {};
  var properties = ['memory'];
  var methods = ('assert,clear,count,debug,dir,dirxml,error,exception,group,' +
     'groupCollapsed,groupEnd,info,log,markTimeline,profile,profiles,profileEnd,' +
     'show,table,time,timeEnd,timeline,timelineEnd,timeStamp,trace,warn').split(',');
  while (prop = properties.pop()) if (!con[prop]) con[prop] = {};
  while (method = methods.pop()) if (typeof con[method] !== 'function') con[method] = dummy;
  // Using `this` for web workers & supports Browserify / Webpack.
})(typeof window === 'undefined' ? this : window);

},{}],6:[function(require,module,exports){
(function (global){
/*
 * leaflet-geocoder-mapzen
 * Leaflet plugin to search (geocode) using Mapzen Search or your
 * own hosted version of the Pelias Geocoder API.
 *
 * License: MIT
 * (c) Mapzen
 */
'use strict';

// Polyfill console and its methods, if missing. (As it tends to be on IE8 (or lower))
// when the developer console is not open.
require('console-polyfill');

var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
var corslite = require('@mapbox/corslite');

// Import utility functions. TODO: switch to Lodash (no IE8 support) in v2
var throttle = require('./utils/throttle');
var escapeRegExp = require('./utils/escapeRegExp');

var VERSION = '1.9.4';
var MINIMUM_INPUT_LENGTH_FOR_AUTOCOMPLETE = 1;
var FULL_WIDTH_MARGIN = 20; // in pixels
var FULL_WIDTH_TOUCH_ADJUSTED_MARGIN = 4; // in pixels
var RESULTS_HEIGHT_MARGIN = 20; // in pixels
var API_RATE_LIMIT = 250; // in ms, throttled time between subsequent requests to API

// Text strings in this geocoder.
var TEXT_STRINGS = {
  'INPUT_PLACEHOLDER': 'Search',
  'INPUT_TITLE_ATTRIBUTE': 'Search',
  'RESET_TITLE_ATTRIBUTE': 'Reset',
  'NO_RESULTS': 'No results were found.',
  // Error codes.
  // https://mapzen.com/documentation/search/http-status-codes/
  'ERROR_403': 'A valid API key is needed for this search feature.',
  'ERROR_404': 'The search service cannot be found. :-(',
  'ERROR_408': 'The search service took too long to respond. Try again in a second.',
  'ERROR_429': 'There were too many requests. Try again in a second.',
  'ERROR_500': 'The search service is not working right now. Please try again later.',
  'ERROR_502': 'Connection lost. Please try again later.',
  // Unhandled error code
  'ERROR_DEFAULT': 'The search service is having problems :-('
};

var Geocoder = L.Control.extend({

  version: VERSION,

  // L.Evented is present in Leaflet v1+
  // L.Mixin.Events is legacy; was deprecated in Leaflet v1 and will start
  // logging deprecation warnings in console in v1.1
  includes: L.Evented ? L.Evented.prototype : L.Mixin.Events,

  options: {
    position: 'topleft',
    attribution: 'Geocoding by <a href="https://mapzen.com/projects/search/">Mapzen</a>',
    url: 'https://search.mapzen.com/v1',
    placeholder: null, // Note: this is now just an alias for textStrings.INPUT_PLACEHOLDER
    bounds: false,
    focus: true,
    layers: null,
    panToPoint: true,
    pointIcon: true, // 'images/point_icon.png',
    polygonIcon: true, // 'images/polygon_icon.png',
    fullWidth: 650,
    markers: true,
    overrideBbox: false,
    expanded: false,
    autocomplete: true,
    place: false,
    textStrings: TEXT_STRINGS
  },

  initialize: function (apiKey, options) {
    // For IE8 compatibility (if XDomainRequest is present),
    // we set the default value of options.url to the protocol-relative
    // version, because XDomainRequest does not allow http-to-https requests
    // This is set first so it can always be overridden by the user
    if (window.XDomainRequest) {
      this.options.url = '//search.mapzen.com/v1';
    }

    // If the apiKey is omitted entirely and the
    // first parameter is actually the options
    if (typeof apiKey === 'object' && !!apiKey) {
      options = apiKey;
    } else {
      this.apiKey = apiKey;
    }

    // Deprecation warnings
    // If options.latlng is defined, warn. (Do not check for falsy values, because it can be set to false.)
    if (options && typeof options.latlng !== 'undefined') {
      // Set user-specified latlng to focus option, but don't overwrite if it's already there
      if (typeof options.focus === 'undefined') {
        options.focus = options.latlng;
      }
      console.warn('[leaflet-geocoder-mapzen] DEPRECATION WARNING:',
        'As of v1.6.0, the `latlng` option is deprecated. It has been renamed to `focus`. `latlng` will be removed in a future version.');
    }

    // Deprecate `title` option
    if (options && typeof options.title !== 'undefined') {
      options.textStrings = options.textStrings || {};
      options.textStrings.INPUT_TITLE_ATTRIBUTE = options.title;
      console.warn('[leaflet-geocoder-mapzen] DEPRECATION WARNING:',
        'As of v1.8.0, the `title` option is deprecated. Please set the property `INPUT_TITLE_ATTRIBUTE` on the `textStrings` option instead. `title` will be removed in a future version.');
    }

    // `placeholder` is not deprecated, but it is an alias for textStrings.INPUT_PLACEHOLDER
    if (options && typeof options.placeholder !== 'undefined') {
      // textStrings.INPUT_PLACEHOLDER has priority, if defined.
      if (!(options.textStrings && typeof options.textStrings.INPUT_PLACEHOLDER !== 'undefined')) {
        options.textStrings = options.textStrings || {};
        options.textStrings.INPUT_PLACEHOLDER = options.placeholder;
      }
    }

    // Merge any strings that are not customized
    if (options && typeof options.textStrings === 'object') {
      for (var prop in this.options.textStrings) {
        if (typeof options.textStrings[prop] === 'undefined') {
          options.textStrings[prop] = this.options.textStrings[prop];
        }
      }
    }

    // Now merge user-specified options
    L.Util.setOptions(this, options);
    this.markers = [];
  },

  /**
   * Resets the geocoder control to an empty state.
   *
   * @public
   */
  reset: function () {
    this._input.value = '';
    L.DomUtil.addClass(this._reset, 'leaflet-pelias-hidden');
    this.removeMarkers();
    this.clearResults();
    this.fire('reset');
  },

  getLayers: function (params) {
    var layers = this.options.layers;

    if (!layers) {
      return params;
    }

    params.layers = layers;
    return params;
  },

  getBoundingBoxParam: function (params) {
    /*
     * this.options.bounds can be one of the following
     * true //Boolean - take the map bounds
     * false //Boolean - no bounds
     * L.latLngBounds(...) //Object
     * [[10, 10], [40, 60]] //Array
    */
    var bounds = this.options.bounds;

    // If falsy, bail
    if (!bounds) {
      return params;
    }

    // If set to true, use map bounds
    // If it is a valid L.LatLngBounds object, get its values
    // If it is an array, try running it through L.LatLngBounds
    if (bounds === true && this._map) {
      bounds = this._map.getBounds();
      params = makeParamsFromLeaflet(params, bounds);
    } else if (typeof bounds === 'object' && bounds.isValid && bounds.isValid()) {
      params = makeParamsFromLeaflet(params, bounds);
    } else if (L.Util.isArray(bounds)) {
      var latLngBounds = L.latLngBounds(bounds);
      if (latLngBounds.isValid && latLngBounds.isValid()) {
        params = makeParamsFromLeaflet(params, latLngBounds);
      }
    }

    function makeParamsFromLeaflet (params, latLngBounds) {
      params['boundary.rect.min_lon'] = latLngBounds.getWest();
      params['boundary.rect.min_lat'] = latLngBounds.getSouth();
      params['boundary.rect.max_lon'] = latLngBounds.getEast();
      params['boundary.rect.max_lat'] = latLngBounds.getNorth();
      return params;
    }

    return params;
  },

  getFocusParam: function (params) {
    /**
     * this.options.focus can be one of the following
     * [50, 30]           // Array
     * {lon: 30, lat: 50} // Object
     * {lat: 50, lng: 30} // Object
     * L.latLng(50, 30)   // Object
     * true               // Boolean - take the map center
     * false              // Boolean - No latlng to be considered
     */
    var focus = this.options.focus;

    if (!focus) {
      return params;
    }

    if (focus === true && this._map) {
      // If focus option is Boolean true, use current map center
      var mapCenter = this._map.getCenter();
      params['focus.point.lat'] = mapCenter.lat;
      params['focus.point.lon'] = mapCenter.lng;
    } else if (typeof focus === 'object') {
      // Accepts array, object and L.latLng form
      // Constructs the latlng object using Leaflet's L.latLng()
      // [50, 30]
      // {lon: 30, lat: 50}
      // {lat: 50, lng: 30}
      // L.latLng(50, 30)
      var latlng = L.latLng(focus);
      params['focus.point.lat'] = latlng.lat;
      params['focus.point.lon'] = latlng.lng;
    }

    return params;
  },

  // @method getParams(params: Object)
  // Collects all the parameters in a single object from various options,
  // including options.bounds, options.focus, options.layers, the api key,
  // and any params that are provided as a argument to this function.
  // Note that options.params will overwrite any of these
  getParams: function (params) {
    params = params || {};
    params = this.getBoundingBoxParam(params);
    params = this.getFocusParam(params);
    params = this.getLayers(params);

    // Search API key
    if (this.apiKey) {
      params.api_key = this.apiKey;
    }

    var newParams = this.options.params;

    if (!newParams) {
      return params;
    }

    if (typeof newParams === 'object') {
      for (var prop in newParams) {
        params[prop] = newParams[prop];
      }
    }

    return params;
  },

  serialize: function (params) {
    var data = '';

    for (var key in params) {
      if (params.hasOwnProperty(key)) {
        var param = params[key];
        var type = param.toString();
        var value;

        if (data.length) {
          data += '&';
        }

        switch (type) {
          case '[object Array]':
            value = (param[0].toString() === '[object Object]') ? JSON.stringify(param) : param.join(',');
            break;
          case '[object Object]':
            value = JSON.stringify(param);
            break;
          case '[object Date]':
            value = param.valueOf();
            break;
          default:
            value = param;
            break;
        }

        data += encodeURIComponent(key) + '=' + encodeURIComponent(value);
      }
    }

    return data;
  },

  search: function (input) {
    // Prevent lack of input from sending a malformed query to Pelias
    if (!input) return;

    var url = this.options.url + '/search';
    var params = {
      text: input
    };

    this.callPelias(url, params, 'search');
  },

  autocomplete: throttle(function (input) {
    // Prevent lack of input from sending a malformed query to Pelias
    if (!input) return;

    var url = this.options.url + '/autocomplete';
    var params = {
      text: input
    };

    this.callPelias(url, params, 'autocomplete');
  }, API_RATE_LIMIT),

  place: function (id) {
    // Prevent lack of input from sending a malformed query to Pelias
    if (!id) return;

    var url = this.options.url + '/place';
    var params = {
      ids: id
    };

    this.callPelias(url, params, 'place');
  },

  handlePlaceResponse: function (response) {
    // Placeholder for handling place response
  },

  // Timestamp of the last response which was successfully rendered to the UI.
  // The time represents when the request was *sent*, not when it was recieved.
  maxReqTimestampRendered: new Date().getTime(),

  callPelias: function (endpoint, params, type) {
    params = this.getParams(params);

    L.DomUtil.addClass(this._search, 'leaflet-pelias-loading');

    // Track when the request began
    var reqStartedAt = new Date().getTime();

    var paramString = this.serialize(params);
    var url = endpoint + '?' + paramString;
    var self = this; // IE8 cannot .bind(this) without a polyfill.
    function handleResponse (err, response) {
      L.DomUtil.removeClass(self._search, 'leaflet-pelias-loading');
      var results;

      try {
        results = JSON.parse(response.responseText);
      } catch (e) {
        err = {
          code: 500,
          message: 'Parse Error' // TODO: string
        };
      }

      if (err) {
        var errorMessage;
        switch (err.code) {
          // Error codes.
          // https://mapzen.com/documentation/search/http-status-codes/
          case 403:
            errorMessage = self.options.textStrings['ERROR_403'];
            break;
          case 404:
            errorMessage = self.options.textStrings['ERROR_404'];
            break;
          case 408:
            errorMessage = self.options.textStrings['ERROR_408'];
            break;
          case 429:
            errorMessage = self.options.textStrings['ERROR_429'];
            break;
          case 500:
            errorMessage = self.options.textStrings['ERROR_500'];
            break;
          case 502:
            errorMessage = self.options.textStrings['ERROR_502'];
            break;
          // Note the status code is 0 if CORS is not enabled on the error response
          default:
            errorMessage = self.options.textStrings['ERROR_DEFAULT'];
            break;
        }
        self.showMessage(errorMessage);
        self.fire('error', {
          results: results,
          endpoint: endpoint,
          requestType: type,
          params: params,
          errorCode: err.code,
          errorMessage: errorMessage
        });
      }

      // There might be an error message from the geocoding service itself
      if (results && results.geocoding && results.geocoding.errors) {
        errorMessage = results.geocoding.errors[0];
        self.showMessage(errorMessage);
        self.fire('error', {
          results: results,
          endpoint: endpoint,
          requestType: type,
          params: params,
          errorCode: err.code,
          errorMessage: errorMessage
        });
        return;
      }

      // Autocomplete and search responses
      if (results && results.features) {
        // Check if request is stale:
        // Only for autocomplete or search endpoints
        // Ignore requests if input is currently blank
        // Ignore requests that started before a request which has already
        // been successfully rendered on to the UI.
        if (type === 'autocomplete' || type === 'search') {
          if (self._input.value === '' || self.maxReqTimestampRendered >= reqStartedAt) {
            return;
          } else {
            // Record the timestamp of the request.
            self.maxReqTimestampRendered = reqStartedAt;
          }
        }

        // Placeholder: handle place response
        if (type === 'place') {
          self.handlePlaceResponse(results);
        }

        // Show results
        if (type === 'autocomplete' || type === 'search') {
          self.showResults(results.features, params.text);
        }

        // Fire event
        self.fire('results', {
          results: results,
          endpoint: endpoint,
          requestType: type,
          params: params
        });
      }
    }

    corslite(url, handleResponse, true);
  },

  highlight: function (text, focus) {
    var r = RegExp('(' + escapeRegExp(focus) + ')', 'gi');
    return text.replace(r, '<strong>$1</strong>');
  },

  getIconType: function (layer) {
    var pointIcon = this.options.pointIcon;
    var polygonIcon = this.options.polygonIcon;
    var classPrefix = 'leaflet-pelias-layer-icon-';

    if (layer.match('venue') || layer.match('address')) {
      if (pointIcon === true) {
        return {
          type: 'class',
          value: classPrefix + 'point'
        };
      } else if (pointIcon === false) {
        return false;
      } else {
        return {
          type: 'image',
          value: pointIcon
        };
      }
    } else {
      if (polygonIcon === true) {
        return {
          type: 'class',
          value: classPrefix + 'polygon'
        };
      } else if (polygonIcon === false) {
        return false;
      } else {
        return {
          type: 'image',
          value: polygonIcon
        };
      }
    }
  },

  showResults: function (features, input) {
    // Exit function if there are no features
    if (features.length === 0) {
      this.showMessage(this.options.textStrings['NO_RESULTS']);
      return;
    }

    var resultsContainer = this._results;

    // Reset and display results container
    resultsContainer.innerHTML = '';
    resultsContainer.style.display = 'block';
    // manage result box height
    resultsContainer.style.maxHeight = (this._map.getSize().y - resultsContainer.offsetTop - this._container.offsetTop - RESULTS_HEIGHT_MARGIN) + 'px';

    var list = L.DomUtil.create('ul', 'leaflet-pelias-list', resultsContainer);

    for (var i = 0, j = features.length; i < j; i++) {
      var feature = features[i];
      var resultItem = L.DomUtil.create('li', 'leaflet-pelias-result', list);

      resultItem.feature = feature;
      resultItem.layer = feature.properties.layer;

      // Deprecated
      // Use L.GeoJSON.coordsToLatLng(resultItem.feature.geometry.coordinates) instead
      // This returns a L.LatLng object that can be used throughout Leaflet
      resultItem.coords = feature.geometry.coordinates;

      var icon = this.getIconType(feature.properties.layer);
      if (icon) {
        // Point or polygon icon
        // May be a class or an image path
        var layerIconContainer = L.DomUtil.create('span', 'leaflet-pelias-layer-icon-container', resultItem);
        var layerIcon;

        if (icon.type === 'class') {
          layerIcon = L.DomUtil.create('div', 'leaflet-pelias-layer-icon ' + icon.value, layerIconContainer);
        } else {
          layerIcon = L.DomUtil.create('img', 'leaflet-pelias-layer-icon', layerIconContainer);
          layerIcon.src = icon.value;
        }

        layerIcon.title = 'layer: ' + feature.properties.layer;
      }

      resultItem.innerHTML += this.highlight(feature.properties.label, input);
    }
  },

  showMessage: function (text) {
    var resultsContainer = this._results;

    // Reset and display results container
    resultsContainer.innerHTML = '';
    resultsContainer.style.display = 'block';

    var messageEl = L.DomUtil.create('div', 'leaflet-pelias-message', resultsContainer);

    // Set text. This is the most cross-browser compatible method
    // and avoids the issues we have detecting either innerText vs textContent
    // (e.g. Firefox cannot detect textContent property on elements, but it's there)
    messageEl.appendChild(document.createTextNode(text));
  },

  removeMarkers: function () {
    if (this.options.markers) {
      for (var i = 0; i < this.markers.length; i++) {
        this._map.removeLayer(this.markers[i]);
      }
      this.markers = [];
    }
  },

  showMarker: function (text, latlng) {
    this._map.setView(latlng, this._map.getZoom() || 8);

    var markerOptions = (typeof this.options.markers === 'object') ? this.options.markers : {};

    if (this.options.markers) {
      var marker = new L.marker(latlng, markerOptions).bindPopup(text); // eslint-disable-line new-cap
      this._map.addLayer(marker);
      this.markers.push(marker);
      marker.openPopup();
    }
  },

  /**
   * Fits the map view to a given bounding box.
   * Mapzen Search / Pelias returns the 'bbox' property on 'feature'. It is
   * as an array of four numbers:
   *   [
   *     0: southwest longitude,
   *     1: southwest latitude,
   *     2: northeast longitude,
   *     3: northeast latitude
   *   ]
   * This method expects the array to be passed directly and it will be converted
   * to a boundary parameter for Leaflet's fitBounds().
   */
  fitBoundingBox: function (bbox) {
    this._map.fitBounds([
      [ bbox[1], bbox[0] ],
      [ bbox[3], bbox[2] ]
    ], {
      animate: true,
      maxZoom: 16
    });
  },

  setSelectedResult: function (selected, originalEvent) {
    var latlng = L.GeoJSON.coordsToLatLng(selected.feature.geometry.coordinates);
    this._input.value = selected.textContent || selected.innerText;
    var layer = selected.feature.properties.layer;
    // "point" layers (venue and address in Pelias) must always display markers
    if ((layer !== 'venue' && layer !== 'address') && selected.feature.bbox && !this.options.overrideBbox) {
      this.removeMarkers();
      this.fitBoundingBox(selected.feature.bbox);
    } else {
      this.removeMarkers();
      this.showMarker(selected.innerHTML, latlng);
    }
    this.fire('select', {
      originalEvent: originalEvent,
      latlng: latlng,
      feature: selected.feature
    });
    this.blur();

    // Not all features will be guaranteed to have `gid` property - interpolated
    // addresses, for example, cannot be retrieved with `/place` and so the `gid`
    // property for them may be dropped in the future.
    if (this.options.place && selected.feature.properties.gid) {
      this.place(selected.feature.properties.gid);
    }
  },

  /**
   * Convenience function for focusing on the input
   * A `focus` event is fired, but it is not fired here. An event listener
   * was added to the _input element to forward the native `focus` event.
   *
   * @public
   */
  focus: function () {
    // If not expanded, expand this first
    if (!L.DomUtil.hasClass(this._container, 'leaflet-pelias-expanded')) {
      this.expand();
    }
    this._input.focus();
  },

  /**
   * Removes focus from geocoder control
   * A `blur` event is fired, but it is not fired here. An event listener
   * was added on the _input element to forward the native `blur` event.
   *
   * @public
   */
  blur: function () {
    this._input.blur();
    this.clearResults();
    if (this._input.value === '' && this._results.style.display !== 'none') {
      L.DomUtil.addClass(this._reset, 'leaflet-pelias-hidden');
      if (!this.options.expanded) {
        this.collapse();
      }
    }
  },

  clearResults: function (force) {
    // Hide results from view
    this._results.style.display = 'none';

    // Destroy contents if input has also cleared
    // OR if force is true
    if (this._input.value === '' || force === true) {
      this._results.innerHTML = '';
    }

    // Turn on scrollWheelZoom, if disabled. (`mouseout` does not fire on
    // the results list when it's closed in this way.)
    this._enableMapScrollWheelZoom();
  },

  expand: function () {
    L.DomUtil.addClass(this._container, 'leaflet-pelias-expanded');
    this.setFullWidth();
    this.fire('expand');
  },

  collapse: function () {
    // 'expanded' options check happens outside of this function now
    // So it's now possible for a script to force-collapse a geocoder
    // that otherwise defaults to the always-expanded state
    L.DomUtil.removeClass(this._container, 'leaflet-pelias-expanded');
    this._input.blur();
    this.clearFullWidth();
    this.clearResults();
    this.fire('collapse');
  },

  // Set full width of expanded input, if enabled
  setFullWidth: function () {
    if (this.options.fullWidth) {
      // If fullWidth setting is a number, only expand if map container
      // is smaller than that breakpoint. Otherwise, clear width
      // Always ask map to invalidate and recalculate size first
      this._map.invalidateSize();
      var mapWidth = this._map.getSize().x;
      var touchAdjustment = L.Browser.touch ? FULL_WIDTH_TOUCH_ADJUSTED_MARGIN : 0;
      var width = mapWidth - FULL_WIDTH_MARGIN - touchAdjustment;
      if (typeof this.options.fullWidth === 'number' && mapWidth >= window.parseInt(this.options.fullWidth, 10)) {
        this.clearFullWidth();
        return;
      }
      this._container.style.width = width.toString() + 'px';
    }
  },

  clearFullWidth: function () {
    // Clear set width, if any
    if (this.options.fullWidth) {
      this._container.style.width = '';
    }
  },

  onAdd: function (map) {
    var container = L.DomUtil.create('div',
        'leaflet-pelias-control leaflet-bar leaflet-control');

    this._body = document.body || document.getElementsByTagName('body')[0];
    this._container = container;
    this._input = L.DomUtil.create('input', 'leaflet-pelias-input', this._container);
    this._input.spellcheck = false;

    // Forwards focus and blur events from input to geocoder
    L.DomEvent.addListener(this._input, 'focus', function (e) {
      this.fire('focus', { originalEvent: e });
    }, this);

    L.DomEvent.addListener(this._input, 'blur', function (e) {
      this.fire('blur', { originalEvent: e });
    }, this);

    // Only set if title option is not null or falsy
    if (this.options.textStrings['INPUT_TITLE_ATTRIBUTE']) {
      this._input.title = this.options.textStrings['INPUT_TITLE_ATTRIBUTE'];
    }

    // Only set if placeholder option is not null or falsy
    if (this.options.textStrings['INPUT_PLACEHOLDER']) {
      this._input.placeholder = this.options.textStrings['INPUT_PLACEHOLDER'];
    }

    this._search = L.DomUtil.create('a', 'leaflet-pelias-search-icon', this._container);
    this._reset = L.DomUtil.create('div', 'leaflet-pelias-close leaflet-pelias-hidden', this._container);
    this._reset.innerHTML = 'Ã—';
    this._reset.title = this.options.textStrings['RESET_TITLE_ATTRIBUTE'];

    this._results = L.DomUtil.create('div', 'leaflet-pelias-results leaflet-bar', this._container);

    if (this.options.expanded) {
      this.expand();
    }

    L.DomEvent
      .on(this._container, 'click', function (e) {
        // Child elements with 'click' listeners should call
        // stopPropagation() to prevent that event from bubbling to
        // the container & causing it to fire too greedily
        this._input.focus();
      }, this)
      .on(this._input, 'focus', function (e) {
        if (this._input.value && this._results.children.length) {
          this._results.style.display = 'block';
        }
      }, this)
      .on(this._map, 'click', function (e) {
        // Does what you might expect a _input.blur() listener might do,
        // but since that would fire for any reason (e.g. clicking a result)
        // what you really want is to blur from the control by listening to clicks on the map
        this.blur();
      }, this)
      .on(this._search, 'click', function (e) {
        L.DomEvent.stopPropagation(e);

        // Toggles expanded state of container on click of search icon
        if (L.DomUtil.hasClass(this._container, 'leaflet-pelias-expanded')) {
          // If expanded option is true, just focus the input
          if (this.options.expanded === true) {
            this._input.focus();
          } else {
            // Otherwise, toggle to hidden state
            L.DomUtil.addClass(this._reset, 'leaflet-pelias-hidden');
            this.collapse();
          }
        } else {
          // If not currently expanded, clicking here always expands it
          if (this._input.value.length > 0) {
            L.DomUtil.removeClass(this._reset, 'leaflet-pelias-hidden');
          }
          this.expand();
          this._input.focus();
        }
      }, this)
      .on(this._reset, 'click', function (e) {
        this.reset();
        this._input.focus();
        L.DomEvent.stopPropagation(e);
      }, this)
      .on(this._input, 'keydown', function (e) {
        var list = this._results.querySelectorAll('.leaflet-pelias-result');
        var selected = this._results.querySelectorAll('.leaflet-pelias-selected')[0];
        var selectedPosition;
        var self = this;

        var panToPoint = function (selected, options) {
          if (selected && options.panToPoint) {
            var layer = selected.feature.properties.layer;
            // "point" layers (venue and address in Pelias) must always display markers
            if ((layer !== 'venue' && layer !== 'address') && selected.feature.bbox && !options.overrideBbox) {
              self.removeMarkers();
              self.fitBoundingBox(selected.feature.bbox);
            } else {
              self.removeMarkers();
              self.showMarker(selected.innerHTML, L.GeoJSON.coordsToLatLng(selected.feature.geometry.coordinates));
            }
          }
        };

        var scrollSelectedResultIntoView = function (selected) {
          var selectedRect = selected.getBoundingClientRect();
          var resultsRect = self._results.getBoundingClientRect();
          // Is the selected element not visible?
          if (selectedRect.bottom > resultsRect.bottom) {
            self._results.scrollTop = selected.offsetTop + selected.offsetHeight - self._results.offsetHeight;
          } else if (selectedRect.top < resultsRect.top) {
            self._results.scrollTop = selected.offsetTop;
          }
        };

        for (var i = 0; i < list.length; i++) {
          if (list[i] === selected) {
            selectedPosition = i;
            break;
          }
        }

        // TODO cleanup
        switch (e.keyCode) {
          // 13 = enter
          case 13:
            if (selected) {
              this.setSelectedResult(selected, e);
            } else {
              // perform a full text search on enter
              var text = (e.target || e.srcElement).value;
              this.search(text);
            }
            L.DomEvent.preventDefault(e);
            break;
          // 38 = up arrow
          case 38:
            // Ignore key if there are no results or if list is not visible
            if (list.length === 0 || this._results.style.display === 'none') {
              return;
            }

            if (selected) {
              L.DomUtil.removeClass(selected, 'leaflet-pelias-selected');
            }

            var previousItem = list[selectedPosition - 1];
            var highlighted = (selected && previousItem) ? previousItem : list[list.length - 1]; // eslint-disable-line no-redeclare

            L.DomUtil.addClass(highlighted, 'leaflet-pelias-selected');
            scrollSelectedResultIntoView(highlighted);
            panToPoint(highlighted, this.options);
            this._input.value = highlighted.textContent || highlighted.innerText;
            this.fire('highlight', {
              originalEvent: e,
              latlng: L.GeoJSON.coordsToLatLng(highlighted.feature.geometry.coordinates),
              feature: highlighted.feature
            });

            L.DomEvent.preventDefault(e);
            break;
          // 40 = down arrow
          case 40:
            // Ignore key if there are no results or if list is not visible
            if (list.length === 0 || this._results.style.display === 'none') {
              return;
            }

            if (selected) {
              L.DomUtil.removeClass(selected, 'leaflet-pelias-selected');
            }

            var nextItem = list[selectedPosition + 1];
            var highlighted = (selected && nextItem) ? nextItem : list[0]; // eslint-disable-line no-redeclare

            L.DomUtil.addClass(highlighted, 'leaflet-pelias-selected');
            scrollSelectedResultIntoView(highlighted);
            panToPoint(highlighted, this.options);
            this._input.value = highlighted.textContent || highlighted.innerText;
            this.fire('highlight', {
              originalEvent: e,
              latlng: L.GeoJSON.coordsToLatLng(highlighted.feature.geometry.coordinates),
              feature: highlighted.feature
            });

            L.DomEvent.preventDefault(e);
            break;
          // all other keys
          default:
            break;
        }
      }, this)
      .on(this._input, 'keyup', function (e) {
        var key = e.which || e.keyCode;
        var text = (e.target || e.srcElement).value;

        if (text.length > 0) {
          L.DomUtil.removeClass(this._reset, 'leaflet-pelias-hidden');
        } else {
          L.DomUtil.addClass(this._reset, 'leaflet-pelias-hidden');
        }

        // Ignore all further action if the keycode matches an arrow
        // key (handled via keydown event)
        if (key === 13 || key === 38 || key === 40) {
          return;
        }

        // keyCode 27 = esc key (esc should clear results)
        if (key === 27) {
          // If input is blank or results have already been cleared
          // (perhaps due to a previous 'esc') then pressing esc at
          // this point will blur from input as well.
          if (text.length === 0 || this._results.style.display === 'none') {
            this._input.blur();

            if (!this.options.expanded && L.DomUtil.hasClass(this._container, 'leaflet-pelias-expanded')) {
              this.collapse();
            }
          }

          // Clears results
          this.clearResults(true);
          L.DomUtil.removeClass(this._search, 'leaflet-pelias-loading');
          return;
        }

        if (text !== this._lastValue) {
          this._lastValue = text;

          if (text.length >= MINIMUM_INPUT_LENGTH_FOR_AUTOCOMPLETE && this.options.autocomplete === true) {
            this.autocomplete(text);
          } else {
            this.clearResults(true);
          }
        }
      }, this)
      .on(this._results, 'click', function (e) {
        L.DomEvent.preventDefault(e);
        L.DomEvent.stopPropagation(e);

        var _selected = this._results.querySelectorAll('.leaflet-pelias-selected')[0];
        if (_selected) {
          L.DomUtil.removeClass(_selected, 'leaflet-pelias-selected');
        }

        var selected = e.target || e.srcElement; /* IE8 */
        var findParent = function () {
          if (!L.DomUtil.hasClass(selected, 'leaflet-pelias-result')) {
            selected = selected.parentElement;
            if (selected) {
              findParent();
            }
          }
          return selected;
        };

        // click event can be registered on the child nodes
        // that does not have the required coords prop
        // so its important to find the parent.
        findParent();

        // If nothing is selected, (e.g. it's a message, not a result),
        // do nothing.
        if (selected) {
          L.DomUtil.addClass(selected, 'leaflet-pelias-selected');
          this.setSelectedResult(selected, e);
        }
      }, this);

    // Recalculate width of the input bar when window resizes
    if (this.options.fullWidth) {
      L.DomEvent.on(window, 'resize', function (e) {
        if (L.DomUtil.hasClass(this._container, 'leaflet-pelias-expanded')) {
          this.setFullWidth();
        }
      }, this);
    }

    L.DomEvent.on(this._results, 'mouseover', this._disableMapScrollWheelZoom, this);
    L.DomEvent.on(this._results, 'mouseout', this._enableMapScrollWheelZoom, this);
    L.DomEvent.on(this._map, 'mousedown', this._onMapInteraction, this);
    L.DomEvent.on(this._map, 'touchstart', this._onMapInteraction, this);

    L.DomEvent.disableClickPropagation(this._container);
    if (map.attributionControl) {
      map.attributionControl.addAttribution(this.options.attribution);
    }
    return container;
  },

  _onMapInteraction: function (event) {
    this.blur();

    // Only collapse if the input is clear, and is currently expanded.
    // Disabled if expanded is set to true
    if (!this.options.expanded) {
      if (!this._input.value && L.DomUtil.hasClass(this._container, 'leaflet-pelias-expanded')) {
        this.collapse();
      }
    }
  },

  _disableMapScrollWheelZoom: function (event) {
    // Prevent scrolling over results list from zooming the map, if enabled
    this._scrollWheelZoomEnabled = this._map.scrollWheelZoom.enabled();
    if (this._scrollWheelZoomEnabled) {
      this._map.scrollWheelZoom.disable();
    }
  },

  _enableMapScrollWheelZoom: function (event) {
    // Re-enable scroll wheel zoom (if previously enabled) after
    // leaving the results box
    if (this._scrollWheelZoomEnabled) {
      this._map.scrollWheelZoom.enable();
    }
  },

  onRemove: function (map) {
    if (map.attributionControl) {
      map.attributionControl.removeAttribution(this.options.attribution);
    }
  }
});

module.exports = Geocoder;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./utils/escapeRegExp":7,"./utils/throttle":8,"@mapbox/corslite":4,"console-polyfill":5}],7:[function(require,module,exports){
/*
 * escaping a string for regex Utility function
 * from https://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
 */
function escapeRegExp (str) {
  return str.replace(/[-[\]/{}()*+?.\\^$|]/g, '\\$&');
}

module.exports = escapeRegExp;

},{}],8:[function(require,module,exports){
/*
 * throttle Utility function (borrowed from underscore)
 */
function throttle (func, wait, options) {
  var context, args, result;
  var timeout = null;
  var previous = 0;
  if (!options) options = {};
  var later = function () {
    previous = options.leading === false ? 0 : new Date().getTime();
    timeout = null;
    result = func.apply(context, args);
    if (!timeout) context = args = null;
  };
  return function () {
    var now = new Date().getTime();
    if (!previous && options.leading === false) previous = now;
    var remaining = wait - (now - previous);
    context = this;
    args = arguments;
    if (remaining <= 0 || remaining > wait) {
      if (timeout) {
        clearTimeout(timeout);
        timeout = null;
      }
      previous = now;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    } else if (!timeout && options.trailing !== false) {
      timeout = setTimeout(later, remaining);
    }
    return result;
  };
}

module.exports = throttle;

},{}],9:[function(require,module,exports){
module.exports = function(version, language, options) {
    // load instructions
    var instructions = require('./instructions').get(language);
    if (Object !== instructions.constructor) throw 'instructions must be object';
    if (!instructions[version]) { throw 'invalid version ' + version; }

    return {
        capitalizeFirstLetter: function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        ordinalize: function(number) {
            // Transform numbers to their translated ordinalized value
            return instructions[version].constants.ordinalize[number.toString()] || '';
        },
        directionFromDegree: function(degree) {
            // Transform degrees to their translated compass direction
            if (!degree && degree !== 0) {
                // step had no bearing_after degree, ignoring
                return '';
            } else if (degree >= 0 && degree <= 20) {
                return instructions[version].constants.direction.north;
            } else if (degree > 20 && degree < 70) {
                return instructions[version].constants.direction.northeast;
            } else if (degree >= 70 && degree < 110) {
                return instructions[version].constants.direction.east;
            } else if (degree >= 110 && degree <= 160) {
                return instructions[version].constants.direction.southeast;
            } else if (degree > 160 && degree <= 200) {
                return instructions[version].constants.direction.south;
            } else if (degree > 200 && degree < 250) {
                return instructions[version].constants.direction.southwest;
            } else if (degree >= 250 && degree <= 290) {
                return instructions[version].constants.direction.west;
            } else if (degree > 290 && degree < 340) {
                return instructions[version].constants.direction.northwest;
            } else if (degree >= 340 && degree <= 360) {
                return instructions[version].constants.direction.north;
            } else {
                throw new Error('Degree ' + degree + ' invalid');
            }
        },
        laneConfig: function(step) {
            // Reduce any lane combination down to a contracted lane diagram
            if (!step.intersections || !step.intersections[0].lanes) throw new Error('No lanes object');

            var config = [];
            var currentLaneValidity = null;

            step.intersections[0].lanes.forEach(function (lane) {
                if (currentLaneValidity === null || currentLaneValidity !== lane.valid) {
                    if (lane.valid) {
                        config.push('o');
                    } else {
                        config.push('x');
                    }
                    currentLaneValidity = lane.valid;
                }
            });

            return config.join('');
        },
        compile: function(step) {
            if (!step.maneuver) throw new Error('No step maneuver provided');

            var type = step.maneuver.type;
            var modifier = step.maneuver.modifier;
            var mode = step.mode;

            if (!type) { throw new Error('Missing step maneuver type'); }
            if (type !== 'depart' && type !== 'arrive' && !modifier) { throw new Error('Missing step maneuver modifier'); }

            if (!instructions[version][type]) {
                // Log for debugging
                console.log('Encountered unknown instruction type: ' + type); // eslint-disable-line no-console
                // OSRM specification assumes turn types can be added without
                // major version changes. Unknown types are to be treated as
                // type `turn` by clients
                type = 'turn';
            }

            // Use special instructions if available, otherwise `defaultinstruction`
            var instructionObject;
            if (instructions[version].modes[mode]) {
                instructionObject = instructions[version].modes[mode];
            } else if (instructions[version][type][modifier]) {
                instructionObject = instructions[version][type][modifier];
            } else {
                instructionObject = instructions[version][type].default;
            }

            // Special case handling
            var laneInstruction;
            switch (type) {
            case 'use lane':
                laneInstruction = instructions[version].constants.lanes[this.laneConfig(step)];

                if (!laneInstruction) {
                    // If the lane combination is not found, default to continue straight
                    instructionObject = instructions[version]['use lane'].no_lanes;
                }
                break;
            case 'rotary':
            case 'roundabout':
                if (step.rotary_name && step.maneuver.exit && instructionObject.name_exit) {
                    instructionObject = instructionObject.name_exit;
                } else if (step.rotary_name && instructionObject.name) {
                    instructionObject = instructionObject.name;
                } else if (step.maneuver.exit && instructionObject.exit) {
                    instructionObject = instructionObject.exit;
                } else {
                    instructionObject = instructionObject.default;
                }
                break;
            default:
                // NOOP, since no special logic for that type
            }

            // Decide way_name with special handling for name and ref
            var wayName;
            var name = step.name || '';
            var ref = (step.ref || '').split(';')[0];

            // Remove hacks from Mapbox Directions mixing ref into name
            if (name === step.ref) {
                // if both are the same we assume that there used to be an empty name, with the ref being filled in for it
                // we only need to retain the ref then
                name = '';
            }
            name = name.replace(' (' + step.ref + ')', '');

            if (name && ref && name !== ref) {
                wayName = name + ' (' + ref + ')';
            } else if (!name && ref) {
                wayName = ref;
            } else {
                wayName = name;
            }

            // Decide which instruction string to use
            // Destination takes precedence over name
            var instruction;
            if (step.destinations && instructionObject.destination) {
                instruction = instructionObject.destination;
            } else if (wayName && instructionObject.name) {
                instruction = instructionObject.name;
            } else {
                instruction = instructionObject.default;
            }

            var tokenizedInstructionHook = ((options || {}).hooks || {}).tokenizedInstruction;
            if (tokenizedInstructionHook) {
                instruction = tokenizedInstructionHook(instruction);
            }

            // Replace tokens
            // NOOP if they don't exist
            var nthWaypoint = ''; // TODO, add correct waypoint counting
            instruction = instruction
                .replace('{way_name}', wayName)
                .replace('{destination}', (step.destinations || '').split(',')[0])
                .replace('{exit_number}', this.ordinalize(step.maneuver.exit || 1))
                .replace('{rotary_name}', step.rotary_name)
                .replace('{lane_instruction}', laneInstruction)
                .replace('{modifier}', instructions[version].constants.modifier[modifier])
                .replace('{direction}', this.directionFromDegree(step.maneuver.bearing_after))
                .replace('{nth}', nthWaypoint)
                .replace(/ {2}/g, ' '); // remove excess spaces

            if (instructions.meta.capitalizeFirstLetter) {
                instruction = this.capitalizeFirstLetter(instruction);
            }

            return instruction;
        }
    };
};

},{"./instructions":10}],10:[function(require,module,exports){
var instructionsDe = require('./instructions/de.json');
var instructionsEn = require('./instructions/en.json');
var instructionsFr = require('./instructions/fr.json');
var instructionsNl = require('./instructions/nl.json');
var instructionsZhHans = require('./instructions/zh-Hans.json');

module.exports = {
    get: function(language) {
        switch (language) {
        case 'en':
            return instructionsEn;
        case 'de':
            return instructionsDe;
        case 'fr':
            return instructionsFr;
        case 'nl':
            return instructionsNl;
        case 'zh':
        case 'zh-Hans':
            return instructionsZhHans;
        default:
            throw 'invalid language ' + language;
        }
    }
};

},{"./instructions/de.json":11,"./instructions/en.json":12,"./instructions/fr.json":13,"./instructions/nl.json":14,"./instructions/zh-Hans.json":15}],11:[function(require,module,exports){
module.exports={
    "meta": {
        "capitalizeFirstLetter": true
    },
    "v5": {
        "constants": {
            "ordinalize": {
                "1": "erste",
                "2": "zweite",
                "3": "dritte",
                "4": "vierte",
                "5": "fÃ¼nfte",
                "6": "sechste",
                "7": "siebente",
                "8": "achte",
                "9": "neunte",
                "10": "zehnte"
            },
            "direction": {
                "north": "Norden",
                "northeast": "Nordosten",
                "east": "Osten",
                "southeast": "SÃ¼dosten",
                "south": "SÃ¼den",
                "southwest": "SÃ¼dwesten",
                "west": "Westen",
                "northwest": "Nordwesten"
            },
            "modifier": {
                "left": "links",
                "right": "rechts",
                "sharp left": "scharf links",
                "sharp right": "scharf rechts",
                "slight left": "leicht links",
                "slight right": "leicht rechts",
                "straight": "geradeaus",
                "uturn": "180Â°-Wendung"
            },
            "lanes": {
                "xo": "Rechts halten",
                "ox": "Links halten",
                "xox": "Mittlere Spur nutzen",
                "oxo": "Rechts oder links halten"
            }
        },
        "modes": {
            "ferry": {
                "default": "FÃ¤hre nehmen",
                "name": "FÃ¤hre nehmen {way_name}",
                "destination": "FÃ¤hre nehmen Richtung {destination}"
            }
        },
        "arrive": {
            "default": {
                "default": "Sie haben Ihr {nth} Ziel erreicht"
            },
            "left": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich links von Ihnen"
            },
            "right": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich rechts von Ihnen"
            },
            "sharp left": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich links von Ihnen"
            },
            "sharp right": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich rechts von Ihnen"
            },
            "slight right": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich rechts von Ihnen"
            },
            "slight left": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich links von Ihnen"
            },
            "straight": {
                "default": "Sie haben Ihr {nth} Ziel erreicht, es befindet sich direkt vor Ihnen"
            }
        },
        "continue": {
            "default": {
                "default": "{modifier} weiterfahren",
                "name": "{modifier} weiterfahren auf {way_name}",
                "destination": "{modifier} weiterfahren Richtung {destination}"
            },
            "slight left": {
                "default": "Leicht links weiter",
                "name": "Leicht links weiter auf {way_name}",
                "destination": "Leicht links weiter Richtung {destination}"
            },
            "slight right": {
                "default": "Leicht rechts weiter",
                "name": "Leicht rechts weiter auf {way_name}",
                "destination": "Leicht rechts weiter Richtung {destination}"
            },
            "uturn": {
                "default": "180Â°-Wendung",
                "name": "180Â°-Wendung auf {way_name}",
                "destination": "180Â°-Wendung Richtung {destination}"
            }
        },
        "depart": {
            "default": {
                "default": "Fahren Sie Richtung {direction}",
                "name": "Fahren Sie Richtung {direction} auf {way_name}"
            }
        },
        "end of road": {
            "default": {
                "default": "{modifier} abbiegen",
                "name": "{modifier} abbiegen auf {way_name}",
                "destination": "{modifier} abbiegen Richtung {destination}"
            },
            "straight": {
                "default": "Geradeaus weiterfahren",
                "name": "Geradeaus weiterfahren auf {way_name}",
                "destination": "Geradeaus weiterfahren Richtung {destination}"
            },
            "uturn": {
                "default": "180Â°-Wendung am Ende der StraÃŸe",
                "name": "180Â°-Wendung auf {way_name} am Ende der StraÃŸe",
                "destination": "180Â°-Wendung Richtung {destination} am Ende der StraÃŸe"
            }
        },
        "fork": {
            "default": {
                "default": "{modifier} halten an der Gabelung",
                "name": "{modifier} halten an der Gabelung auf {way_name}",
                "destination": "{modifier}  halten an der Gabelung Richtung {destination}"
            },
            "slight left": {
                "default": "Links halten an der Gabelung",
                "name": "Links halten an der Gabelung auf {way_name}",
                "destination": "Links halten an der Gabelung Richtung {destination}"
            },
            "slight right": {
                "default": "Rechts halten an der Gabelung",
                "name": "Rechts halten an der Gabelung auf {way_name}",
                "destination": "Rechts halten an der Gabelung Richtung {destination}"
            },
            "sharp left": {
                "default": "Scharf links abbiegen an der Gabelung",
                "name": "Scharf links abbiegen an der Gabelung auf {way_name}",
                "destination": "Scharf links abbiegen an der Gabelung Richtung {destination}"
            },
            "sharp right": {
                "default": "Scharf rechts abbiegen an der Gabelung",
                "name": "Scharf rechts abbiegen an der Gabelung auf {way_name}",
                "destination": "Scharf rechts abbiegen an der Gabelung Richtung {destination}"
            },
            "uturn": {
                "default": "180Â°-Wendung",
                "name": "180Â°-Wendung auf {way_name}",
                "destination": "180Â°-Wendung Richtung {destination}"
            }
        },
        "merge": {
            "default": {
                "default": "{modifier} auffahren",
                "name": "{modifier} auffahren auf {way_name}",
                "destination": "{modifier} auffahren Richtung {destination}"
            },
            "slight left": {
                "default": "Leicht links auffahren",
                "name": "Leicht links auffahren auf {way_name}",
                "destination": "Leicht links auffahren Richtung {destination}"
            },
            "slight right": {
                "default": "Leicht rechts auffahren",
                "name": "Leicht rechts auffahren auf {way_name}",
                "destination": "Leicht rechts auffahren Richtung {destination}"
            },
            "sharp left": {
                "default": "Scharf links auffahren",
                "name": "Scharf links auffahren auf {way_name}",
                "destination": "Scharf links auffahren Richtung {destination}"
            },
            "sharp right": {
                "default": "Scharf rechts auffahren",
                "name": "Scharf rechts auffahren auf {way_name}",
                "destination": "Scharf rechts auffahren Richtung {destination}"
            },
            "uturn": {
                "default": "180Â°-Wendung",
                "name": "180Â°-Wendung auf {way_name}",
                "destination": "180Â°-Wendung Richtung {destination}"
            }
        },
        "new name": {
            "default": {
                "default": "{modifier} weiterfahren",
                "name": "{modifier} weiterfahren auf {way_name}",
                "destination": "{modifier} weiterfahren Richtung {destination}"
            },
            "sharp left": {
                "default": "Scharf links",
                "name": "Scharf links auf {way_name}",
                "destination": "Scharf links Richtung {destination}"
            },
            "sharp right": {
                "default": "Scharf rechts",
                "name": "Scharf rechts auf {way_name}",
                "destination": "Scharf rechts Richtung {destination}"
            },
            "slight left": {
                "default": "Leicht links weiter",
                "name": "Leicht links weiter auf {way_name}",
                "destination": "Leicht links weiter Richtung {destination}"
            },
            "slight right": {
                "default": "Leicht rechts weiter",
                "name": "Leicht rechts weiter auf {way_name}",
                "destination": "Leicht rechts weiter Richtung {destination}"
            },
            "uturn": {
                "default": "180Â°-Wendung",
                "name": "180Â°-Wendung auf {way_name}",
                "destination": "180Â°-Wendung Richtung {destination}"
            }
        },
        "notification": {
            "default": {
                "default": "{modifier} weiterfahren",
                "name": "{modifier} weiterfahren auf {way_name}",
                "destination" : "{modifier} weiterfahren Richtung {destination}"
            },
            "uturn": {
                "default": "180Â°-Wendung",
                "name": "180Â°-Wendung auf {way_name}",
                "destination": "180Â°-Wendung Richtung {destination}"
            }
        },
        "off ramp": {
            "default": {
                "default": "Rampe nehmen",
                "name": "Rampe nehmen auf {way_name}",
                "destination": "Rampe nehmen Richtung {destination}"
            },
            "left": {
                "default": "Rampe auf der linken Seite nehmen",
                "name": "Rampe auf der linken Seite nehmen auf {way_name}",
                "destination": "Rampe auf der linken Seite nehmen Richtung {destination}"
            },
            "right": {
                "default": "Rampe auf der rechten Seite nehmen",
                "name": "Rampe auf der rechten Seite nehmen auf {way_name}",
                "destination": "Rampe auf der rechten Seite nehmen Richtung {destination}"
            },
            "sharp left": {
                "default": "Rampe auf der linken Seite nehmen",
                "name": "Rampe auf der linken Seite nehmen auf {way_name}",
                "destination": "Rampe auf der linken Seite nehmen Richtung {destination}"
            },
            "sharp right": {
                "default": "Rampe auf der rechten Seite nehmen",
                "name": "Rampe auf der rechten Seite nehmen auf {way_name}",
                "destination": "Rampe auf der rechten Seite nehmen Richtung {destination}"
            },
            "slight left": {
                "default": "Rampe auf der linken Seite nehmen",
                "name": "Rampe auf der linken Seite nehmen auf {way_name}",
                "destination": "Rampe auf der linken Seite nehmen Richtung {destination}"
            },
            "slight right": {
                "default": "Rampe auf der rechten Seite nehmen",
                "name": "Rampe auf der rechten Seite nehmen auf {way_name}",
                "destination": "Rampe auf der rechten Seite nehmen Richtung {destination}"
            }
        },
        "on ramp": {
            "default": {
                "default": "Rampe nehmen",
                "name": "Rampe nehmen auf {way_name}",
                "destination": "Rampe nehmen Richtung {destination}"
            },
            "left": {
                "default": "Rampe auf der linken Seite nehmen",
                "name": "Rampe auf der linken Seite nehmen auf {way_name}",
                "destination": "Rampe auf der linken Seite nehmen Richtung {destination}"
            },
            "right": {
                "default": "Rampe auf der rechten Seite nehmen",
                "name": "Rampe auf der rechten Seite nehmen auf {way_name}",
                "destination": "Rampe auf der rechten Seite nehmen Richtung {destination}"
            },
            "sharp left": {
                "default": "Rampe auf der linken Seite nehmen",
                "name": "Rampe auf der linken Seite nehmen auf {way_name}",
                "destination": "Rampe auf der linken Seite nehmen Richtung {destination}"
            },
            "sharp right": {
                "default": "Rampe auf der rechten Seite nehmen",
                "name": "Rampe auf der rechten Seite nehmen auf {way_name}",
                "destination": "Rampe auf der rechten Seite nehmen Richtung {destination}"
            },
            "slight left": {
                "default": "Rampe auf der linken Seite nehmen",
                "name": "Rampe auf der linken Seite nehmen auf {way_name}",
                "destination": "Rampe auf der linken Seite nehmen Richtung {destination}"
            },
            "slight right": {
                "default": "Rampe auf der rechten Seite nehmen",
                "name": "Rampe auf der rechten Seite nehmen auf {way_name}",
                "destination": "Rampe auf der rechten Seite nehmen Richtung {destination}"
            }
        },
        "rotary": {
            "default": {
                "default": {
                    "default": "In den Kreisverkehr fahren",
                    "name": "In den Kreisverkehr fahren und auf {way_name} verlassen",
                    "destination": "In den Kreisverkehr fahren und Richtung {destination} verlassen"
                },
                "name": {
                    "default": "In {rotary_name} fahren",
                    "name": "In {rotary_name} fahren und auf {way_name} verlassen",
                    "destination": "In {rotary_name} fahren und Richtung {destination} verlassen"
                },
                "exit": {
                    "default": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen",
                    "name": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen auf {way_name}",
                    "destination": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen Richtung {destination}"
                },
                "name_exit": {
                    "default": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen",
                    "name": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen auf {way_name}",
                    "destination": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen Richtung {destination}"
                }
            }
        },
        "roundabout": {
            "default": {
                "exit": {
                    "default": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen",
                    "name": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen auf {way_name}",
                    "destination": "In den Kreisverkehr fahren und {exit_number} Ausfahrt nehmen Richtung {destination}"
                },
                "default": {
                    "default": "In den Kreisverkehr fahren",
                    "name": "In den Kreisverkehr fahren und auf {way_name} verlassen",
                    "destination": "In den Kreisverkehr fahren und Richtung {destination} verlassen"
                }
            }
        },
        "roundabout turn": {
            "default": {
                "default": "Am Kreisverkehr {modifier}",
                "name": "Am Kreisverkehr {modifier} auf {way_name}",
                "destination": "Am Kreisverkehr {modifier} Richtung {destination}"
            },
            "left": {
                "default": "Am Kreisverkehr links",
                "name": "Am Kreisverkehr links auf {way_name}",
                "destination": "Am Kreisverkehr links Richtung {destination}"
            },
            "right": {
                "default": "Am Kreisverkehr rechts",
                "name": "Am Kreisverkehr rechts auf {way_name}",
                "destination": "Am Kreisverkehr rechts Richtung {destination}"
            },
            "straight": {
                "default": "Am Kreisverkehr geradeaus weiterfahren",
                "name": "Am Kreisverkehr geradeaus weiterfahren auf {way_name}",
                "destination": "Am Kreisverkehr geradeaus weiterfahren Richtung {destination}"
            }
        },
        "turn": {
            "default": {
                "default": "{modifier} abbiegen",
                "name": "{modifier} abbiegen auf {way_name}",
                "destination": "{modifier} abbiegen Richtung {destination}"
            },
            "left": {
                "default": "Links abbiegen",
                "name": "Links abbiegen auf {way_name}",
                "destination": "Links abbiegen Richtung {destination}"
            },
            "right": {
                "default": "Rechts abbiegen",
                "name": "Rechts abbiegen auf {way_name}",
                "destination": "Rechts abbiegen Richtung {destination}"
            },
            "straight": {
                "default": "Geradeaus weiterfahren",
                "name": "Geradeaus weiterfahren auf {way_name}",
                "destination": "Geradeaus weiterfahren Richtung {destination}"
            }
        },
        "use lane": {
            "no_lanes": {
                "default": "Geradeaus weiterfahren"
            },
            "default": {
                "default": "{lane_instruction}"
            }
        }
    }
}

},{}],12:[function(require,module,exports){
module.exports={
    "meta": {
        "capitalizeFirstLetter": true
    },
    "v5": {
        "constants": {
            "ordinalize": {
                "1": "1st",
                "2": "2nd",
                "3": "3rd",
                "4": "4th",
                "5": "5th",
                "6": "6th",
                "7": "7th",
                "8": "8th",
                "9": "9th",
                "10": "10th"
            },
            "direction": {
                "north": "north",
                "northeast": "northeast",
                "east": "east",
                "southeast": "southeast",
                "south": "south",
                "southwest": "southwest",
                "west": "west",
                "northwest": "northwest"
            },
            "modifier": {
                "left": "left",
                "right": "right",
                "sharp left": "sharp left",
                "sharp right": "sharp right",
                "slight left": "slight left",
                "slight right": "slight right",
                "straight": "straight",
                "uturn": "U-turn"
            },
            "lanes": {
                "xo": "Keep right",
                "ox": "Keep left",
                "xox": "Keep in the middle",
                "oxo": "Keep left or right"
            }
        },
        "modes": {
            "ferry": {
                "default": "Take the ferry",
                "name": "Take the ferry {way_name}",
                "destination": "Take the ferry towards {destination}"
            }
        },
        "arrive": {
            "default": {
                "default": "You have arrived at your {nth} destination"
            },
            "left": {
                "default": "You have arrived at your {nth} destination, on the left"
            },
            "right": {
                "default": "You have arrived at your {nth} destination, on the right"
            },
            "sharp left": {
                "default": "You have arrived at your {nth} destination, on the left"
            },
            "sharp right": {
                "default": "You have arrived at your {nth} destination, on the right"
            },
            "slight right": {
                "default": "You have arrived at your {nth} destination, on the right"
            },
            "slight left": {
                "default": "You have arrived at your {nth} destination, on the left"
            },
            "straight": {
                "default": "You have arrived at your {nth} destination, straight ahead"
            }
        },
        "continue": {
            "default": {
                "default": "Continue {modifier}",
                "name": "Continue {modifier} onto {way_name}",
                "destination": "Continue {modifier} towards {destination}"
            },
            "slight left": {
                "default": "Continue slightly left",
                "name": "Continue slightly left onto {way_name}",
                "destination": "Continue slightly left towards {destination}"
            },
            "slight right": {
                "default": "Continue slightly right",
                "name": "Continue slightly right onto {way_name}",
                "destination": "Continue slightly right towards {destination}"
            },
            "uturn": {
                "default": "Make a U-turn",
                "name": "Make a U-turn onto {way_name}",
                "destination": "Make a U-turn towards {destination}"
            }
        },
        "depart": {
            "default": {
                "default": "Head {direction}",
                "name": "Head {direction} on {way_name}"
            }
        },
        "end of road": {
            "default": {
                "default": "Turn {modifier}",
                "name": "Turn {modifier} onto {way_name}",
                "destination": "Turn {modifier} towards {destination}"
            },
            "straight": {
                "default": "Continue straight",
                "name": "Continue straight onto {way_name}",
                "destination": "Continue straight towards {destination}"
            },
            "uturn": {
                "default": "Make a U-turn at the end of the road",
                "name": "Make a U-turn onto {way_name} at the end of the road",
                "destination": "Make a U-turn towards {destination} at the end of the road"
            }
        },
        "fork": {
            "default": {
                "default": "Keep {modifier} at the fork",
                "name": "Keep {modifier} at the fork onto {way_name}",
                "destination": "Keep {modifier} at the fork towards {destination}"
            },
            "slight left": {
                "default": "Keep left at the fork",
                "name": "Keep left at the fork onto {way_name}",
                "destination": "Keep left at the fork towards {destination}"
            },
            "slight right": {
                "default": "Keep right at the fork",
                "name": "Keep right at the fork onto {way_name}",
                "destination": "Keep right at the fork towards {destination}"
            },
            "sharp left": {
                "default": "Take a sharp left at the fork",
                "name": "Take a sharp left at the fork onto {way_name}",
                "destination": "Take a sharp left at the fork towards {destination}"
            },
            "sharp right": {
                "default": "Take a sharp right at the fork",
                "name": "Take a sharp right at the fork onto {way_name}",
                "destination": "Take a sharp right at the fork towards {destination}"
            },
            "uturn": {
                "default": "Make a U-turn",
                "name": "Make a U-turn onto {way_name}",
                "destination": "Make a U-turn towards {destination}"
            }
        },
        "merge": {
            "default": {
                "default": "Merge {modifier}",
                "name": "Merge {modifier} onto {way_name}",
                "destination": "Merge {modifier} towards {destination}"
            },
            "slight left": {
                "default": "Merge left",
                "name": "Merge left onto {way_name}",
                "destination": "Merge left towards {destination}"
            },
            "slight right": {
                "default": "Merge right",
                "name": "Merge right onto {way_name}",
                "destination": "Merge right towards {destination}"
            },
            "sharp left": {
                "default": "Merge left",
                "name": "Merge left onto {way_name}",
                "destination": "Merge left towards {destination}"
            },
            "sharp right": {
                "default": "Merge right",
                "name": "Merge right onto {way_name}",
                "destination": "Merge right towards {destination}"
            },
            "uturn": {
                "default": "Make a U-turn",
                "name": "Make a U-turn onto {way_name}",
                "destination": "Make a U-turn towards {destination}"
            }
        },
        "new name": {
            "default": {
                "default": "Continue {modifier}",
                "name": "Continue {modifier} onto {way_name}",
                "destination": "Continue {modifier} towards {destination}"
            },
            "sharp left": {
                "default": "Take a sharp left",
                "name": "Take a sharp left onto {way_name}",
                "destination": "Take a sharp left towards {destination}"
            },
            "sharp right": {
                "default": "Take a sharp right",
                "name": "Take a sharp right onto {way_name}",
                "destination": "Take a sharp right towards {destination}"
            },
            "slight left": {
                "default": "Continue slightly left",
                "name": "Continue slightly left onto {way_name}",
                "destination": "Continue slightly left towards {destination}"
            },
            "slight right": {
                "default": "Continue slightly right",
                "name": "Continue slightly right onto {way_name}",
                "destination": "Continue slightly right towards {destination}"
            },
            "uturn": {
                "default": "Make a U-turn",
                "name": "Make a U-turn onto {way_name}",
                "destination": "Make a U-turn towards {destination}"
            }
        },
        "notification": {
            "default": {
                "default": "Continue {modifier}",
                "name": "Continue {modifier} onto {way_name}",
                "destination" : "Continue {modifier} towards {destination}"
            },
            "uturn": {
                "default": "Make a U-turn",
                "name": "Make a U-turn onto {way_name}",
                "destination": "Make a U-turn towards {destination}"
            }
        },
        "off ramp": {
            "default": {
                "default": "Take the ramp",
                "name": "Take the ramp onto {way_name}",
                "destination": "Take the ramp towards {destination}"
            },
            "left": {
                "default": "Take the ramp on the left",
                "name": "Take the ramp on the left onto {way_name}",
                "destination": "Take the ramp on the left towards {destination}"
            },
            "right": {
                "default": "Take the ramp on the right",
                "name": "Take the ramp on the right onto {way_name}",
                "destination": "Take the ramp on the right towards {destination}"
            },
            "sharp left": {
                "default": "Take the ramp on the left",
                "name": "Take the ramp on the left onto {way_name}",
                "destination": "Take the ramp on the left towards {destination}"
            },
            "sharp right": {
                "default": "Take the ramp on the right",
                "name": "Take the ramp on the right onto {way_name}",
                "destination": "Take the ramp on the right towards {destination}"
            },
            "slight left": {
                "default": "Take the ramp on the left",
                "name": "Take the ramp on the left onto {way_name}",
                "destination": "Take the ramp on the left towards {destination}"
            },
            "slight right": {
                "default": "Take the ramp on the right",
                "name": "Take the ramp on the right onto {way_name}",
                "destination": "Take the ramp on the right towards {destination}"
            }
        },
        "on ramp": {
            "default": {
                "default": "Take the ramp",
                "name": "Take the ramp onto {way_name}",
                "destination": "Take the ramp towards {destination}"
            },
            "left": {
                "default": "Take the ramp on the left",
                "name": "Take the ramp on the left onto {way_name}",
                "destination": "Take the ramp on the left towards {destination}"
            },
            "right": {
                "default": "Take the ramp on the right",
                "name": "Take the ramp on the right onto {way_name}",
                "destination": "Take the ramp on the right towards {destination}"
            },
            "sharp left": {
                "default": "Take the ramp on the left",
                "name": "Take the ramp on the left onto {way_name}",
                "destination": "Take the ramp on the left towards {destination}"
            },
            "sharp right": {
                "default": "Take the ramp on the right",
                "name": "Take the ramp on the right onto {way_name}",
                "destination": "Take the ramp on the right towards {destination}"
            },
            "slight left": {
                "default": "Take the ramp on the left",
                "name": "Take the ramp on the left onto {way_name}",
                "destination": "Take the ramp on the left towards {destination}"
            },
            "slight right": {
                "default": "Take the ramp on the right",
                "name": "Take the ramp on the right onto {way_name}",
                "destination": "Take the ramp on the right towards {destination}"
            }
        },
        "rotary": {
            "default": {
                "default": {
                    "default": "Enter the rotary",
                    "name": "Enter the rotary and exit onto {way_name}",
                    "destination": "Enter the rotary and exit towards {destination}"
                },
                "name": {
                    "default": "Enter {rotary_name}",
                    "name": "Enter {rotary_name} and exit onto {way_name}",
                    "destination": "Enter {rotary_name} and exit towards {destination}"
                },
                "exit": {
                    "default": "Enter the rotary and take the {exit_number} exit",
                    "name": "Enter the rotary and take the {exit_number} exit onto {way_name}",
                    "destination": "Enter the rotary and take the {exit_number} exit towards {destination}"
                },
                "name_exit": {
                    "default": "Enter {rotary_name} and take the {exit_number} exit",
                    "name": "Enter {rotary_name} and take the {exit_number} exit onto {way_name}",
                    "destination": "Enter {rotary_name} and take the {exit_number} exit towards {destination}"
                }
            }
        },
        "roundabout": {
            "default": {
                "exit": {
                    "default": "Enter the roundabout and take the {exit_number} exit",
                    "name": "Enter the roundabout and take the {exit_number} exit onto {way_name}",
                    "destination": "Enter the roundabout and take the {exit_number} exit towards {destination}"
                },
                "default": {
                    "default": "Enter the roundabout",
                    "name": "Enter the roundabout and exit onto {way_name}",
                    "destination": "Enter the roundabout and exit towards {destination}"
                }
            }
        },
        "roundabout turn": {
            "default": {
                "default": "At the roundabout make a {modifier}",
                "name": "At the roundabout make a {modifier} onto {way_name}",
                "destination": "At the roundabout make a {modifier} towards {destination}"
            },
            "left": {
                "default": "At the roundabout turn left",
                "name": "At the roundabout turn left onto {way_name}",
                "destination": "At the roundabout turn left towards {destination}"
            },
            "right": {
                "default": "At the roundabout turn right",
                "name": "At the roundabout turn right onto {way_name}",
                "destination": "At the roundabout turn right towards {destination}"
            },
            "straight": {
                "default": "At the roundabout continue straight",
                "name": "At the roundabout continue straight onto {way_name}",
                "destination": "At the roundabout continue straight towards {destination}"
            }
        },
        "turn": {
            "default": {
                "default": "Make a {modifier}",
                "name": "Make a {modifier} onto {way_name}",
                "destination": "Make a {modifier} towards {destination}"
            },
            "left": {
                "default": "Turn left",
                "name": "Turn left onto {way_name}",
                "destination": "Turn left towards {destination}"
            },
            "right": {
                "default": "Turn right",
                "name": "Turn right onto {way_name}",
                "destination": "Turn right towards {destination}"
            },
            "straight": {
                "default": "Go straight",
                "name": "Go straight onto {way_name}",
                "destination": "Go straight towards {destination}"
            }
        },
        "use lane": {
            "no_lanes": {
                "default": "Continue straight"
            },
            "default": {
                "default": "{lane_instruction}"
            }
        }
    }
}

},{}],13:[function(require,module,exports){
module.exports={
    "meta": {
        "capitalizeFirstLetter": true
    },
    "v5": {
        "constants": {
            "ordinalize": {
                "1": "premiÃ¨re",
                "2": "seconde",
                "3": "troisiÃ¨me",
                "4": "quatriÃ¨me",
                "5": "cinquiÃ¨me",
                "6": "sixiÃ¨me",
                "7": "setpiÃ¨me",
                "8": "huitiÃ¨me",
                "9": "neuviÃ¨me",
                "10": "dixiÃ¨me"
            },
            "direction": {
                "north": "le nord",
                "northeast": "le nord-est",
                "east": "l'est",
                "southeast": "le sud-est",
                "south": "le sud",
                "southwest": "le sud-ouest",
                "west": "l'ouest",
                "northwest": "le nord-ouest"
            },
            "modifier": {
                "left": "Ã  gauche",
                "right": "Ã  droite",
                "sharp left": "franchement Ã  gauche",
                "sharp right": "franchement Ã  droite",
                "slight left": "lÃ©gÃ¨rement Ã  gauche",
                "slight right": "lÃ©gÃ¨rement Ã  droite",
                "straight": "tout droit",
                "uturn": "demi-tour"
            },
            "lanes": {
                "xo": "Serrer Ã  droite",
                "ox": "Serrer Ã  gauche",
                "xox": "Rester au milieu",
                "oxo": "Rester Ã  gauche ou Ã  droite"
            }
        },
        "modes": {
            "ferry": {
                "default": "Prendre le ferry",
                "name": "Prendre le ferry {way_name}",
                "destination": "Prendre le ferry en direction de {destination}"
            }
        },
        "arrive": {
            "default": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination"
            },
            "left": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, sur la gauche"
            },
            "right": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, sur la droite"
            },
            "sharp left": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, sur la gauche"
            },
            "sharp right": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, sur la droite"
            },
            "slight right": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, sur la droite"
            },
            "slight left": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, sur la gauche"
            },
            "straight": {
                "default": "Vous Ãªtes arrivÃ©s Ã  votre {nth} destination, droit devant"
            }
        },
        "continue": {
            "default": {
                "default": "Continuer {modifier}",
                "name": "Continuer {modifier} sur {way_name}",
                "destination": "Continuer {modifier} en direction de {destination}"
            },
            "slight left": {
                "default": "Continuer lÃ©gÃ¨rement Ã  gauche",
                "name": "Continuer lÃ©gÃ¨rement Ã  gauche sur {way_name}",
                "destination": "Continuer lÃ©gÃ¨rement Ã  gauche en direction de {destination}"
            },
            "slight right": {
                "default": "Continuer lÃ©gÃ¨rement Ã  droite",
                "name": "Continuer lÃ©gÃ¨rement Ã  droite sur {way_name}",
                "destination": "Continuer lÃ©gÃ¨rement Ã  droite en direction de {destination}"
            },
            "uturn": {
                "default": "Faire demi-tour",
                "name": "Faire demi-tour sur {way_name}",
                "destination": "Faire demi-tour en direction de {destination}"
            }
        },
        "depart": {
            "default": {
                "default": "Rouler vers {direction}",
                "name": "Rouler vers {direction} sur {way_name}"
            }
        },
        "end of road": {
            "default": {
                "default": "Tourner {modifier}",
                "name": "Tourner {modifier} sur {way_name}",
                "destination": "Tourner {modifier} en direction de {destination}"
            },
            "straight": {
                "default": "Continuer tout droit",
                "name": "Continuer tout droit sur {way_name}",
                "destination": "Continuer tout droit en direction de {destination}"
            },
            "uturn": {
                "default": "Faire demi-tour Ã  la fin de la route",
                "name": "Faire demi-tour Ã  la fin de la route {way_name}",
                "destination": "Faire demi-tour Ã  la fin de la route en direction de {destination}"
            }
        },
        "fork": {
            "default": {
                "default": "Rester {modifier} Ã  l'embranchement",
                "name": "Rester {modifier} Ã  l'embranchement sur {way_name}",
                "destination": "Rester {modifier} Ã  l'embranchement en direction de {destination}"
            },
            "slight left": {
                "default": "Rester Ã  gauche Ã  l'embranchement",
                "name": "Rester Ã  gauche Ã  l'embranchement sur {way_name}",
                "destination": "Rester Ã  gauche Ã  l'embranchement en direction de {destination}"
            },
            "slight right": {
                "default": "Rester Ã  droite Ã  l'embranchement",
                "name": "Rester Ã  droite Ã  l'embranchement sur {way_name}",
                "destination": "Rester Ã  droite Ã  l'embranchement en direction de {destination}"
            },
            "sharp left": {
                "default": "Prendre Ã  gauche Ã  l'embranchement",
                "name": "Prendre Ã  gauche Ã  l'embranchement sur {way_name}",
                "destination": "Prendre Ã  gauche Ã  l'embranchement en direction de {destination}"
            },
            "sharp right": {
                "default": "Prendre Ã  droite Ã  l'embranchement",
                "name": "Prendre Ã  droite Ã  l'embranchement sur {way_name}",
                "destination": "Prendre Ã  droite Ã  l'embranchement en direction de {destination}"
            },
            "uturn": {
                "default": "Faire demi-tour",
                "name": "Faire demi-tour sur {way_name}",
                "destination": "Faire demi-tour en direction de {destination}"
            }
        },
        "merge": {
            "default": {
                "default": "Rejoindre {modifier}",
                "name": "Rejoindre {modifier} sur {way_name}",
                "destination": "Rejoindre {modifier} en direction de {destination}"
            },
            "slight left": {
                "default": "Rejoindre lÃ©gÃ¨rement par la gauche",
                "name": "Rejoindre {way_name} lÃ©gÃ¨rement par la gauche",
                "destination": "Rejoindre lÃ©gÃ¨rement par la gauche la route en direction de {destination}"
            },
            "slight right": {
                "default": "Rejoindre lÃ©gÃ¨rement par la droite",
                "name": "Rejoindre {way_name} lÃ©gÃ¨rement par la droite",
                "destination": "Rejoindre lÃ©gÃ¨rement par la droite la route en direction de {destination}"
            },
            "sharp left": {
                "default": "Rejoindre par la gauche",
                "name": "Rejoindre {way_name} par la gauche",
                "destination": "Rejoindre par la gauche la route en direction de {destination}"
            },
            "sharp right": {
                "default": "Rejoindre par la droite",
                "name": "Rejoindre {way_name} par la droite",
                "destination": "Rejoindre par la droite la route en direction de {destination}"
            },
            "uturn": {
                "default": "Fair demi-tour",
                "name": "Fair demi-tour sur {way_name}",
                "destination": "Fair demi-tour en direction de {destination}"
            }
        },
        "new name": {
            "default": {
                "default": "Continuer {modifier}",
                "name": "Continuer {modifier} sur {way_name}",
                "destination": "Continuer {modifier} en direction de {destination}"
            },
            "sharp left": {
                "default": "Prendre Ã  gauche",
                "name": "Prendre Ã  gauche sur {way_name}",
                "destination": "Prendre Ã  gauche en direction de {destination}"
            },
            "sharp right": {
                "default": "Prendre Ã  droite",
                "name": "Prendre Ã  droite sur {way_name}",
                "destination": "Prendre Ã  droite en direction de {destination}"
            },
            "slight left": {
                "default": "Continuer lÃ©gÃ¨rement Ã  gauche",
                "name": "Continuer lÃ©gÃ¨rement Ã  gauche sur {way_name}",
                "destination": "Continuer lÃ©gÃ¨rement Ã  gauche en direction de {destination}"
            },
            "slight right": {
                "default": "Continuer lÃ©gÃ¨rement Ã  droite",
                "name": "Continuer lÃ©gÃ¨rement Ã  droite sur {way_name}",
                "destination": "Continuer lÃ©gÃ¨rement Ã  droite en direction de {destination}"
            },
            "uturn": {
                "default": "Fair demi-tour",
                "name": "Fair demi-tour sur {way_name}",
                "destination": "Fair demi-tour en direction de {destination}"
            }
        },
        "notification": {
            "default": {
                "default": "Continuer {modifier}",
                "name": "Continuer {modifier} sur {way_name}",
                "destination" : "Continuer {modifier} en direction de {destination}"
            },
            "uturn": {
                "default": "Fair demi-tour",
                "name": "Fair demi-tour sur {way_name}",
                "destination": "Fair demi-tour en direction de {destination}"
            }
        },
        "off ramp": {
            "default": {
                "default": "Prendre la sortie",
                "name": "Prendre la sortie sur {way_name}",
                "destination": "Prendre la sortie en direction de {destination}"
            },
            "left": {
                "default": "Prendre la sortie Ã  gauche",
                "name": "Prendre la sortie Ã  gauche sur {way_name}",
                "destination": "Prendre la sortie Ã  gauche en direction de {destination}"
            },
            "right": {
                "default": "Prendre la sortie Ã  droite",
                "name": "Prendre la sortie Ã  droite sur {way_name}",
                "destination": "Prendre la sortie Ã  droite en direction de {destination}"
            },
            "sharp left": {
                "default": "Prendre la sortie Ã  gauche",
                "name": "Prendre la sortie Ã  gauche sur {way_name}",
                "destination": "Prendre la sortie Ã  gauche en direction de {destination}"
            },
            "sharp right": {
                "default": "Prendre la sortie Ã  droite",
                "name": "Prendre la sortie Ã  droite sur {way_name}",
                "destination": "Prendre la sortie Ã  droite en direction de {destination}"
            },
            "slight left": {
                "default": "Prendre la sortie Ã  gauche",
                "name": "Prendre la sortie Ã  gauche sur {way_name}",
                "destination": "Prendre la sortie Ã  gauche en direction de {destination}"
            },
            "slight right": {
                "default": "Prendre la sortie Ã  droite",
                "name": "Prendre la sortie Ã  droite sur {way_name}",
                "destination": "Prendre la sortie Ã  droite en direction de {destination}"
            }
        },
        "on ramp": {
            "default": {
                "default": "Prendre la sortie",
                "name": "Prendre la sortie sur {way_name}",
                "destination": "Prendre la sortie en direction de {destination}"
            },
            "left": {
                "default": "Prendre la sortie Ã  gauche",
                "name": "Prendre la sortie Ã  gauche sur {way_name}",
                "destination": "Prendre la sortie Ã  gauche en direction de {destination}"
            },
            "right": {
                "default": "Prendre la sortie Ã  droite",
                "name": "Prendre la sortie Ã  droite sur {way_name}",
                "destination": "Prendre la sortie Ã  droite en direction de {destination}"
            },
            "sharp left": {
                "default": "Prendre la sortie Ã  gauche",
                "name": "Prendre la sortie Ã  gauche sur {way_name}",
                "destination": "Prendre la sortie Ã  gauche en direction de {destination}"
            },
            "sharp right": {
                "default": "Prendre la sortie Ã  droite",
                "name": "Prendre la sortie Ã  droite sur {way_name}",
                "destination": "Prendre la sortie Ã  droite en direction de {destination}"
            },
            "slight left": {
                "default": "Prendre la sortie Ã  gauche",
                "name": "Prendre la sortie Ã  gauche sur {way_name}",
                "destination": "Prendre la sortie Ã  gauche en direction de {destination}"
            },
            "slight right": {
                "default": "Prendre la sortie Ã  droite",
                "name": "Prendre la sortie Ã  droite sur {way_name}",
                "destination": "Prendre la sortie Ã  droite en direction de {destination}"
            }
        },
        "rotary": {
            "default": {
                "default": {
                    "default": "Entrer dans le rond-point",
                    "name": "Entrer dans le rond-point et sortir par {way_name}",
                    "destination": "Entrer dans le rond-point et sortir en direction de {destination}"
                },
                "name": {
                    "default": "Entrer dans le rond-point {rotary_name}",
                    "name": "Entrer dans le rond-point {rotary_name} et sortir par {way_name}",
                    "destination": "Entrer dans le rond-point {rotary_name} et sortir en direction de {destination}"
                },
                "exit": {
                    "default": "Entrer dans le rond-point et prendre la {exit_number} sortie",
                    "name": "Entrer dans le rond-point et prendre la {exit_number} sortie sur {way_name}",
                    "destination": "Entrer dans le rond-point et prendre la {exit_number} sortie en direction de {destination}"
                },
                "name_exit": {
                    "default": "Entrer dans le rond-point {rotary_name} et prendre la {exit_number} sortie",
                    "name": "Entrer dans le rond-point {rotary_name} et prendre la {exit_number} sortie sur {way_name}",
                    "destination": "Entrer dans le rond-point {rotary_name} et prendre la {exit_number} sortie en direction de {destination}"
                }
            }
        },
        "roundabout": {
            "default": {
                "exit": {
                    "default": "Entrer dans le rond-point et prendre la {exit_number} sortie",
                    "name": "Entrer dans le rond-point et prendre la {exit_number} sortie sur {way_name}",
                    "destination": "Entrer dans le rond-point et prendre la {exit_number} sortie en direction de {destination}"
                },
                "default": {
                    "default": "Entrer dans le rond-point",
                    "name": "Entrer dans le rond-point et sortir par {way_name}",
                    "destination": "Entrer dans le rond-point et sortir en direction de {destination}"
                }
            }
        },
        "roundabout turn": {
            "default": {
                "default": "Au rond-point, tourner {modifier}",
                "name": "Au rond-point, tourner {modifier} sur {way_name}",
                "destination": "Au rond-point, tourner {modifier} en direction de {destination}"
            },
            "left": {
                "default": "Au rond-point, tourner Ã  gauche",
                "name": "Au rond-point, tourner Ã  gauche sur {way_name}",
                "destination": "Au rond-point, tourner Ã  gauche en direction de {destination}"
            },
            "right": {
                "default": "Au rond-point, tourner Ã  droite",
                "name": "Au rond-point, tourner Ã  droite sur {way_name}",
                "destination": "Au rond-point, tourner Ã  droite en direction de {destination}"
            },
            "straight": {
                "default": "Au rond-point, continuer tout droit",
                "name": "Au rond-point, continuer tout droit sur {way_name}",
                "destination": "Au rond-point, continuer tout droit en direction de {destination}"
            }
        },
        "turn": {
            "default": {
                "default": "Tourner {modifier}",
                "name": "Tourner {modifier} sur {way_name}",
                "destination": "Tourner {modifier} en direction de {destination}"
            },
            "left": {
                "default": "Tourner Ã  gauche",
                "name": "Tourner Ã  gauche sur {way_name}",
                "destination": "Tourner Ã  gauche en direction de {destination}"
            },
            "right": {
                "default": "Tourner Ã  droite",
                "name": "Tourner Ã  droite sur {way_name}",
                "destination": "Tourner Ã  droite en direction de {destination}"
            },
            "straight": {
                "default": "Aller tout droit",
                "name": "Aller tout droit sur {way_name}",
                "destination": "Aller tout droit en direction de {destination}"
            }
        },
        "use lane": {
            "no_lanes": {
                "default": "Continuer tout droit"
            },
            "default": {
                "default": "{lane_instruction} pour continuer {modifier}"
            },
            "straight": {
                "default": "{lane_instruction}"
            },
            "left": {
                "default": "{lane_instruction} pour tourner Ã  gauche"
            },
            "right": {
                "default": "{lane_instruction} pour tourner Ã  droite"
            }
        }
    }
}

},{}],14:[function(require,module,exports){
module.exports={
    "meta": {
        "capitalizeFirstLetter": true
    },
    "v5": {
        "constants": {
            "ordinalize": {
                "1": "eerste",
                "2": "tweede",
                "3": "derde",
                "4": "vierde",
                "5": "vijfde",
                "6": "zesde",
                "7": "zevende",
                "8": "achtste",
                "9": "negende",
                "10": "tiende"
            },
            "direction": {
                "north": "noord",
                "northeast": "noordoost",
                "east": "oost",
                "southeast": "zuidoost",
                "south": "zuid",
                "southwest": "zuidwest",
                "west": "west",
                "northwest": "noordwest"
            },
            "modifier": {
                "left": "links",
                "right": "rechts",
                "sharp left": "linksaf",
                "sharp right": "rechtsaf",
                "slight left": "links",
                "slight right": "rechts",
                "straight": "rechtdoor",
                "uturn": "omkeren"
            },
            "lanes": {
                "xo": "Rechts aanhouden",
                "ox": "Links aanhouden",
                "xox": "In het midden blijven",
                "oxo": "Links of rechts blijven"
            }
        },
        "modes": {
            "ferry": {
                "default": "Neem het veer",
                "name": "Neem het veer {way_name}",
                "destination": "Neem het veer naar {destination}"
            }
        },
        "arrive": {
            "default": {
                "default": "Je bent gearriveerd op de {nth} bestemming."
            },
            "left": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich links."
            },
            "right": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich rechts."
            },
            "sharp left": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich links."
            },
            "sharp right": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich rechts."
            },
            "slight right": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich rechts."
            },
            "slight left": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich links."
            },
            "straight": {
                "default": "Je bent gearriveerd. De {nth} bestemming bevindt zich voor je."
            }
        },
        "continue": {
            "default": {
                "default": "Ga {modifier}",
                "name": "Ga {modifier} naar {way_name}",
                "destination": "Ga {modifier} richting {destination}"
            },
            "slight left": {
                "default": "Links aanhouden",
                "name": "Links aanhouden naar {way_name}",
                "destination": "Links aanhouden richting {destination}"
            },
            "slight right": {
                "default": "Rechts aanhouden",
                "name": "Rechts aanhouden naar {way_name}",
                "destination": "Rechts aanhouden richting {destination}"
            },
            "uturn": {
                "default": "Keer om",
                "name": "Keer om naar {way_name}",
                "destination": "Keer om richting {destination}"
            }
        },
        "depart": {
            "default": {
                "default": "Vertrek in {direction}elijke richting",
                "name": "Neem {way_name} in {direction}elijke richting"
            }
        },
        "end of road": {
            "default": {
                "default": "Ga {modifier}",
                "name": "Ga {modifier} naar {way_name}",
                "destination": "Ga {modifier} richting {destination}"
            },
            "straight": {
                "default": "Ga in de aangegeven richting",
                "name": "Ga naar {way_name}",
                "destination": "Ga richting {destination}"
            },
            "uturn": {
                "default": "Keer om",
                "name": "Keer om naar {way_name}",
                "destination": "Keer om richting {destination}"
            }
        },
        "fork": {
            "default": {
                "default": "Ga {modifier} op de splitsing",
                "name": "Ga {modifier} op de splitsing naar {way_name}",
                "destination": "Ga {modifier} op de splitsing richting {destination}"
            },
            "slight left": {
                "default": "Links aanhouden op de splitsing",
                "name": "Links aanhouden op de splitsing naar {way_name}",
                "destination": "Links aanhouden op de splitsing richting {destination}"
            },
            "slight right": {
                "default": "Rechts aanhouden op de splitsing",
                "name": "Rechts aanhouden op de splitsing naar {way_name}",
                "destination": "Rechts aanhouden op de splitsing richting {destination}"
            },
            "sharp left": {
                "default": "Linksaf op de splitsing",
                "name": "Linksaf op de splitsing naar {way_name}",
                "destination": "Linksaf op de splitsing richting {destination}"
            },
            "sharp right": {
              "default": "Rechtsaf op de splitsing",
              "name": "Rechtsaf op de splitsing naar {way_name}",
              "destination": "Rechtsaf op de splitsing richting {destination}"
            },
            "uturn": {
                "default": "Keer om",
                "name": "Keer om naar {way_name}",
                "destination": "Keer om richting {destination}"
            }
        },
        "merge": {
            "default": {
                "default": "Bij de splitsing {modifier}",
                "name": "Bij de splitsing {modifier} naar {way_name}",
                "destination": "Bij de splitsing {modifier} richting {destination}"
            },
            "slight left": {
                "default": "Bij de splitsing links aanhouden",
                "name": "Bij de splitsing links aanhouden naar {way_name}",
                "destination": "Bij de splitsing links aanhouden richting {destination}"
            },
            "slight right": {
                "default": "Bij de splitsing rechts aanhouden",
                "name": "Bij de splitsing rechts aanhouden naar {way_name}",
                "destination": "Bij de splitsing rechts aanhouden richting {destination}"
            },
            "sharp left": {
                "default": "Bij de splitsing linksaf",
                "name": "Bij de splitsing linksaf naar {way_name}",
                "destination": "Bij de splitsing linksaf richting {destination}"
            },
            "sharp right": {
                "default": "Bij de splitsing rechtsaf",
                "name": "Bij de splitsing rechtsaf naar {way_name}",
                "destination": "Bij de splitsing rechtsaf richting {destination}"
            },
            "uturn": {
                "default": "Keer om",
                "name": "Keer om naar {way_name}",
                "destination": "Keer om richting {destination}"
            }
        },
        "new name": {
            "default": {
                "default": "Ga {modifier}",
                "name": "Ga {modifier} naar {way_name}",
                "destination": "Ga {modifier} richting {destination}"
            },
            "sharp left": {
                "default": "Linksaf",
                "name": "Linksaf naar {way_name}",
                "destination": "Linksaf richting {destination}"
            },
            "sharp right": {
                "default": "Rechtsaf",
                "name": "Rechtsaf naar {way_name}",
                "destination": "Rechtsaf richting {destination}"
            },
            "slight left": {
                "default": "Links aanhouden",
                "name": "Links aanhouden naar {way_name}",
                "destination": "Links aanhouden richting {destination}"
            },
            "slight right": {
                "default": "Rechts aanhouden",
                "name": "Rechts aanhouden naar {way_name}",
                "destination": "Rechts aanhouden richting {destination}"
            },
            "uturn": {
                "default": "Keer om",
                "name": "Keer om naar {way_name}",
                "destination": "Keer om richting {destination}"
            }
        },
        "notification": {
            "default": {
                "default": "Ga {modifier}",
                "name": "Ga {modifier} naar {way_name}",
                "destination" : "Ga {modifier} richting {destination}"
            },
            "uturn": {
                "default": "Keer om",
                "name": "Keer om naar {way_name}",
                "destination": "Keer om richting {destination}"
            }
        },
        "off ramp": {
            "default": {
                "default": "Neem de afrit",
                "name": "Neem de afrit naar {way_name}",
                "destination": "Neem de afrit richting {destination}"
            },
            "left": {
                "default": "Neem de afrit links",
                "name": "Neem de afrit links naar {way_name}",
                "destination": "Neem de afrit links richting {destination}"
            },
            "right": {
              "default": "Neem de afrit rechts",
              "name": "Neem de afrit rechts naar {way_name}",
              "destination": "Neem de afrit rechts richting {destination}"
            },
            "sharp left": {
                "default": "Neem de afrit links",
                "name": "Neem de afrit links naar {way_name}",
                "destination": "Neem de afrit links richting {destination}"
            },
            "sharp right": {
                "default": "Neem de afrit rechts",
                "name": "Neem de afrit rechts naar {way_name}",
                "destination": "Neem de afrit rechts richting {destination}"
            },
            "slight left": {
                "default": "Neem de afrit links",
                "name": "Neem de afrit links naar {way_name}",
                "destination": "Neem de afrit links richting {destination}"
            },
            "slight right": {
                "default": "Neem de afrit rechts",
                "name": "Neem de afrit rechts naar {way_name}",
                "destination": "Neem de afrit rechts richting {destination}"
            }
        },
        "on ramp": {
            "default": {
                "default": "Neem de oprit",
                "name": "Neem de oprit naar {way_name}",
                "destination": "Neem de oprit richting {destination}"
            },
            "left": {
                "default": "Neem de oprit links",
                "name": "Neem de oprit links naar {way_name}",
                "destination": "Neem de oprit links richting {destination}"
            },
            "right": {
              "default": "Neem de oprit rechts",
              "name": "Neem de oprit rechts naar {way_name}",
              "destination": "Neem de oprit rechts richting {destination}"
            },
            "sharp left": {
                "default": "Neem de oprit links",
                "name": "Neem de oprit links naar {way_name}",
                "destination": "Neem de oprit links richting {destination}"
            },
            "sharp right": {
                "default": "Neem de oprit rechts",
                "name": "Neem de oprit rechts naar {way_name}",
                "destination": "Neem de oprit rechts richting {destination}"
            },
            "slight left": {
                "default": "Neem de oprit links",
                "name": "Neem de oprit links naar {way_name}",
                "destination": "Neem de oprit links richting {destination}"
            },
            "slight right": {
                "default": "Neem de oprit rechts",
                "name": "Neem de oprit rechts naar {way_name}",
                "destination": "Neem de oprit rechts richting {destination}"
            }
        },
        "rotary": {
            "default": {
                "default": {
                    "default": "Ga het knooppunt op",
                    "name": "Verlaat het knooppunt naar {way_name}",
                    "destination": "Verlaat het knooppunt richting {destination}"
                },
                "name": {
                    "default": "Ga het knooppunt {rotary_name} op",
                    "name": "Verlaat het knooppunt {rotary_name} naar {way_name}",
                    "destination": "Verlaat het knooppunt {rotary_name} richting {destination}"
                },
                "exit": {
                    "default": "Ga het knooppunt op en neem afslag {exit_number}",
                    "name": "Ga het knooppunt op en neem afslag {exit_number} naar {way_name}",
                    "destination": "Ga het knooppunt op en neem afslag {exit_number} richting {destination}"
                },
                "name_exit": {
                    "default": "Ga het knooppunt {rotary_name} op en neem afslag {exit_number}",
                    "name": "Ga het knooppunt {rotary_name} op en neem afslag {exit_number} naar {way_name}",
                    "destination": "Ga het knooppunt {rotary_name} op en neem afslag {exit_number} richting {destination}"

                }
            }
        },
        "roundabout": {
            "default": {
                "exit": {
                    "default": "Ga de rotonde op en neem afslag {exit_number}",
                    "name": "Ga de rotonde op en neem afslag {exit_number} naar {way_name}",
                    "destination": "Ga de rotonde op en neem afslag {exit_number} richting {destination}"
                },
                "default": {
                    "default": "Ga de rotonde op",
                    "name": "Verlaat de rotonde naar {way_name}",
                    "destination": "Verlaat de rotonde richting {destination}"
                }
            }
        },
        "roundabout turn": {
            "default": {
                "default": "Ga {modifier} op de rotonde",
                "name": "Ga {modifier} op de rotonde naar {way_name}",
                "destination": "Ga {modifier} op de rotonde richting {destination}"
            },
            "left": {
                "default": "Ga links op de rotonde",
                "name": "Ga links op de rotonde naar {way_name}",
                "destination": "Ga links op de rotonde richting {destination}"
            },
            "right": {
                "default": "Ga rechts op de rotonde",
                "name": "Ga rechts op de rotonde naar {way_name}",
                "destination": "Ga rechts op de rotonde richting {destination}"
            },
            "straight": {
                "default": "Rechtdoor op de rotonde",
                "name": "Rechtdoor op de rotonde naar {way_name}",
                "destination": "Rechtdoor op de rotonde richting {destination}"
            }
        },
        "turn": {
            "default": {
                "default": "Ga {modifier}",
                "name": "Ga {modifier} naar {way_name}",
                "destination": "Ga {modifier} richting {destination}"
            },
            "left": {
                "default": "Ga linksaf",
                "name": "Ga linksaf naar {way_name}",
                "destination": "Ga linksaf richting {destination}"
            },
            "right": {
                "default": "Ga rechtsaf",
                "name": "Ga rechtsaf naar {way_name}",
                "destination": "Ga rechtsaf richting {destination}"
            },
            "straight": {
                "default": "Ga rechtdoor",
                "name": "Ga rechtdoor naar {way_name}",
                "destination": "Ga rechtdoor richting {destination}"
            }
        },
        "use lane": {
            "no_lanes": {
                "default": "Rechtdoor"
            },
            "default": {
                "default": "{lane_instruction} ga {modifier}"
            },
            "straight": {
                "default": "{lane_instruction}"
            },
            "left": {
                "default": "{lane_instruction} om links te gaan"
            },
            "right": {
                "default": "{lane_instruction} om rechts te gaan"
            }
        }
    }
}

},{}],15:[function(require,module,exports){
module.exports={
    "meta": {
        "capitalizeFirstLetter": false
    },
    "v5": {
        "constants": {
            "ordinalize": {
                "1": "ç¬¬ä¸€",
                "2": "ç¬¬äºŒ",
                "3": "ç¬¬ä¸‰",
                "4": "ç¬¬å››",
                "5": "ç¬¬äº”",
                "6": "ç¬¬å…­",
                "7": "ç¬¬ä¸ƒ",
                "8": "ç¬¬å…«",
                "9": "ç¬¬ä¹",
                "10": "ç¬¬å"
            },
            "direction": {
                "north": "åŒ—",
                "northeast": "ä¸œåŒ—",
                "east": "ä¸œ",
                "southeast": "ä¸œå—",
                "south": "å—",
                "southwest": "è¥¿å—",
                "west": "è¥¿",
                "northwest": "è¥¿åŒ—"
            },
            "modifier": {
                "left": "å‘å·¦",
                "right": "å‘å³",
                "sharp left": "å‘å·¦",
                "sharp right": "å‘å³",
                "slight left": "å‘å·¦",
                "slight right": "å‘å³",
                "straight": "ç›´è¡Œ",
                "uturn": "è°ƒå¤´"
            },
            "lanes": {
                "xo": "é å³ç›´è¡Œ",
                "ox": "é å·¦ç›´è¡Œ",
                "xox": "ä¿æŒåœ¨é“è·¯ä¸­é—´ç›´è¡Œ",
                "oxo": "ä¿æŒåœ¨é“è·¯ä¸¤ä¾§ç›´è¡Œ"
            }
        },
        "modes": {
            "ferry": {
                "default": "ä¹˜åè½®æ¸¡",
                "name": "ä¹˜å{way_name}è½®æ¸¡",
                "destination": "ä¹˜åå¼€å¾€{destination}çš„è½®æ¸¡"
            }
        },
        "arrive": {
            "default": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°"
            },
            "left": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨é“è·¯å·¦ä¾§"
            },
            "right": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨é“è·¯å³ä¾§"
            },
            "sharp left": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨é“è·¯å·¦ä¾§"
            },
            "sharp right": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨é“è·¯å³ä¾§"
            },
            "slight right": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨é“è·¯å³ä¾§"
            },
            "slight left": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨é“è·¯å·¦ä¾§"
            },
            "straight": {
                "default": "æ‚¨å·²ç»åˆ°è¾¾æ‚¨çš„{nth}ä¸ªç›®çš„åœ°ï¼Œåœ¨æ‚¨æ­£å‰æ–¹"
            }
        },
        "continue": {
            "default": {
                "default": "ç»§ç»­{modifier}",
                "name": "ç»§ç»­{modifier}ï¼Œä¸Š{way_name}",
                "destination": "ç»§ç»­{modifier}è¡Œé©¶ï¼Œå‰å¾€{destination}"
            },
            "uturn": {
                "default": "è°ƒå¤´",
                "name": "è°ƒå¤´ä¸Š{way_name}",
                "destination": "è°ƒå¤´åŽå‰å¾€{destination}"
            }
        },
        "depart": {
            "default": {
                "default": "å‡ºå‘å‘{direction}",
                "name": "å‡ºå‘å‘{direction}ï¼Œä¸Š{way_name}"
            }
        },
        "end of road": {
            "default": {
                "default": "{modifier}è¡Œé©¶",
                "name": "{modifier}è¡Œé©¶ï¼Œä¸Š{way_name}",
                "destination": "{modifier}è¡Œé©¶ï¼Œå‰å¾€{destination}"
            },
            "straight": {
                "default": "ç»§ç»­ç›´è¡Œ",
                "name": "ç»§ç»­ç›´è¡Œï¼Œä¸Š{way_name}",
                "destination": "ç»§ç»­ç›´è¡Œï¼Œå‰å¾€{destination}"
            },
            "uturn": {
                "default": "åœ¨é“è·¯å°½å¤´è°ƒå¤´",
                "name": "åœ¨é“è·¯å°½å¤´è°ƒå¤´ä¸Š{way_name}",
                "destination": "åœ¨é“è·¯å°½å¤´è°ƒå¤´ï¼Œå‰å¾€{destination}"
            }
        },
        "fork": {
            "default": {
                "default": "åœ¨å²”é“ä¿æŒ{modifier}",
                "name": "åœ¨å²”é“ä¿æŒ{modifier}ï¼Œä¸Š{way_name}",
                "destination": "åœ¨å²”é“ä¿æŒ{modifier}ï¼Œå‰å¾€{destination}"
            },
            "uturn": {
                "default": "è°ƒå¤´",
                "name": "è°ƒå¤´ï¼Œä¸Š{way_name}",
                "destination": "è°ƒå¤´ï¼Œå‰å¾€{destination}"
            }
        },
        "merge": {
            "default": {
                "default": "{modifier}å¹¶é“",
                "name": "{modifier}å¹¶é“ï¼Œä¸Š{way_name}",
                "destination": "{modifier}å¹¶é“ï¼Œå‰å¾€{destination}"
            },
            "uturn": {
                "default": "è°ƒå¤´",
                "name": "è°ƒå¤´ï¼Œä¸Š{way_name}",
                "destination": "è°ƒå¤´ï¼Œå‰å¾€{destination}"
            }
        },
        "new name": {
            "default": {
                "default": "ç»§ç»­{modifier}",
                "name": "ç»§ç»­{modifier}ï¼Œä¸Š{way_name}",
                "destination": "ç»§ç»­{modifier}ï¼Œå‰å¾€{destination}"
            },
             "uturn": {
                "default": "è°ƒå¤´",
                "name": "è°ƒå¤´ï¼Œä¸Š{way_name}",
                "destination": "è°ƒå¤´ï¼Œå‰å¾€{destination}"
            }
        },
        "notification": {
            "default": {
                "default": "ç»§ç»­{modifier}",
                "name": "ç»§ç»­{modifier}ï¼Œä¸Š{way_name}",
                "destination" : "ç»§ç»­{modifier}ï¼Œå‰å¾€{destination}"
            },
            "uturn": {
                "default": "è°ƒå¤´",
                "name": "è°ƒå¤´ï¼Œä¸Š{way_name}",
                "destination": "è°ƒå¤´ï¼Œå‰å¾€{destination}"
            }
        },
        "off ramp": {
            "default": {
                "default": "ä¸ŠåŒé“",
                "name": "é€šè¿‡åŒé“é©¶å…¥{way_name}",
                "destination": "é€šè¿‡åŒé“å‰å¾€{destination}"
            },
            "left": {
                "default": "é€šè¿‡å·¦è¾¹çš„åŒé“",
                "name": "é€šè¿‡å·¦è¾¹çš„åŒé“é©¶å…¥{way_name}",
                "destination": "é€šè¿‡å·¦è¾¹çš„åŒé“å‰å¾€{destination}"
            },
            "right": {
                "default": "é€šè¿‡å³è¾¹çš„åŒé“",
                "name": "é€šè¿‡å³è¾¹çš„åŒé“é©¶å…¥{way_name}",
                "destination": "é€šè¿‡å³è¾¹çš„åŒé“å‰å¾€{destination}"
            }
        },
        "on ramp": {
            "default": {
                "default": "é€šè¿‡åŒé“",
                "name": "é€šè¿‡åŒé“é©¶å…¥{way_name}",
                "destination": "é€šè¿‡åŒé“å‰å¾€{destination}"
            },
            "left": {
                "default": "é€šè¿‡å·¦è¾¹çš„åŒé“",
                "name": "é€šè¿‡å·¦è¾¹çš„åŒé“é©¶å…¥{way_name}",
                "destination": "é€šè¿‡å·¦è¾¹çš„åŒé“å‰å¾€{destination}"
            },
            "right": {
                "default": "é€šè¿‡å³è¾¹çš„åŒé“",
                "name": "é€šè¿‡å³è¾¹çš„åŒé“é©¶å…¥{way_name}",
                "destination": "é€šè¿‡å³è¾¹çš„åŒé“å‰å¾€{destination}"
            }
        },
        "rotary": {
            "default": {
                "default": {
                    "default": "è¿›å…¥çŽ¯å²›",
                    "name": "é€šè¿‡çŽ¯å²›åŽé©¶å…¥{way_name}",
                    "destination": "é€šè¿‡çŽ¯å²›å‰å¾€{destination}"
                },
                "name": {
                    "default": "è¿›å…¥{rotary_name}çŽ¯å²›",
                    "name": "é€šè¿‡{rotary_name}çŽ¯å²›åŽé©¶å…¥{way_name}",
                    "destination": "é€šè¿‡{rotary_name}çŽ¯å²›åŽå‰å¾€{destination}"
                },
                "exit": {
                    "default": "è¿›å…¥çŽ¯å²›å¹¶ä»Ž{exit_number}å‡ºå£é©¶å‡º",
                    "name": "è¿›å…¥çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡ºè¿›å…¥{way_name}",
                    "destination": "è¿›å…¥çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡ºå‰å¾€{destination}"
                },
                "name_exit": {
                    "default": "è¿›å…¥{rotary_name}çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡º",
                    "name": "è¿›å…¥{rotary_name}çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡ºè¿›å…¥{way_name}",
                    "destination": "è¿›å…¥{rotary_name}çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡ºå‰å¾€{destination}"
                }
            }
        },
        "roundabout": {
            "default": {
                "exit": {
                    "default": "è¿›å…¥çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡º",
                    "name": "è¿›å…¥çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡ºå‰å¾€{way_name}",
                    "destination": "è¿›å…¥çŽ¯å²›åŽä»Ž{exit_number}å‡ºå£é©¶å‡ºå‰å¾€{destination}"
                },
                "default": {
                    "default": "è¿›å…¥çŽ¯å²›",
                    "name": "é€šè¿‡çŽ¯å²›åŽé©¶å…¥{way_name}",
                    "destination": "é€šè¿‡çŽ¯å²›åŽå‰å¾€{destination}"
                }
            }
        },
        "roundabout turn": {
            "default": {
                "default": "åœ¨çŽ¯å²›{modifier}è¡Œé©¶",
                "name": "åœ¨çŽ¯å²›{modifier}è¡Œé©¶ï¼Œä¸Š{way_name}",
                "destination": "åœ¨çŽ¯å²›{modifier}è¡Œé©¶ï¼Œå‰å¾€{destination}"
            },
            "left": {
                "default": "åœ¨çŽ¯å²›å·¦è½¬",
                "name": "åœ¨çŽ¯å²›å·¦è½¬ï¼Œä¸Š{way_name}",
                "destination": "åœ¨çŽ¯å²›å·¦è½¬ï¼Œå‰å¾€{destination}"
            },
            "right": {
                "default": "åœ¨çŽ¯å²›å³è½¬",
                "name": "åœ¨çŽ¯å²›å³è½¬ï¼Œä¸Š{way_name}",
                "destination": "åœ¨çŽ¯å²›å³è½¬ï¼Œå‰å¾€{destination}"
            },
            "straight": {
                "default": "åœ¨çŽ¯å²›ç»§ç»­ç›´è¡Œ",
                "name": "åœ¨çŽ¯å²›ç»§ç»­ç›´è¡Œï¼Œä¸Š{way_name}",
                "destination": "åœ¨çŽ¯å²›ç»§ç»­ç›´è¡Œï¼Œå‰å¾€{destination}"
            }
        },
        "turn": {
            "default": {
                "default": "{modifier}è½¬å¼¯",
                "name": "{modifier}è½¬å¼¯ï¼Œä¸Š{way_name}",
                "destination": "{modifier}è½¬å¼¯ï¼Œå‰å¾€{destination}"
            },
            "left": {
                "default": "å·¦è½¬",
                "name": "å·¦è½¬ï¼Œä¸Š{way_name}",
                "destination": "å·¦è½¬ï¼Œå‰å¾€{destination}"
            },
            "right": {
                "default": "å³è½¬",
                "name": "å³è½¬ï¼Œä¸Š{way_name}",
                "destination": "å³è½¬ï¼Œå‰å¾€{destination}"
            },
            "straight": {
                "default": "ç›´è¡Œ",
                "name": "ç›´è¡Œï¼Œä¸Š{way_name}",
                "destination": "ç›´è¡Œï¼Œå‰å¾€{destination}"
            }
        },
        "use lane": {
            "no_lanes": {
                "default": "ç»§ç»­ç›´è¡Œ"
            },
            "default": {
                "default": "{lane_instruction}ç„¶åŽ{modifier}"
            },
            "straight": {
                "default": "{lane_instruction}"
            },
            "left": {
                "default": "{lane_instruction}ç„¶åŽå·¦è½¬"
            },
            "right": {
                "default": "{lane_instruction}ç„¶åŽå³è½¬"
            }
        }
    }
}

},{}],16:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	module.exports = L.Class.extend({
		options: {
			timeout: 500,
			blurTimeout: 100,
			noResultsMessage: 'No results found.'
		},

		initialize: function(elem, callback, context, options) {
			L.setOptions(this, options);

			this._elem = elem;
			this._resultFn = options.resultFn ? L.Util.bind(options.resultFn, options.resultContext) : null;
			this._autocomplete = options.autocompleteFn ? L.Util.bind(options.autocompleteFn, options.autocompleteContext) : null;
			this._selectFn = L.Util.bind(callback, context);
			this._container = L.DomUtil.create('div', 'leaflet-routing-geocoder-result');
			this._resultTable = L.DomUtil.create('table', '', this._container);

			// TODO: looks a bit like a kludge to register same for input and keypress -
			// browsers supporting both will get duplicate events; just registering
			// input will not catch enter, though.
			L.DomEvent.addListener(this._elem, 'input', this._keyPressed, this);
			L.DomEvent.addListener(this._elem, 'keypress', this._keyPressed, this);
			L.DomEvent.addListener(this._elem, 'keydown', this._keyDown, this);
			L.DomEvent.addListener(this._elem, 'blur', function() {
				if (this._isOpen) {
					this.close();
				}
			}, this);
		},

		close: function() {
			L.DomUtil.removeClass(this._container, 'leaflet-routing-geocoder-result-open');
			this._isOpen = false;
		},

		_open: function() {
			var rect = this._elem.getBoundingClientRect();
			if (!this._container.parentElement) {
				// See notes section under https://developer.mozilla.org/en-US/docs/Web/API/Window/scrollX
				// This abomination is required to support all flavors of IE
				var scrollX = (window.pageXOffset !== undefined) ? window.pageXOffset
					: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
				var scrollY = (window.pageYOffset !== undefined) ? window.pageYOffset
					: (document.documentElement || document.body.parentNode || document.body).scrollTop;
				this._container.style.left = (rect.left + scrollX) + 'px';
				this._container.style.top = (rect.bottom + scrollY) + 'px';
				this._container.style.width = (rect.right - rect.left) + 'px';
				document.body.appendChild(this._container);
			}

			L.DomUtil.addClass(this._container, 'leaflet-routing-geocoder-result-open');
			this._isOpen = true;
		},

		_setResults: function(results) {
			var i,
			    tr,
			    td,
			    text;

			delete this._selection;
			this._results = results;

			while (this._resultTable.firstChild) {
				this._resultTable.removeChild(this._resultTable.firstChild);
			}

			for (i = 0; i < results.length; i++) {
				tr = L.DomUtil.create('tr', '', this._resultTable);
				tr.setAttribute('data-result-index', i);
				td = L.DomUtil.create('td', '', tr);
				text = document.createTextNode(results[i].name);
				td.appendChild(text);
				// mousedown + click because:
				// http://stackoverflow.com/questions/10652852/jquery-fire-click-before-blur-event
				L.DomEvent.addListener(td, 'mousedown', L.DomEvent.preventDefault);
				L.DomEvent.addListener(td, 'click', this._createClickListener(results[i]));
			}

			if (!i) {
				tr = L.DomUtil.create('tr', '', this._resultTable);
				td = L.DomUtil.create('td', 'leaflet-routing-geocoder-no-results', tr);
				td.innerHTML = this.options.noResultsMessage;
			}

			this._open();

			if (results.length > 0) {
				// Select the first entry
				this._select(1);
			}
		},

		_createClickListener: function(r) {
			var resultSelected = this._resultSelected(r);
			return L.bind(function() {
				this._elem.blur();
				resultSelected();
			}, this);
		},

		_resultSelected: function(r) {
			return L.bind(function() {
				this.close();
				this._elem.value = r.name;
				this._lastCompletedText = r.name;
				this._selectFn(r);
			}, this);
		},

		_keyPressed: function(e) {
			var index;

			if (this._isOpen && e.keyCode === 13 && this._selection) {
				index = parseInt(this._selection.getAttribute('data-result-index'), 10);
				this._resultSelected(this._results[index])();
				L.DomEvent.preventDefault(e);
				return;
			}

			if (e.keyCode === 13) {
				this._complete(this._resultFn, true);
				return;
			}

			if (this._autocomplete && document.activeElement === this._elem) {
				if (this._timer) {
					clearTimeout(this._timer);
				}
				this._timer = setTimeout(L.Util.bind(function() { this._complete(this._autocomplete); }, this),
					this.options.timeout);
				return;
			}

			this._unselect();
		},

		_select: function(dir) {
			var sel = this._selection;
			if (sel) {
				L.DomUtil.removeClass(sel.firstChild, 'leaflet-routing-geocoder-selected');
				sel = sel[dir > 0 ? 'nextSibling' : 'previousSibling'];
			}
			if (!sel) {
				sel = this._resultTable[dir > 0 ? 'firstChild' : 'lastChild'];
			}

			if (sel) {
				L.DomUtil.addClass(sel.firstChild, 'leaflet-routing-geocoder-selected');
				this._selection = sel;
			}
		},

		_unselect: function() {
			if (this._selection) {
				L.DomUtil.removeClass(this._selection.firstChild, 'leaflet-routing-geocoder-selected');
			}
			delete this._selection;
		},

		_keyDown: function(e) {
			if (this._isOpen) {
				switch (e.keyCode) {
				// Escape
				case 27:
					this.close();
					L.DomEvent.preventDefault(e);
					return;
				// Up
				case 38:
					this._select(-1);
					L.DomEvent.preventDefault(e);
					return;
				// Down
				case 40:
					this._select(1);
					L.DomEvent.preventDefault(e);
					return;
				}
			}
		},

		_complete: function(completeFn, trySelect) {
			var v = this._elem.value;
			function completeResults(results) {
				this._lastCompletedText = v;
				if (trySelect && results.length === 1) {
					this._resultSelected(results[0])();
				} else {
					this._setResults(results);
				}
			}

			if (!v) {
				return;
			}

			if (v !== this._lastCompletedText) {
				completeFn(v, completeResults, this);
			} else if (trySelect) {
				completeResults.call(this, this._results);
			}
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],17:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	var Itinerary = require('./itinerary');
	var Line = require('./line');
	var Plan = require('./plan');
	var OSRMv1 = require('./osrm-v1');

	module.exports = Itinerary.extend({
		options: {
			fitSelectedRoutes: 'smart',
			routeLine: function(route, options) { return new Line(route, options); },
			autoRoute: true,
			routeWhileDragging: false,
			routeDragInterval: 500,
			waypointMode: 'connect',
			showAlternatives: false,
			defaultErrorHandler: function(e) {
				console.error('Routing error:', e.error);
			}
		},

		initialize: function(options) {
			L.Util.setOptions(this, options);

			this._router = this.options.router || new OSRMv1(options);
			this._plan = this.options.plan || new Plan(this.options.waypoints, options);
			this._requestCount = 0;

			Itinerary.prototype.initialize.call(this, options);

			this.on('routeselected', this._routeSelected, this);
			if (this.options.defaultErrorHandler) {
				this.on('routingerror', this.options.defaultErrorHandler);
			}
			this._plan.on('waypointschanged', this._onWaypointsChanged, this);
			if (options.routeWhileDragging) {
				this._setupRouteDragging();
			}

			if (this.options.autoRoute) {
				this.route();
			}
		},

		_onZoomEnd: function() {
			if (!this._selectedRoute ||
				!this._router.requiresMoreDetail) {
				return;
			}

			var map = this._map;
			if (this._router.requiresMoreDetail(this._selectedRoute,
					map.getZoom(), map.getBounds())) {
				this.route({
					callback: L.bind(function(err, routes) {
						var i;
						if (!err) {
							for (i = 0; i < routes.length; i++) {
								this._routes[i].properties = routes[i].properties;
							}
							this._updateLineCallback(err, routes);
						}

					}, this),
					simplifyGeometry: false,
					geometryOnly: true
				});
			}
		},

		onAdd: function(map) {
			var container = Itinerary.prototype.onAdd.call(this, map);

			this._map = map;
			this._map.addLayer(this._plan);

			this._map.on('zoomend', this._onZoomEnd, this);

			if (this._plan.options.geocoder) {
				container.insertBefore(this._plan.createGeocoders(), container.firstChild);
			}

			return container;
		},

		onRemove: function(map) {
			map.off('zoomend', this._onZoomEnd, this);
			if (this._line) {
				map.removeLayer(this._line);
			}
			map.removeLayer(this._plan);
			if (this._alternatives && this._alternatives.length > 0) {
				for (var i = 0, len = this._alternatives.length; i < len; i++) {
					map.removeLayer(this._alternatives[i]);
				}
			}
			return Itinerary.prototype.onRemove.call(this, map);
		},

		getWaypoints: function() {
			return this._plan.getWaypoints();
		},

		setWaypoints: function(waypoints) {
			this._plan.setWaypoints(waypoints);
			return this;
		},

		spliceWaypoints: function() {
			var removed = this._plan.spliceWaypoints.apply(this._plan, arguments);
			return removed;
		},

		getPlan: function() {
			return this._plan;
		},

		getRouter: function() {
			return this._router;
		},

		_routeSelected: function(e) {
			var route = this._selectedRoute = e.route,
				alternatives = this.options.showAlternatives && e.alternatives,
				fitMode = this.options.fitSelectedRoutes,
				fitBounds =
					(fitMode === 'smart' && !this._waypointsVisible()) ||
					(fitMode !== 'smart' && fitMode);

			this._updateLines({route: route, alternatives: alternatives});

			if (fitBounds) {
				this._map.fitBounds(this._line.getBounds());
			}

			if (this.options.waypointMode === 'snap') {
				this._plan.off('waypointschanged', this._onWaypointsChanged, this);
				this.setWaypoints(route.waypoints);
				this._plan.on('waypointschanged', this._onWaypointsChanged, this);
			}
		},

		_waypointsVisible: function() {
			var wps = this.getWaypoints(),
				mapSize,
				bounds,
				boundsSize,
				i,
				p;

			try {
				mapSize = this._map.getSize();

				for (i = 0; i < wps.length; i++) {
					p = this._map.latLngToLayerPoint(wps[i].latLng);

					if (bounds) {
						bounds.extend(p);
					} else {
						bounds = L.bounds([p]);
					}
				}

				boundsSize = bounds.getSize();
				return (boundsSize.x > mapSize.x / 5 ||
					boundsSize.y > mapSize.y / 5) && this._waypointsInViewport();

			} catch (e) {
				return false;
			}
		},

		_waypointsInViewport: function() {
			var wps = this.getWaypoints(),
				mapBounds,
				i;

			try {
				mapBounds = this._map.getBounds();
			} catch (e) {
				return false;
			}

			for (i = 0; i < wps.length; i++) {
				if (mapBounds.contains(wps[i].latLng)) {
					return true;
				}
			}

			return false;
		},

		_updateLines: function(routes) {
			var addWaypoints = this.options.addWaypoints !== undefined ?
				this.options.addWaypoints : true;
			this._clearLines();

			// add alternatives first so they lie below the main route
			this._alternatives = [];
			if (routes.alternatives) routes.alternatives.forEach(function(alt, i) {
				this._alternatives[i] = this.options.routeLine(alt,
					L.extend({
						isAlternative: true
					}, this.options.altLineOptions || this.options.lineOptions));
				this._alternatives[i].addTo(this._map);
				this._hookAltEvents(this._alternatives[i]);
			}, this);

			this._line = this.options.routeLine(routes.route,
				L.extend({
					addWaypoints: addWaypoints,
					extendToWaypoints: this.options.waypointMode === 'connect'
				}, this.options.lineOptions));
			this._line.addTo(this._map);
			this._hookEvents(this._line);
		},

		_hookEvents: function(l) {
			l.on('linetouched', function(e) {
				this._plan.dragNewWaypoint(e);
			}, this);
		},

		_hookAltEvents: function(l) {
			l.on('linetouched', function(e) {
				var alts = this._routes.slice();
				var selected = alts.splice(e.target._route.routesIndex, 1)[0];
				this.fire('routeselected', {route: selected, alternatives: alts});
			}, this);
		},

		_onWaypointsChanged: function(e) {
			if (this.options.autoRoute) {
				this.route({});
			}
			if (!this._plan.isReady()) {
				this._clearLines();
				this._clearAlts();
			}
			this.fire('waypointschanged', {waypoints: e.waypoints});
		},

		_setupRouteDragging: function() {
			var timer = 0,
				waypoints;

			this._plan.on('waypointdrag', L.bind(function(e) {
				waypoints = e.waypoints;

				if (!timer) {
					timer = setTimeout(L.bind(function() {
						this.route({
							waypoints: waypoints,
							geometryOnly: true,
							callback: L.bind(this._updateLineCallback, this)
						});
						timer = undefined;
					}, this), this.options.routeDragInterval);
				}
			}, this));
			this._plan.on('waypointdragend', function() {
				if (timer) {
					clearTimeout(timer);
					timer = undefined;
				}
				this.route();
			}, this);
		},

		_updateLineCallback: function(err, routes) {
			if (!err) {
				routes = routes.slice();
				var selected = routes.splice(this._selectedRoute.routesIndex, 1)[0];
				this._updateLines({route: selected, alternatives: routes });
			} else if (err.type !== 'abort') {
				this._clearLines();
			}
		},

		route: function(options) {
			var ts = ++this._requestCount,
				wps;

			if (this._pendingRequest && this._pendingRequest.abort) {
				this._pendingRequest.abort();
				this._pendingRequest = null;
			}

			options = options || {};

			if (this._plan.isReady()) {
				if (this.options.useZoomParameter) {
					options.z = this._map && this._map.getZoom();
				}

				wps = options && options.waypoints || this._plan.getWaypoints();
				this.fire('routingstart', {waypoints: wps});
				this._pendingRequest = this._router.route(wps, function(err, routes) {
					this._pendingRequest = null;

					if (options.callback) {
						return options.callback.call(this, err, routes);
					}

					// Prevent race among multiple requests,
					// by checking the current request's count
					// against the last request's; ignore result if
					// this isn't the last request.
					if (ts === this._requestCount) {
						this._clearLines();
						this._clearAlts();
						if (err && err.type !== 'abort') {
							this.fire('routingerror', {error: err});
							return;
						}

						routes.forEach(function(route, i) { route.routesIndex = i; });

						if (!options.geometryOnly) {
							this.fire('routesfound', {waypoints: wps, routes: routes});
							this.setAlternatives(routes);
						} else {
							var selectedRoute = routes.splice(0,1)[0];
							this._routeSelected({route: selectedRoute, alternatives: routes});
						}
					}
				}, this, options);
			}
		},

		_clearLines: function() {
			if (this._line) {
				this._map.removeLayer(this._line);
				delete this._line;
			}
			if (this._alternatives && this._alternatives.length) {
				for (var i in this._alternatives) {
					this._map.removeLayer(this._alternatives[i]);
				}
				this._alternatives = [];
			}
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./itinerary":22,"./line":23,"./osrm-v1":25,"./plan":26}],18:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	module.exports = L.Control.extend({
		options: {
			header: 'Routing error',
			formatMessage: function(error) {
				if (error.status < 0) {
					return 'Calculating the route caused an error. Technical description follows: <code><pre>' +
						error.message + '</pre></code';
				} else {
					return 'The route could not be calculated. ' +
						error.message;
				}
			}
		},

		initialize: function(routingControl, options) {
			L.Control.prototype.initialize.call(this, options);
			routingControl
				.on('routingerror', L.bind(function(e) {
					if (this._element) {
						this._element.children[1].innerHTML = this.options.formatMessage(e.error);
						this._element.style.visibility = 'visible';
					}
				}, this))
				.on('routingstart', L.bind(function() {
					if (this._element) {
						this._element.style.visibility = 'hidden';
					}
				}, this));
		},

		onAdd: function() {
			var header,
				message;

			this._element = L.DomUtil.create('div', 'leaflet-bar leaflet-routing-error');
			this._element.style.visibility = 'hidden';

			header = L.DomUtil.create('h3', null, this._element);
			message = L.DomUtil.create('span', null, this._element);

			header.innerHTML = this.options.header;

			return this._element;
		},

		onRemove: function() {
			delete this._element;
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],19:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	var Localization = require('./localization');

	module.exports = L.Class.extend({
		options: {
			units: 'metric',
			unitNames: null,
			language: 'en',
			roundingSensitivity: 1,
			distanceTemplate: '{value} {unit}'
		},

		initialize: function(options) {
			L.setOptions(this, options);

			var langs = L.Util.isArray(this.options.language) ?
				this.options.language :
				[this.options.language, 'en'];
			this._localization = new Localization(langs);
		},

		formatDistance: function(d /* Number (meters) */, sensitivity) {
			var un = this.options.unitNames || this._localization.localize('units'),
				simpleRounding = sensitivity <= 0,
				round = simpleRounding ? function(v) { return v; } : L.bind(this._round, this),
			    v,
			    yards,
				data,
				pow10;

			if (this.options.units === 'imperial') {
				yards = d / 0.9144;
				if (yards >= 1000) {
					data = {
						value: round(d / 1609.344, sensitivity),
						unit: un.miles
					};
				} else {
					data = {
						value: round(yards, sensitivity),
						unit: un.yards
					};
				}
			} else {
				v = round(d, sensitivity);
				data = {
					value: v >= 1000 ? (v / 1000) : v,
					unit: v >= 1000 ? un.kilometers : un.meters
				};
			}

			if (simpleRounding) {
				data.value = data.value.toFixed(-sensitivity);
			}

			return L.Util.template(this.options.distanceTemplate, data);
		},

		_round: function(d, sensitivity) {
			var s = sensitivity || this.options.roundingSensitivity,
				pow10 = Math.pow(10, (Math.floor(d / s) + '').length - 1),
				r = Math.floor(d / pow10),
				p = (r > 5) ? pow10 : pow10 / 2;

			return Math.round(d / p) * p;
		},

		formatTime: function(t /* Number (seconds) */) {
			var un = this.options.unitNames || this._localization.localize('units');
			// More than 30 seconds precision looks ridiculous
			t = Math.round(t / 30) * 30;

			if (t > 86400) {
				return Math.round(t / 3600) + ' ' + un.hours;
			} else if (t > 3600) {
				return Math.floor(t / 3600) + ' ' + un.hours + ' ' +
					Math.round((t % 3600) / 60) + ' ' + un.minutes;
			} else if (t > 300) {
				return Math.round(t / 60) + ' ' + un.minutes;
			} else if (t > 60) {
				return Math.floor(t / 60) + ' ' + un.minutes +
					(t % 60 !== 0 ? ' ' + (t % 60) + ' ' + un.seconds : '');
			} else {
				return t + ' ' + un.seconds;
			}
		},

		formatInstruction: function(instr, i) {
			if (instr.text === undefined) {
				return this.capitalize(L.Util.template(this._getInstructionTemplate(instr, i),
					L.extend({}, instr, {
						exitStr: instr.exit ? this._localization.localize('formatOrder')(instr.exit) : '',
						dir: this._localization.localize(['directions', instr.direction]),
						modifier: this._localization.localize(['directions', instr.modifier])
					})));
			} else {
				return instr.text;
			}
		},

		getIconName: function(instr, i) {
			switch (instr.type) {
			case 'Head':
				if (i === 0) {
					return 'depart';
				}
				break;
			case 'WaypointReached':
				return 'via';
			case 'Roundabout':
				return 'enter-roundabout';
			case 'DestinationReached':
				return 'arrive';
			}

			switch (instr.modifier) {
			case 'Straight':
				return 'continue';
			case 'SlightRight':
				return 'bear-right';
			case 'Right':
				return 'turn-right';
			case 'SharpRight':
				return 'sharp-right';
			case 'TurnAround':
			case 'Uturn':
				return 'u-turn';
			case 'SharpLeft':
				return 'sharp-left';
			case 'Left':
				return 'turn-left';
			case 'SlightLeft':
				return 'bear-left';
			}
		},

		capitalize: function(s) {
			return s.charAt(0).toUpperCase() + s.substring(1);
		},

		_getInstructionTemplate: function(instr, i) {
			var type = instr.type === 'Straight' ? (i === 0 ? 'Head' : 'Continue') : instr.type,
				strings = this._localization.localize(['instructions', type]);

			if (!strings) {
				strings = [
					this._localization.localize(['directions', type]),
					' ' + this._localization.localize(['instructions', 'Onto'])
				];
			}

			return strings[0] + (strings.length > 1 && instr.road ? strings[1] : '');
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./localization":24}],20:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
	var Autocomplete = require('./autocomplete');
	var Localization = require('./localization');

	function selectInputText(input) {
		if (input.setSelectionRange) {
			// On iOS, select() doesn't work
			input.setSelectionRange(0, 9999);
		} else {
			// On at least IE8, setSeleectionRange doesn't exist
			input.select();
		}
	}

	module.exports = L.Class.extend({
		includes: L.Mixin.Events,

		options: {
			createGeocoder: function(i, nWps, options) {
				var container = L.DomUtil.create('div', 'leaflet-routing-geocoder'),
					input = L.DomUtil.create('input', '', container),
					remove = options.addWaypoints ? L.DomUtil.create('span', 'leaflet-routing-remove-waypoint', container) : undefined;

				input.disabled = !options.addWaypoints;

				return {
					container: container,
					input: input,
					closeButton: remove
				};
			},
			geocoderPlaceholder: function(i, numberWaypoints, geocoderElement) {
				var l = new Localization(geocoderElement.options.language).localize('ui');
				return i === 0 ?
					l.startPlaceholder :
					(i < numberWaypoints - 1 ?
						L.Util.template(l.viaPlaceholder, {viaNumber: i}) :
						l.endPlaceholder);
			},

			geocoderClass: function() {
				return '';
			},

			waypointNameFallback: function(latLng) {
				var ns = latLng.lat < 0 ? 'S' : 'N',
					ew = latLng.lng < 0 ? 'W' : 'E',
					lat = (Math.round(Math.abs(latLng.lat) * 10000) / 10000).toString(),
					lng = (Math.round(Math.abs(latLng.lng) * 10000) / 10000).toString();
				return ns + lat + ', ' + ew + lng;
			},
			maxGeocoderTolerance: 200,
			autocompleteOptions: {},
			language: 'en',
		},

		initialize: function(wp, i, nWps, options) {
			L.setOptions(this, options);

			var g = this.options.createGeocoder(i, nWps, this.options),
				closeButton = g.closeButton,
				geocoderInput = g.input;
			geocoderInput.setAttribute('placeholder', this.options.geocoderPlaceholder(i, nWps, this));
			geocoderInput.className = this.options.geocoderClass(i, nWps);

			this._element = g;
			this._waypoint = wp;

			this.update();
			// This has to be here, or geocoder's value will not be properly
			// initialized.
			// TODO: look into why and make _updateWaypointName fix this.
			geocoderInput.value = wp.name;

			L.DomEvent.addListener(geocoderInput, 'click', function() {
				selectInputText(this);
			}, geocoderInput);

			if (closeButton) {
				L.DomEvent.addListener(closeButton, 'click', function() {
					this.fire('delete', { waypoint: this._waypoint });
				}, this);
			}

			new Autocomplete(geocoderInput, function(r) {
					geocoderInput.value = r.name;
					wp.name = r.name;
					wp.latLng = r.center;
					this.fire('geocoded', { waypoint: wp, value: r });
				}, this, L.extend({
					resultFn: this.options.geocoder.geocode,
					resultContext: this.options.geocoder,
					autocompleteFn: this.options.geocoder.suggest,
					autocompleteContext: this.options.geocoder
				}, this.options.autocompleteOptions));
		},

		getContainer: function() {
			return this._element.container;
		},

		setValue: function(v) {
			this._element.input.value = v;
		},

		update: function(force) {
			var wp = this._waypoint,
				wpCoords;

			wp.name = wp.name || '';

			if (wp.latLng && (force || !wp.name)) {
				wpCoords = this.options.waypointNameFallback(wp.latLng);
				if (this.options.geocoder && this.options.geocoder.reverse) {
					this.options.geocoder.reverse(wp.latLng, 67108864 /* zoom 18 */, function(rs) {
						if (rs.length > 0 && rs[0].center.distanceTo(wp.latLng) < this.options.maxGeocoderTolerance) {
							wp.name = rs[0].name;
						} else {
							wp.name = wpCoords;
						}
						this._update();
					}, this);
				} else {
					wp.name = wpCoords;
					this._update();
				}
			}
		},

		focus: function() {
			var input = this._element.input;
			input.focus();
			selectInputText(input);
		},

		_update: function() {
			var wp = this._waypoint,
			    value = wp && wp.name ? wp.name : '';
			this.setValue(value);
			this.fire('reversegeocoded', {waypoint: wp, value: value});
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./autocomplete":16,"./localization":24}],21:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	module.exports = L.Class.extend({
		options: {
			containerClassName: ''
		},

		initialize: function(options) {
			L.setOptions(this, options);
		},

		createContainer: function(className) {
			var table = L.DomUtil.create('table', className || ''),
				colgroup = L.DomUtil.create('colgroup', '', table);

			L.DomUtil.create('col', 'leaflet-routing-instruction-icon', colgroup);
			L.DomUtil.create('col', 'leaflet-routing-instruction-text', colgroup);
			L.DomUtil.create('col', 'leaflet-routing-instruction-distance', colgroup);

			return table;
		},

		createStepsContainer: function() {
			return L.DomUtil.create('tbody', '');
		},

		createStep: function(text, distance, icon, steps) {
			var row = L.DomUtil.create('tr', '', steps),
				span,
				td;
			td = L.DomUtil.create('td', '', row);
			span = L.DomUtil.create('span', 'leaflet-routing-icon leaflet-routing-icon-'+icon, td);
			td.appendChild(span);
			td = L.DomUtil.create('td', '', row);
			td.appendChild(document.createTextNode(text));
			td = L.DomUtil.create('td', '', row);
			td.appendChild(document.createTextNode(distance));
			return row;
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],22:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
	var Formatter = require('./formatter');
	var ItineraryBuilder = require('./itinerary-builder');

	module.exports = L.Control.extend({
		includes: L.Mixin.Events,

		options: {
			pointMarkerStyle: {
				radius: 5,
				color: '#03f',
				fillColor: 'white',
				opacity: 1,
				fillOpacity: 0.7
			},
			summaryTemplate: '<h2>{name}</h2><h3>{distance}, {time}</h3>',
			timeTemplate: '{time}',
			containerClassName: '',
			alternativeClassName: '',
			minimizedClassName: '',
			itineraryClassName: '',
			totalDistanceRoundingSensitivity: -1,
			show: true,
			collapsible: undefined,
			collapseBtn: function(itinerary) {
				var collapseBtn = L.DomUtil.create('span', itinerary.options.collapseBtnClass);
				L.DomEvent.on(collapseBtn, 'click', itinerary._toggle, itinerary);
				itinerary._container.insertBefore(collapseBtn, itinerary._container.firstChild);
			},
			collapseBtnClass: 'leaflet-routing-collapse-btn'
		},

		initialize: function(options) {
			L.setOptions(this, options);
			this._formatter = this.options.formatter || new Formatter(this.options);
			this._itineraryBuilder = this.options.itineraryBuilder || new ItineraryBuilder({
				containerClassName: this.options.itineraryClassName
			});
		},

		onAdd: function(map) {
			var collapsible = this.options.collapsible;

			collapsible = collapsible || (collapsible === undefined && map.getSize().x <= 640);

			this._container = L.DomUtil.create('div', 'leaflet-routing-container leaflet-bar ' +
				(!this.options.show ? 'leaflet-routing-container-hide ' : '') +
				(collapsible ? 'leaflet-routing-collapsible ' : '') +
				this.options.containerClassName);
			this._altContainer = this.createAlternativesContainer();
			this._container.appendChild(this._altContainer);
			L.DomEvent.disableClickPropagation(this._container);
			L.DomEvent.addListener(this._container, 'mousewheel', function(e) {
				L.DomEvent.stopPropagation(e);
			});

			if (collapsible) {
				this.options.collapseBtn(this);
			}

			return this._container;
		},

		onRemove: function() {
		},

		createAlternativesContainer: function() {
			return L.DomUtil.create('div', 'leaflet-routing-alternatives-container');
		},

		setAlternatives: function(routes) {
			var i,
			    alt,
			    altDiv;

			this._clearAlts();

			this._routes = routes;

			for (i = 0; i < this._routes.length; i++) {
				alt = this._routes[i];
				altDiv = this._createAlternative(alt, i);
				this._altContainer.appendChild(altDiv);
				this._altElements.push(altDiv);
			}

			this._selectRoute({route: this._routes[0], alternatives: this._routes.slice(1)});

			return this;
		},

		show: function() {
			L.DomUtil.removeClass(this._container, 'leaflet-routing-container-hide');
		},

		hide: function() {
			L.DomUtil.addClass(this._container, 'leaflet-routing-container-hide');
		},

		_toggle: function() {
			var collapsed = L.DomUtil.hasClass(this._container, 'leaflet-routing-container-hide');
			this[collapsed ? 'show' : 'hide']();
		},

		_createAlternative: function(alt, i) {
			var altDiv = L.DomUtil.create('div', 'leaflet-routing-alt ' +
				this.options.alternativeClassName +
				(i > 0 ? ' leaflet-routing-alt-minimized ' + this.options.minimizedClassName : '')),
				template = this.options.summaryTemplate,
				data = L.extend({
					name: alt.name,
					distance: this._formatter.formatDistance(alt.summary.totalDistance, this.options.totalDistanceRoundingSensitivity),
					time: this._formatter.formatTime(alt.summary.totalTime)
				}, alt);
			altDiv.innerHTML = typeof(template) === 'function' ? template(data) : L.Util.template(template, data);
			L.DomEvent.addListener(altDiv, 'click', this._onAltClicked, this);
			this.on('routeselected', this._selectAlt, this);

			altDiv.appendChild(this._createItineraryContainer(alt));
			return altDiv;
		},

		_clearAlts: function() {
			var el = this._altContainer;
			while (el && el.firstChild) {
				el.removeChild(el.firstChild);
			}

			this._altElements = [];
		},

		_createItineraryContainer: function(r) {
			var container = this._itineraryBuilder.createContainer(),
			    steps = this._itineraryBuilder.createStepsContainer(),
			    i,
			    instr,
			    step,
			    distance,
			    text,
			    icon;

			container.appendChild(steps);

			for (i = 0; i < r.instructions.length; i++) {
				instr = r.instructions[i];
				text = this._formatter.formatInstruction(instr, i);
				distance = this._formatter.formatDistance(instr.distance);
				icon = this._formatter.getIconName(instr, i);
				step = this._itineraryBuilder.createStep(text, distance, icon, steps);

				this._addRowListeners(step, r.coordinates[instr.index]);
			}

			return container;
		},

		_addRowListeners: function(row, coordinate) {
			L.DomEvent.addListener(row, 'mouseover', function() {
				this._marker = L.circleMarker(coordinate,
					this.options.pointMarkerStyle).addTo(this._map);
			}, this);
			L.DomEvent.addListener(row, 'mouseout', function() {
				if (this._marker) {
					this._map.removeLayer(this._marker);
					delete this._marker;
				}
			}, this);
			L.DomEvent.addListener(row, 'click', function(e) {
				this._map.panTo(coordinate);
				L.DomEvent.stopPropagation(e);
			}, this);
		},

		_onAltClicked: function(e) {
			var altElem = e.target || window.event.srcElement;
			while (!L.DomUtil.hasClass(altElem, 'leaflet-routing-alt')) {
				altElem = altElem.parentElement;
			}

			var j = this._altElements.indexOf(altElem);
			var alts = this._routes.slice();
			var route = alts.splice(j, 1)[0];

			this.fire('routeselected', {
				route: route,
				alternatives: alts
			});
		},

		_selectAlt: function(e) {
			var altElem,
			    j,
			    n,
			    classFn;

			altElem = this._altElements[e.route.routesIndex];

			if (L.DomUtil.hasClass(altElem, 'leaflet-routing-alt-minimized')) {
				for (j = 0; j < this._altElements.length; j++) {
					n = this._altElements[j];
					classFn = j === e.route.routesIndex ? 'removeClass' : 'addClass';
					L.DomUtil[classFn](n, 'leaflet-routing-alt-minimized');
					if (this.options.minimizedClassName) {
						L.DomUtil[classFn](n, this.options.minimizedClassName);
					}

					if (j !== e.route.routesIndex) n.scrollTop = 0;
				}
			}

			L.DomEvent.stop(e);
		},

		_selectRoute: function(routes) {
			if (this._marker) {
				this._map.removeLayer(this._marker);
				delete this._marker;
			}
			this.fire('routeselected', routes);
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./formatter":19,"./itinerary-builder":21}],23:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
	
	module.exports = L.LayerGroup.extend({
		includes: L.Mixin.Events,

		options: {
			styles: [
				{color: 'black', opacity: 0.15, weight: 9},
				{color: 'white', opacity: 0.8, weight: 6},
				{color: 'red', opacity: 1, weight: 2}
			],
			missingRouteStyles: [
				{color: 'black', opacity: 0.15, weight: 7},
				{color: 'white', opacity: 0.6, weight: 4},
				{color: 'gray', opacity: 0.8, weight: 2, dashArray: '7,12'}
			],
			addWaypoints: true,
			extendToWaypoints: true,
			missingRouteTolerance: 10
		},

		initialize: function(route, options) {
			L.setOptions(this, options);
			L.LayerGroup.prototype.initialize.call(this, options);
			this._route = route;

			if (this.options.extendToWaypoints) {
				this._extendToWaypoints();
			}

			this._addSegment(
				route.coordinates,
				this.options.styles,
				this.options.addWaypoints);
		},
		
		getBounds: function() {
			return L.latLngBounds(this._route.coordinates);
		},

		_findWaypointIndices: function() {
			var wps = this._route.inputWaypoints,
			    indices = [],
			    i;
			for (i = 0; i < wps.length; i++) {
				indices.push(this._findClosestRoutePoint(wps[i].latLng));
			}

			return indices;
		},

		_findClosestRoutePoint: function(latlng) {
			var minDist = Number.MAX_VALUE,
				minIndex,
			    i,
			    d;

			for (i = this._route.coordinates.length - 1; i >= 0 ; i--) {
				// TODO: maybe do this in pixel space instead?
				d = latlng.distanceTo(this._route.coordinates[i]);
				if (d < minDist) {
					minIndex = i;
					minDist = d;
				}
			}

			return minIndex;
		},

		_extendToWaypoints: function() {
			var wps = this._route.inputWaypoints,
				wpIndices = this._getWaypointIndices(),
			    i,
			    wpLatLng,
			    routeCoord;

			for (i = 0; i < wps.length; i++) {
				wpLatLng = wps[i].latLng;
				routeCoord = L.latLng(this._route.coordinates[wpIndices[i]]);
				if (wpLatLng.distanceTo(routeCoord) >
					this.options.missingRouteTolerance) {
					this._addSegment([wpLatLng, routeCoord],
						this.options.missingRouteStyles);
				}
			}
		},

		_addSegment: function(coords, styles, mouselistener) {
			var i,
				pl;

			for (i = 0; i < styles.length; i++) {
				pl = L.polyline(coords, styles[i]);
				this.addLayer(pl);
				if (mouselistener) {
					pl.on('mousedown', this._onLineTouched, this);
				}
			}
		},

		_findNearestWpBefore: function(i) {
			var wpIndices = this._getWaypointIndices(),
				j = wpIndices.length - 1;
			while (j >= 0 && wpIndices[j] > i) {
				j--;
			}

			return j;
		},

		_onLineTouched: function(e) {
			var afterIndex = this._findNearestWpBefore(this._findClosestRoutePoint(e.latlng));
			this.fire('linetouched', {
				afterIndex: afterIndex,
				latlng: e.latlng
			});
		},

		_getWaypointIndices: function() {
			if (!this._wpIndices) {
				this._wpIndices = this._route.waypointIndices || this._findWaypointIndices();
			}

			return this._wpIndices;
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],24:[function(require,module,exports){
(function() {
	'use strict';

	var spanish = {
		directions: {
			N: 'norte',
			NE: 'noreste',
			E: 'este',
			SE: 'sureste',
			S: 'sur',
			SW: 'suroeste',
			W: 'oeste',
			NW: 'noroeste',
			SlightRight: 'leve giro a la derecha',
			Right: 'derecha',
			SharpRight: 'giro pronunciado a la derecha',
			SlightLeft: 'leve giro a la izquierda',
			Left: 'izquierda',
			SharpLeft: 'giro pronunciado a la izquierda',
			Uturn: 'media vuelta'
		},
		instructions: {
			// instruction, postfix if the road is named
			'Head':
				['Derecho {dir}', ' sobre {road}'],
			'Continue':
				['Continuar {dir}', ' en {road}'],
			'TurnAround':
				['Dar vuelta'],
			'WaypointReached':
				['LlegÃ³ a un punto del camino'],
			'Roundabout':
				['Tomar {exitStr} salida en la rotonda', ' en {road}'],
			'DestinationReached':
				['Llegada a destino'],
			'Fork': ['En el cruce gira a {modifier}', ' hacia {road}'],
			'Merge': ['IncorpÃ³rate {modifier}', ' hacia {road}'],
			'OnRamp': ['Gira {modifier} en la salida', ' hacia {road}'],
			'OffRamp': ['Toma la salida {modifier}', ' hacia {road}'],
			'EndOfRoad': ['Gira {modifier} al final de la carretera', ' hacia {road}'],
			'Onto': 'hacia {road}'
		},
		formatOrder: function(n) {
			return n + 'Âº';
		},
		ui: {
			startPlaceholder: 'Inicio',
			viaPlaceholder: 'Via {viaNumber}',
			endPlaceholder: 'Destino'
		},
		units: {
			meters: 'm',
			kilometers: 'km',
			yards: 'yd',
			miles: 'mi',
			hours: 'h',
			minutes: 'min',
			seconds: 's'
		}
	};

	L.Routing = L.Routing || {};

	var Localization = L.Class.extend({
		initialize: function(langs) {
			this._langs = L.Util.isArray(langs) ? langs : [langs, 'en'];

			for (var i = 0, l = this._langs.length; i < l; i++) {
				if (!Localization[this._langs[i]]) {
					throw new Error('No localization for language "' + this._langs[i] + '".');
				}
			}
		},

		localize: function(keys) {
			var dict,
				key,
				value;

			keys = L.Util.isArray(keys) ? keys : [keys];

			for (var i = 0, l = this._langs.length; i < l; i++) {
				dict = Localization[this._langs[i]];
				for (var j = 0, nKeys = keys.length; dict && j < nKeys; j++) {
					key = keys[j];
					value = dict[key];
					dict = value;
				}

				if (value) {
					return value;
				}
			}
		}
	});

	module.exports = L.extend(Localization, {
		'en': {
			directions: {
				N: 'north',
				NE: 'northeast',
				E: 'east',
				SE: 'southeast',
				S: 'south',
				SW: 'southwest',
				W: 'west',
				NW: 'northwest',
				SlightRight: 'slight right',
				Right: 'right',
				SharpRight: 'sharp right',
				SlightLeft: 'slight left',
				Left: 'left',
				SharpLeft: 'sharp left',
				Uturn: 'Turn around'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Head {dir}', ' on {road}'],
				'Continue':
					['Continue {dir}'],
				'TurnAround':
					['Turn around'],
				'WaypointReached':
					['Waypoint reached'],
				'Roundabout':
					['Take the {exitStr} exit in the roundabout', ' onto {road}'],
				'DestinationReached':
					['Destination reached'],
				'Fork': ['At the fork, turn {modifier}', ' onto {road}'],
				'Merge': ['Merge {modifier}', ' onto {road}'],
				'OnRamp': ['Turn {modifier} on the ramp', ' onto {road}'],
				'OffRamp': ['Take the ramp on the {modifier}', ' onto {road}'],
				'EndOfRoad': ['Turn {modifier} at the end of the road', ' onto {road}'],
				'Onto': 'onto {road}'
			},
			formatOrder: function(n) {
				var i = n % 10 - 1,
				suffix = ['st', 'nd', 'rd'];

				return suffix[i] ? n + suffix[i] : n + 'th';
			},
			ui: {
				startPlaceholder: 'Start',
				viaPlaceholder: 'Via {viaNumber}',
				endPlaceholder: 'End'
			},
			units: {
				meters: 'm',
				kilometers: 'km',
				yards: 'yd',
				miles: 'mi',
				hours: 'h',
				minutes: 'min',
				seconds: 's'
			}
		},

		'de': {
			directions: {
				N: 'Norden',
				NE: 'Nordosten',
				E: 'Osten',
				SE: 'SÃ¼dosten',
				S: 'SÃ¼den',
				SW: 'SÃ¼dwesten',
				W: 'Westen',
				NW: 'Nordwesten',
				SlightRight: 'leicht rechts',
				Right: 'rechts',
				SharpRight: 'scharf rechts',
				SlightLeft: 'leicht links',
				Left: 'links',
				SharpLeft: 'scharf links',
				Uturn: 'Wenden'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Richtung {dir}', ' auf {road}'],
				'Continue':
					['Geradeaus Richtung {dir}', ' auf {road}'],
				'SlightRight':
					['Leicht rechts abbiegen', ' auf {road}'],
				'Right':
					['Rechts abbiegen', ' auf {road}'],
				'SharpRight':
					['Scharf rechts abbiegen', ' auf {road}'],
				'TurnAround':
					['Wenden'],
				'SharpLeft':
					['Scharf links abbiegen', ' auf {road}'],
				'Left':
					['Links abbiegen', ' auf {road}'],
				'SlightLeft':
					['Leicht links abbiegen', ' auf {road}'],
				'WaypointReached':
					['Zwischenhalt erreicht'],
				'Roundabout':
					['Nehmen Sie die {exitStr} Ausfahrt im Kreisverkehr', ' auf {road}'],
				'DestinationReached':
					['Sie haben ihr Ziel erreicht'],
				'Fork': ['An der Kreuzung {modifier}', ' auf {road}'],
				'Merge': ['Fahren Sie {modifier} weiter', ' auf {road}'],
				'OnRamp': ['Fahren Sie {modifier} auf die Auffahrt', ' auf {road}'],
				'OffRamp': ['Nehmen Sie die Ausfahrt {modifier}', ' auf {road}'],
				'EndOfRoad': ['Fahren Sie {modifier} am Ende der StraÃŸe', ' auf {road}'],
				'Onto': 'auf {road}'
			},
			formatOrder: function(n) {
				return n + '.';
			},
			ui: {
				startPlaceholder: 'Start',
				viaPlaceholder: 'Via {viaNumber}',
				endPlaceholder: 'Ziel'
			}
		},

		'sv': {
			directions: {
				N: 'norr',
				NE: 'nordost',
				E: 'Ã¶st',
				SE: 'sydost',
				S: 'syd',
				SW: 'sydvÃ¤st',
				W: 'vÃ¤st',
				NW: 'nordvÃ¤st',
				SlightRight: 'svagt hÃ¶ger',
				Right: 'hÃ¶ger',
				SharpRight: 'skarpt hÃ¶ger',
				SlightLeft: 'svagt vÃ¤nster',
				Left: 'vÃ¤nster',
				SharpLeft: 'skarpt vÃ¤nster',
				Uturn: 'VÃ¤nd'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Ã…k Ã¥t {dir}', ' till {road}'],
				'Continue':
					['FortsÃ¤tt {dir}'],
				'SlightRight':
					['Svagt hÃ¶ger', ' till {road}'],
				'Right':
					['SvÃ¤ng hÃ¶ger', ' till {road}'],
				'SharpRight':
					['Skarpt hÃ¶ger', ' till {road}'],
				'TurnAround':
					['VÃ¤nd'],
				'SharpLeft':
					['Skarpt vÃ¤nster', ' till {road}'],
				'Left':
					['SvÃ¤ng vÃ¤nster', ' till {road}'],
				'SlightLeft':
					['Svagt vÃ¤nster', ' till {road}'],
				'WaypointReached':
					['Viapunkt nÃ¥dd'],
				'Roundabout':
					['Tag {exitStr} avfarten i rondellen', ' till {road}'],
				'DestinationReached':
					['Framme vid resans mÃ¥l'],
				'Fork': ['Tag av {modifier}', ' till {road}'],
				'Merge': ['Anslut {modifier} ', ' till {road}'],
				'OnRamp': ['Tag pÃ¥farten {modifier}', ' till {road}'],
				'OffRamp': ['Tag avfarten {modifier}', ' till {road}'],
				'EndOfRoad': ['SvÃ¤ng {modifier} vid vÃ¤gens slut', ' till {road}'],
				'Onto': 'till {road}'
			},
			formatOrder: function(n) {
				return ['fÃ¶rsta', 'andra', 'tredje', 'fjÃ¤rde', 'femte',
					'sjÃ¤tte', 'sjunde', 'Ã¥ttonde', 'nionde', 'tionde'
					/* Can't possibly be more than ten exits, can there? */][n - 1];
			},
			ui: {
				startPlaceholder: 'FrÃ¥n',
				viaPlaceholder: 'Via {viaNumber}',
				endPlaceholder: 'Till'
			}
		},

		'es': spanish,
		'sp': spanish,
		
		'nl': {
			directions: {
				N: 'noordelijke',
				NE: 'noordoostelijke',
				E: 'oostelijke',
				SE: 'zuidoostelijke',
				S: 'zuidelijke',
				SW: 'zuidewestelijke',
				W: 'westelijke',
				NW: 'noordwestelijke'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Vertrek in {dir} richting', ' de {road} op'],
				'Continue':
					['Ga in {dir} richting', ' de {road} op'],
				'SlightRight':
					['Volg de weg naar rechts', ' de {road} op'],
				'Right':
					['Ga rechtsaf', ' de {road} op'],
				'SharpRight':
					['Ga scherpe bocht naar rechts', ' de {road} op'],
				'TurnAround':
					['Keer om'],
				'SharpLeft':
					['Ga scherpe bocht naar links', ' de {road} op'],
				'Left':
					['Ga linksaf', ' de {road} op'],
				'SlightLeft':
					['Volg de weg naar links', ' de {road} op'],
				'WaypointReached':
					['Aangekomen bij tussenpunt'],
				'Roundabout':
					['Neem de {exitStr} afslag op de rotonde', ' de {road} op'],
				'DestinationReached':
					['Aangekomen op eindpunt'],
			},
			formatOrder: function(n) {
				if (n === 1 || n >= 20) {
					return n + 'ste';
				} else {
					return n + 'de';
				}
			},
			ui: {
				startPlaceholder: 'Vertrekpunt',
				viaPlaceholder: 'Via {viaNumber}',
				endPlaceholder: 'Bestemming'
			}
		},
		'fr': {
			directions: {
				N: 'nord',
				NE: 'nord-est',
				E: 'est',
				SE: 'sud-est',
				S: 'sud',
				SW: 'sud-ouest',
				W: 'ouest',
				NW: 'nord-ouest'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Tout droit au {dir}', ' sur {road}'],
				'Continue':
					['Continuer au {dir}', ' sur {road}'],
				'SlightRight':
					['LÃ©gÃ¨rement Ã  droite', ' sur {road}'],
				'Right':
					['A droite', ' sur {road}'],
				'SharpRight':
					['ComplÃ¨tement Ã  droite', ' sur {road}'],
				'TurnAround':
					['Faire demi-tour'],
				'SharpLeft':
					['ComplÃ¨tement Ã  gauche', ' sur {road}'],
				'Left':
					['A gauche', ' sur {road}'],
				'SlightLeft':
					['LÃ©gÃ¨rement Ã  gauche', ' sur {road}'],
				'WaypointReached':
					['Point d\'Ã©tape atteint'],
				'Roundabout':
					['Au rond-point, prenez la {exitStr} sortie', ' sur {road}'],
				'DestinationReached':
					['Destination atteinte'],
			},
			formatOrder: function(n) {
				return n + 'Âº';
			},
			ui: {
				startPlaceholder: 'DÃ©part',
				viaPlaceholder: 'IntermÃ©diaire {viaNumber}',
				endPlaceholder: 'ArrivÃ©e'
			}
		},
		'it': {
			directions: {
				N: 'nord',
				NE: 'nord-est',
				E: 'est',
				SE: 'sud-est',
				S: 'sud',
				SW: 'sud-ovest',
				W: 'ovest',
				NW: 'nord-ovest'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Dritto verso {dir}', ' su {road}'],
				'Continue':
					['Continuare verso {dir}', ' su {road}'],
				'SlightRight':
					['Mantenere la destra', ' su {road}'],
				'Right':
					['A destra', ' su {road}'],
				'SharpRight':
					['Strettamente a destra', ' su {road}'],
				'TurnAround':
					['Fare inversione di marcia'],
				'SharpLeft':
					['Strettamente a sinistra', ' su {road}'],
				'Left':
					['A sinistra', ' sur {road}'],
				'SlightLeft':
					['Mantenere la sinistra', ' su {road}'],
				'WaypointReached':
					['Punto di passaggio raggiunto'],
				'Roundabout':
					['Alla rotonda, prendere la {exitStr} uscita'],
				'DestinationReached':
					['Destinazione raggiunta'],
			},
			formatOrder: function(n) {
				return n + 'Âº';
			},
			ui: {
				startPlaceholder: 'Partenza',
				viaPlaceholder: 'Intermedia {viaNumber}',
				endPlaceholder: 'Destinazione'
			}
		},
		'pt': {
			directions: {
				N: 'norte',
				NE: 'nordeste',
				E: 'leste',
				SE: 'sudeste',
				S: 'sul',
				SW: 'sudoeste',
				W: 'oeste',
				NW: 'noroeste',
				SlightRight: 'curva ligeira a direita',
				Right: 'direita',
				SharpRight: 'curva fechada a direita',
				SlightLeft: 'ligeira a esquerda',
				Left: 'esquerda',
				SharpLeft: 'curva fechada a esquerda',
				Uturn: 'Meia volta'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Siga {dir}', ' na {road}'],
				'Continue':
					['Continue {dir}', ' na {road}'],
				'SlightRight':
					['Curva ligeira a direita', ' na {road}'],
				'Right':
					['Curva a direita', ' na {road}'],
				'SharpRight':
					['Curva fechada a direita', ' na {road}'],
				'TurnAround':
					['Retorne'],
				'SharpLeft':
					['Curva fechada a esquerda', ' na {road}'],
				'Left':
					['Curva a esquerda', ' na {road}'],
				'SlightLeft':
					['Curva ligueira a esquerda', ' na {road}'],
				'WaypointReached':
					['Ponto de interesse atingido'],
				'Roundabout':
					['Pegue a {exitStr} saÃ­da na rotatÃ³ria', ' na {road}'],
				'DestinationReached':
					['Destino atingido'],
				'Fork': ['Na encruzilhada, vire a {modifier}', ' na {road}'],
				'Merge': ['Entre Ã  {modifier}', ' na {road}'],
				'OnRamp': ['Vire {modifier} na rampa', ' na {road}'],
				'OffRamp': ['Entre na rampa na {modifier}', ' na {road}'],
				'EndOfRoad': ['Vire {modifier} no fim da rua', ' na {road}'],
				'Onto': 'na {road}'
			},
			formatOrder: function(n) {
				return n + 'Âº';
			},
			ui: {
				startPlaceholder: 'Origem',
				viaPlaceholder: 'IntermÃ©dio {viaNumber}',
				endPlaceholder: 'Destino'
			}
		},
		'sk': {
			directions: {
				N: 'sever',
				NE: 'serverovÃ½chod',
				E: 'vÃ½chod',
				SE: 'juhovÃ½chod',
				S: 'juh',
				SW: 'juhozÃ¡pad',
				W: 'zÃ¡pad',
				NW: 'serverozÃ¡pad'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['Mierte na {dir}', ' na {road}'],
				'Continue':
					['PokraÄujte na {dir}', ' na {road}'],
				'SlightRight':
					['Mierne doprava', ' na {road}'],
				'Right':
					['Doprava', ' na {road}'],
				'SharpRight':
					['Prudko doprava', ' na {road}'],
				'TurnAround':
					['OtoÄte sa'],
				'SharpLeft':
					['Prudko doÄ¾ava', ' na {road}'],
				'Left':
					['DoÄ¾ava', ' na {road}'],
				'SlightLeft':
					['Mierne doÄ¾ava', ' na {road}'],
				'WaypointReached':
					['Ste v prejazdovom bode.'],
				'Roundabout':
					['OdboÄte na {exitStr} vÃ½jazde', ' na {road}'],
				'DestinationReached':
					['PriÅ¡li ste do cieÄ¾a.'],
			},
			formatOrder: function(n) {
				var i = n % 10 - 1,
				suffix = ['.', '.', '.'];

				return suffix[i] ? n + suffix[i] : n + '.';
			},
			ui: {
				startPlaceholder: 'ZaÄiatok',
				viaPlaceholder: 'Cez {viaNumber}',
				endPlaceholder: 'Koniec'
			}
		},
		'el': {
			directions: {
				N: 'Î²ÏŒÏÎµÎ¹Î±',
				NE: 'Î²Î¿ÏÎµÎ¹Î¿Î±Î½Î±Ï„Î¿Î»Î¹ÎºÎ¬',
				E: 'Î±Î½Î±Ï„Î¿Î»Î¹ÎºÎ¬',
				SE: 'Î½Î¿Ï„Î¹Î¿Î±Î½Î±Ï„Î¿Î»Î¹ÎºÎ¬',
				S: 'Î½ÏŒÏ„Î¹Î±',
				SW: 'Î½Î¿Ï„Î¹Î¿Î´Ï…Ï„Î¹ÎºÎ¬',
				W: 'Î´Ï…Ï„Î¹ÎºÎ¬',
				NW: 'Î²Î¿ÏÎµÎ¹Î¿Î´Ï…Ï„Î¹ÎºÎ¬'
			},
			instructions: {
				// instruction, postfix if the road is named
				'Head':
					['ÎšÎ±Ï„ÎµÏ…Î¸Ï…Î½Î¸ÎµÎ¯Ï„Îµ {dir}', ' ÏƒÏ„Î·Î½ {road}'],
				'Continue':
					['Î£Ï…Î½ÎµÏ‡Î¯ÏƒÏ„Îµ {dir}', ' ÏƒÏ„Î·Î½ {road}'],
				'SlightRight':
					['Î•Î»Î±Ï†ÏÏŽÏ‚ Î´ÎµÎ¾Î¹Î¬', ' ÏƒÏ„Î·Î½ {road}'],
				'Right':
					['Î”ÎµÎ¾Î¹Î¬', ' ÏƒÏ„Î·Î½ {road}'],
				'SharpRight':
					['Î‘Ï€ÏŒÏ„Î¿Î¼Î· Î´ÎµÎ¾Î¹Î¬ ÏƒÏ„ÏÎ¿Ï†Î®', ' ÏƒÏ„Î·Î½ {road}'],
				'TurnAround':
					['ÎšÎ¬Î½Ï„Îµ Î±Î½Î±ÏƒÏ„ÏÎ¿Ï†Î®'],
				'SharpLeft':
					['Î‘Ï€ÏŒÏ„Î¿Î¼Î· Î±ÏÎ¹ÏƒÏ„ÎµÏÎ® ÏƒÏ„ÏÎ¿Ï†Î®', ' ÏƒÏ„Î·Î½ {road}'],
				'Left':
					['Î‘ÏÎ¹ÏƒÏ„ÎµÏÎ¬', ' ÏƒÏ„Î·Î½ {road}'],
				'SlightLeft':
					['Î•Î»Î±Ï†ÏÏŽÏ‚ Î±ÏÎ¹ÏƒÏ„ÎµÏÎ¬', ' ÏƒÏ„Î·Î½ {road}'],
				'WaypointReached':
					['Î¦Ï„Î¬ÏƒÎ±Ï„Îµ ÏƒÏ„Î¿ ÏƒÎ·Î¼ÎµÎ¯Î¿ Î±Î½Î±Ï†Î¿ÏÎ¬Ï‚'],
				'Roundabout':
					['Î‘ÎºÎ¿Î»Î¿Ï…Î¸Î®ÏƒÏ„Îµ Ï„Î·Î½ {exitStr} Î­Î¾Î¿Î´Î¿ ÏƒÏ„Î¿ ÎºÏ…ÎºÎ»Î¹ÎºÏŒ ÎºÏŒÎ¼Î²Î¿', ' ÏƒÏ„Î·Î½ {road}'],
				'DestinationReached':
					['Î¦Ï„Î¬ÏƒÎ±Ï„Îµ ÏƒÏ„Î¿Î½ Ï€ÏÎ¿Î¿ÏÎ¹ÏƒÎ¼ÏŒ ÏƒÎ±Ï‚'],
			},
			formatOrder: function(n) {
				return n + 'Âº';
			},
			ui: {
				startPlaceholder: 'Î‘Ï†ÎµÏ„Î·ÏÎ¯Î±',
				viaPlaceholder: 'Î¼Î­ÏƒÏ‰ {viaNumber}',
				endPlaceholder: 'Î ÏÎ¿Î¿ÏÎ¹ÏƒÎ¼ÏŒÏ‚'
			}
		},
		'ca': {
			directions: {
				N: 'nord',
				NE: 'nord-est',
				E: 'est',
				SE: 'sud-est',
				S: 'sud',
				SW: 'sud-oest',
				W: 'oest',
				NW: 'nord-oest',
				SlightRight: 'lleu gir a la dreta',
				Right: 'dreta',
				SharpRight: 'gir pronunciat a la dreta',
				SlightLeft: 'gir pronunciat a l\'esquerra',
				Left: 'esquerra',
				SharpLeft: 'lleu gir a l\'esquerra',
				Uturn: 'mitja volta'
			},
			instructions: {
				'Head':
					['Recte {dir}', ' sobre {road}'],
				'Continue':
					['Continuar {dir}'],
				'TurnAround':
					['Donar la volta'],
				'WaypointReached':
					['Ha arribat a un punt del camÃ­'],
				'Roundabout':
					['Agafar {exitStr} sortida a la rotonda', ' a {road}'],
				'DestinationReached':
					['Arribada al destÃ­'],
				'Fork': ['A la cruÃ¯lla gira a la {modifier}', ' cap a {road}'],
				'Merge': ['Incorpora\'t {modifier}', ' a {road}'],
				'OnRamp': ['Gira {modifier} a la sortida', ' cap a {road}'],
				'OffRamp': ['Pren la sortida {modifier}', ' cap a {road}'],
				'EndOfRoad': ['Gira {modifier} al final de la carretera', ' cap a {road}'],
				'Onto': 'cap a {road}'
			},
			formatOrder: function(n) {
				return n + 'Âº';
			},
			ui: {
				startPlaceholder: 'Origen',
				viaPlaceholder: 'Via {viaNumber}',
				endPlaceholder: 'DestÃ­'
			},
			units: {
				meters: 'm',
				kilometers: 'km',
				yards: 'yd',
				miles: 'mi',
				hours: 'h',
				minutes: 'min',
				seconds: 's'
			}
		},
		'ru': {
			directions: {
				N: 'ÑÐµÐ²ÐµÑ€',
				NE: 'ÑÐµÐ²ÐµÑ€Ð¾Ð²Ð¾ÑÑ‚Ð¾Ðº',
				E: 'Ð²Ð¾ÑÑ‚Ð¾Ðº',
				SE: 'ÑŽÐ³Ð¾Ð²Ð¾ÑÑ‚Ð¾Ðº',
				S: 'ÑŽÐ³',
				SW: 'ÑŽÐ³Ð¾Ð·Ð°Ð¿Ð°Ð´',
				W: 'Ð·Ð°Ð¿Ð°Ð´',
				NW: 'ÑÐµÐ²ÐµÑ€Ð¾Ð·Ð°Ð¿Ð°Ð´',
				SlightRight: 'Ð¿Ð»Ð°Ð²Ð½Ð¾ Ð½Ð°Ð¿Ñ€Ð°Ð²Ð¾',
				Right: 'Ð½Ð°Ð¿Ñ€Ð°Ð²Ð¾',
				SharpRight: 'Ñ€ÐµÐ·ÐºÐ¾ Ð½Ð°Ð¿Ñ€Ð°Ð²Ð¾',
				SlightLeft: 'Ð¿Ð»Ð°Ð²Ð½Ð¾ Ð½Ð°Ð»ÐµÐ²Ð¾',
				Left: 'Ð½Ð°Ð»ÐµÐ²Ð¾',
				SharpLeft: 'Ñ€ÐµÐ·ÐºÐ¾ Ð½Ð°Ð»ÐµÐ²Ð¾',
				Uturn: 'Ñ€Ð°Ð·Ð²ÐµÑ€Ð½ÑƒÑ‚ÑŒÑÑ'
			},
			instructions: {
				'Head':
					['ÐÐ°Ñ‡Ð°Ñ‚ÑŒ Ð´Ð²Ð¸Ð¶ÐµÐ½Ð¸Ðµ Ð½Ð° {dir}', ' Ð¿Ð¾ {road}'],
				'Continue':
					['ÐŸÑ€Ð¾Ð´Ð¾Ð»Ð¶Ð°Ñ‚ÑŒ Ð´Ð²Ð¸Ð¶ÐµÐ½Ð¸Ðµ Ð½Ð° {dir}', ' Ð¿Ð¾ {road}'],
				'SlightRight':
					['ÐŸÐ»Ð°Ð²Ð½Ñ‹Ð¹ Ð¿Ð¾Ð²Ð¾Ñ€Ð¾Ñ‚ Ð½Ð°Ð¿Ñ€Ð°Ð²Ð¾', ' Ð½Ð° {road}'],
				'Right':
					['ÐÐ°Ð¿Ñ€Ð°Ð²Ð¾', ' Ð½Ð° {road}'],
				'SharpRight':
					['Ð ÐµÐ·ÐºÐ¸Ð¹ Ð¿Ð¾Ð²Ð¾Ñ€Ð¾Ñ‚ Ð½Ð°Ð¿Ñ€Ð°Ð²Ð¾', ' Ð½Ð° {road}'],
				'TurnAround':
					['Ð Ð°Ð·Ð²ÐµÑ€Ð½ÑƒÑ‚ÑŒÑÑ'],
				'SharpLeft':
					['Ð ÐµÐ·ÐºÐ¸Ð¹ Ð¿Ð¾Ð²Ð¾Ñ€Ð¾Ñ‚ Ð½Ð°Ð»ÐµÐ²Ð¾', ' Ð½Ð° {road}'],
				'Left':
					['ÐŸÐ¾Ð²Ð¾Ñ€Ð¾Ñ‚ Ð½Ð°Ð»ÐµÐ²Ð¾', ' Ð½Ð° {road}'],
				'SlightLeft':
					['ÐŸÐ»Ð°Ð²Ð½Ñ‹Ð¹ Ð¿Ð¾Ð²Ð¾Ñ€Ð¾Ñ‚ Ð½Ð°Ð»ÐµÐ²Ð¾', ' Ð½Ð° {road}'],
				'WaypointReached':
					['Ð¢Ð¾Ñ‡ÐºÐ° Ð´Ð¾ÑÑ‚Ð¸Ð³Ð½ÑƒÑ‚Ð°'],
				'Roundabout':
					['{exitStr} ÑÑŠÐµÐ·Ð´ Ñ ÐºÐ¾Ð»ÑŒÑ†Ð°', ' Ð½Ð° {road}'],
				'DestinationReached':
					['ÐžÐºÐ¾Ð½Ñ‡Ð°Ð½Ð¸Ðµ Ð¼Ð°Ñ€ÑˆÑ€ÑƒÑ‚Ð°'],
				'Fork': ['ÐÐ° Ñ€Ð°Ð·Ð²Ð¸Ð»ÐºÐµ Ð¿Ð¾Ð²ÐµÑ€Ð½Ð¸Ñ‚Ðµ {modifier}', ' Ð½Ð° {road}'],
				'Merge': ['ÐŸÐµÑ€ÐµÑÑ‚Ñ€Ð¾Ð¹Ñ‚ÐµÑÑŒ {modifier}', ' Ð½Ð° {road}'],
				'OnRamp': ['ÐŸÐ¾Ð²ÐµÑ€Ð½Ð¸Ñ‚Ðµ {modifier} Ð½Ð° ÑÑŠÐµÐ·Ð´', ' Ð½Ð° {road}'],
				'OffRamp': ['Ð¡ÑŠÐµÐ·Ð¶Ð°Ð¹Ñ‚Ðµ Ð½Ð° {modifier}', ' Ð½Ð° {road}'],
				'EndOfRoad': ['ÐŸÐ¾Ð²ÐµÑ€Ð½Ð¸Ñ‚Ðµ {modifier} Ð² ÐºÐ¾Ð½Ñ†Ðµ Ð´Ð¾Ñ€Ð¾Ð³Ð¸', ' Ð½Ð° {road}'],
				'Onto': 'Ð½Ð° {road}'
			},
			formatOrder: function(n) {
				return n + '-Ð¹';
			},
			ui: {
				startPlaceholder: 'ÐÐ°Ñ‡Ð°Ð»Ð¾',
				viaPlaceholder: 'Ð§ÐµÑ€ÐµÐ· {viaNumber}',
				endPlaceholder: 'ÐšÐ¾Ð½ÐµÑ†'
			},
			units: {
				meters: 'Ð¼',
				kilometers: 'ÐºÐ¼',
				yards: 'ÑÑ€Ð´',
				miles: 'Ð¼Ð¸',
				hours: 'Ñ‡',
				minutes: 'Ð¼',
				seconds: 'Ñ'
			}
		}
	});
})();

},{}],25:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null),
		corslite = require('corslite'),
		polyline = require('polyline'),
		osrmTextInstructions = require('osrm-text-instructions');

	// Ignore camelcase naming for this file, since OSRM's API uses
	// underscores.
	/* jshint camelcase: false */

	var Waypoint = require('./waypoint');

	/**
	 * Works against OSRM's new API in version 5.0; this has
	 * the API version v1.
	 */
	module.exports = L.Class.extend({
		options: {
			serviceUrl: 'https://router.project-osrm.org/route/v1',
			profile: 'driving',
			timeout: 30 * 1000,
			routingOptions: {
				alternatives: true,
				steps: true
			},
			polylinePrecision: 5,
			useHints: true,
			suppressDemoServerWarning: false,
			language: 'en'
		},

		initialize: function(options) {
			L.Util.setOptions(this, options);
			this._hints = {
				locations: {}
			};

			if (!this.options.suppressDemoServerWarning &&
				this.options.serviceUrl.indexOf('//router.project-osrm.org') >= 0) {
				console.warn('You are using OSRM\'s demo server. ' +
					'Please note that it is **NOT SUITABLE FOR PRODUCTION USE**.\n' +
					'Refer to the demo server\'s usage policy: ' +
					'https://github.com/Project-OSRM/osrm-backend/wiki/Api-usage-policy\n\n' +
					'To change, set the serviceUrl option.\n\n' +
					'Please do not report issues with this server to neither ' +
					'Leaflet Routing Machine or OSRM - it\'s for\n' +
					'demo only, and will sometimes not be available, or work in ' +
					'unexpected ways.\n\n' +
					'Please set up your own OSRM server, or use a paid service ' +
					'provider for production.');
			}
		},

		route: function(waypoints, callback, context, options) {
			var timedOut = false,
				wps = [],
				url,
				timer,
				wp,
				i,
				xhr;

			options = L.extend({}, this.options.routingOptions, options);
			url = this.buildRouteUrl(waypoints, options);
			if (this.options.requestParameters) {
				url += L.Util.getParamString(this.options.requestParameters, url);
			}

			timer = setTimeout(function() {
				timedOut = true;
				callback.call(context || callback, {
					status: -1,
					message: 'OSRM request timed out.'
				});
			}, this.options.timeout);

			// Create a copy of the waypoints, since they
			// might otherwise be asynchronously modified while
			// the request is being processed.
			for (i = 0; i < waypoints.length; i++) {
				wp = waypoints[i];
				wps.push(new Waypoint(wp.latLng, wp.name, wp.options));
			}

			return xhr = corslite(url, L.bind(function(err, resp) {
				var data,
					error =  {};

				clearTimeout(timer);
				if (!timedOut) {
					if (!err) {
						try {
							data = JSON.parse(resp.responseText);
							try {
								return this._routeDone(data, wps, options, callback, context);
							} catch (ex) {
								error.status = -3;
								error.message = ex.toString();
							}
						} catch (ex) {
							error.status = -2;
							error.message = 'Error parsing OSRM response: ' + ex.toString();
						}
					} else {
						error.message = 'HTTP request failed: ' + err.type +
							(err.target && err.target.status ? ' HTTP ' + err.target.status + ': ' + err.target.statusText : '');
						error.url = url;
						error.status = -1;
						error.target = err;
					}

					callback.call(context || callback, error);
				} else {
					xhr.abort();
				}
			}, this));
		},

		requiresMoreDetail: function(route, zoom, bounds) {
			if (!route.properties.isSimplified) {
				return false;
			}

			var waypoints = route.inputWaypoints,
				i;
			for (i = 0; i < waypoints.length; ++i) {
				if (!bounds.contains(waypoints[i].latLng)) {
					return true;
				}
			}

			return false;
		},

		_routeDone: function(response, inputWaypoints, options, callback, context) {
			var alts = [],
			    actualWaypoints,
			    i,
			    route;

			context = context || callback;
			if (response.code !== 'Ok') {
				callback.call(context, {
					status: response.code
				});
				return;
			}

			actualWaypoints = this._toWaypoints(inputWaypoints, response.waypoints);

			for (i = 0; i < response.routes.length; i++) {
				route = this._convertRoute(response.routes[i]);
				route.inputWaypoints = inputWaypoints;
				route.waypoints = actualWaypoints;
				route.properties = {isSimplified: !options || !options.geometryOnly || options.simplifyGeometry};
				alts.push(route);
			}

			this._saveHintData(response.waypoints, inputWaypoints);

			callback.call(context, null, alts);
		},

		_convertRoute: function(responseRoute) {
			var result = {
					name: '',
					coordinates: [],
					instructions: [],
					summary: {
						totalDistance: responseRoute.distance,
						totalTime: responseRoute.duration
					}
				},
				legNames = [],
				waypointIndices = [],
				index = 0,
				legCount = responseRoute.legs.length,
				hasSteps = responseRoute.legs[0].steps.length > 0,
				i,
				j,
				leg,
				step,
				geometry,
				type,
				modifier,
				text,
				stepToText;

			if (this.options.stepToText) {
				stepToText = this.options.stepToText;
			} else {
				var textInstructions = osrmTextInstructions('v5', this.options.language);
				stepToText = textInstructions.compile.bind(textInstructions);
			}

			for (i = 0; i < legCount; i++) {
				leg = responseRoute.legs[i];
				legNames.push(leg.summary && leg.summary.charAt(0).toUpperCase() + leg.summary.substring(1));
				for (j = 0; j < leg.steps.length; j++) {
					step = leg.steps[j];
					geometry = this._decodePolyline(step.geometry);
					result.coordinates.push.apply(result.coordinates, geometry);
					type = this._maneuverToInstructionType(step.maneuver, i === legCount - 1);
					modifier = this._maneuverToModifier(step.maneuver);
					text = stepToText(step);

					if (type) {
						if ((i == 0 && step.maneuver.type == 'depart') || step.maneuver.type == 'arrive') {
							waypointIndices.push(index);
						}

						result.instructions.push({
							type: type,
							distance: step.distance,
							time: step.duration,
							road: step.name,
							direction: this._bearingToDirection(step.maneuver.bearing_after),
							exit: step.maneuver.exit,
							index: index,
							mode: step.mode,
							modifier: modifier,
							text: text
						});
					}

					index += geometry.length;
				}
			}

			result.name = legNames.join(', ');
			if (!hasSteps) {
				result.coordinates = this._decodePolyline(responseRoute.geometry);
			} else {
				result.waypointIndices = waypointIndices;
			}

			return result;
		},

		_bearingToDirection: function(bearing) {
			var oct = Math.round(bearing / 45) % 8;
			return ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][oct];
		},

		_maneuverToInstructionType: function(maneuver, lastLeg) {
			switch (maneuver.type) {
			case 'new name':
				return 'Continue';
			case 'depart':
				return 'Head';
			case 'arrive':
				return lastLeg ? 'DestinationReached' : 'WaypointReached';
			case 'roundabout':
			case 'rotary':
				return 'Roundabout';
			case 'merge':
			case 'fork':
			case 'on ramp':
			case 'off ramp':
			case 'end of road':
				return this._camelCase(maneuver.type);
			// These are all reduced to the same instruction in the current model
			//case 'turn':
			//case 'ramp': // deprecated in v5.1
			default:
				return this._camelCase(maneuver.modifier);
			}
		},

		_maneuverToModifier: function(maneuver) {
			var modifier = maneuver.modifier;

			switch (maneuver.type) {
			case 'merge':
			case 'fork':
			case 'on ramp':
			case 'off ramp':
			case 'end of road':
				modifier = this._leftOrRight(modifier);
			}

			return modifier && this._camelCase(modifier);
		},

		_camelCase: function(s) {
			var words = s.split(' '),
				result = '';
			for (var i = 0, l = words.length; i < l; i++) {
				result += words[i].charAt(0).toUpperCase() + words[i].substring(1);
			}

			return result;
		},

		_leftOrRight: function(d) {
			return d.indexOf('left') >= 0 ? 'Left' : 'Right';
		},

		_decodePolyline: function(routeGeometry) {
			var cs = polyline.decode(routeGeometry, this.options.polylinePrecision),
				result = new Array(cs.length),
				i;
			for (i = cs.length - 1; i >= 0; i--) {
				result[i] = L.latLng(cs[i]);
			}

			return result;
		},

		_toWaypoints: function(inputWaypoints, vias) {
			var wps = [],
			    i,
			    viaLoc;
			for (i = 0; i < vias.length; i++) {
				viaLoc = vias[i].location;
				wps.push(new Waypoint(L.latLng(viaLoc[1], viaLoc[0]),
				                            inputWaypoints[i].name,
											inputWaypoints[i].options));
			}

			return wps;
		},

		buildRouteUrl: function(waypoints, options) {
			var locs = [],
				hints = [],
				wp,
				latLng,
			    computeInstructions,
			    computeAlternative = true;

			for (var i = 0; i < waypoints.length; i++) {
				wp = waypoints[i];
				latLng = wp.latLng;
				locs.push(latLng.lng + ',' + latLng.lat);
				hints.push(this._hints.locations[this._locationKey(latLng)] || '');
			}

			computeInstructions =
				true;

			return this.options.serviceUrl + '/' + this.options.profile + '/' +
				locs.join(';') + '?' +
				(options.geometryOnly ? (options.simplifyGeometry ? '' : 'overview=full') : 'overview=false') +
				'&alternatives=' + computeAlternative.toString() +
				'&steps=' + computeInstructions.toString() +
				(this.options.useHints ? '&hints=' + hints.join(';') : '') +
				(options.allowUTurns ? '&continue_straight=' + !options.allowUTurns : '');
		},

		_locationKey: function(location) {
			return location.lat + ',' + location.lng;
		},

		_saveHintData: function(actualWaypoints, waypoints) {
			var loc;
			this._hints = {
				locations: {}
			};
			for (var i = actualWaypoints.length - 1; i >= 0; i--) {
				loc = waypoints[i].latLng;
				this._hints.locations[this._locationKey(loc)] = actualWaypoints[i].hint;
			}
		},
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./waypoint":27,"corslite":1,"osrm-text-instructions":9,"polyline":36}],26:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
	var GeocoderElement = require('./geocoder-element');
	var Waypoint = require('./waypoint');

	module.exports = (L.Layer || L.Class).extend({
		includes: L.Mixin.Events,

		options: {
			dragStyles: [
				{color: 'black', opacity: 0.15, weight: 9},
				{color: 'white', opacity: 0.8, weight: 6},
				{color: 'red', opacity: 1, weight: 2, dashArray: '7,12'}
			],
			draggableWaypoints: true,
			routeWhileDragging: false,
			addWaypoints: true,
			reverseWaypoints: false,
			addButtonClassName: '',
			language: 'en',
			createGeocoderElement: function(wp, i, nWps, plan) {
				return new GeocoderElement(wp, i, nWps, plan);
			},
			createMarker: function(i, wp) {
				var options = {
						draggable: this.draggableWaypoints
					},
				    marker = L.marker(wp.latLng, options);

				return marker;
			},
			geocodersClassName: ''
		},

		initialize: function(waypoints, options) {
			L.Util.setOptions(this, options);
			this._waypoints = [];
			this.setWaypoints(waypoints);
		},

		isReady: function() {
			var i;
			for (i = 0; i < this._waypoints.length; i++) {
				if (!this._waypoints[i].latLng) {
					return false;
				}
			}

			return true;
		},

		getWaypoints: function() {
			var i,
				wps = [];

			for (i = 0; i < this._waypoints.length; i++) {
				wps.push(this._waypoints[i]);
			}

			return wps;
		},

		setWaypoints: function(waypoints) {
			var args = [0, this._waypoints.length].concat(waypoints);
			this.spliceWaypoints.apply(this, args);
			return this;
		},

		spliceWaypoints: function() {
			var args = [arguments[0], arguments[1]],
			    i;

			for (i = 2; i < arguments.length; i++) {
				args.push(arguments[i] && arguments[i].hasOwnProperty('latLng') ? arguments[i] : new Waypoint(arguments[i]));
			}

			[].splice.apply(this._waypoints, args);

			// Make sure there's always at least two waypoints
			while (this._waypoints.length < 2) {
				this.spliceWaypoints(this._waypoints.length, 0, null);
			}

			this._updateMarkers();
			this._fireChanged.apply(this, args);
		},

		onAdd: function(map) {
			this._map = map;
			this._updateMarkers();
		},

		onRemove: function() {
			var i;
			this._removeMarkers();

			if (this._newWp) {
				for (i = 0; i < this._newWp.lines.length; i++) {
					this._map.removeLayer(this._newWp.lines[i]);
				}
			}

			delete this._map;
		},

		createGeocoders: function() {
			var container = L.DomUtil.create('div', 'leaflet-routing-geocoders ' + this.options.geocodersClassName),
				waypoints = this._waypoints,
			    addWpBtn,
			    reverseBtn;

			this._geocoderContainer = container;
			this._geocoderElems = [];


			if (this.options.addWaypoints) {
				addWpBtn = L.DomUtil.create('button', 'leaflet-routing-add-waypoint ' + this.options.addButtonClassName, container);
				addWpBtn.setAttribute('type', 'button');
				L.DomEvent.addListener(addWpBtn, 'click', function() {
					this.spliceWaypoints(waypoints.length, 0, null);
				}, this);
			}

			if (this.options.reverseWaypoints) {
				reverseBtn = L.DomUtil.create('button', 'leaflet-routing-reverse-waypoints', container);
				reverseBtn.setAttribute('type', 'button');
				L.DomEvent.addListener(reverseBtn, 'click', function() {
					this._waypoints.reverse();
					this.setWaypoints(this._waypoints);
				}, this);
			}

			this._updateGeocoders();
			this.on('waypointsspliced', this._updateGeocoders);

			return container;
		},

		_createGeocoder: function(i) {
			var geocoder = this.options.createGeocoderElement(this._waypoints[i], i, this._waypoints.length, this.options);
			geocoder
			.on('delete', function() {
				if (i > 0 || this._waypoints.length > 2) {
					this.spliceWaypoints(i, 1);
				} else {
					this.spliceWaypoints(i, 1, new Waypoint());
				}
			}, this)
			.on('geocoded', function(e) {
				this._updateMarkers();
				this._fireChanged();
				this._focusGeocoder(i + 1);
				this.fire('waypointgeocoded', {
					waypointIndex: i,
					waypoint: e.waypoint
				});
			}, this)
			.on('reversegeocoded', function(e) {
				this.fire('waypointgeocoded', {
					waypointIndex: i,
					waypoint: e.waypoint
				});
			}, this);

			return geocoder;
		},

		_updateGeocoders: function() {
			var elems = [],
				i,
			    geocoderElem;

			for (i = 0; i < this._geocoderElems.length; i++) {
				this._geocoderContainer.removeChild(this._geocoderElems[i].getContainer());
			}

			for (i = this._waypoints.length - 1; i >= 0; i--) {
				geocoderElem = this._createGeocoder(i);
				this._geocoderContainer.insertBefore(geocoderElem.getContainer(), this._geocoderContainer.firstChild);
				elems.push(geocoderElem);
			}

			this._geocoderElems = elems.reverse();
		},

		_removeMarkers: function() {
			var i;
			if (this._markers) {
				for (i = 0; i < this._markers.length; i++) {
					if (this._markers[i]) {
						this._map.removeLayer(this._markers[i]);
					}
				}
			}
			this._markers = [];
		},

		_updateMarkers: function() {
			var i,
			    m;

			if (!this._map) {
				return;
			}

			this._removeMarkers();

			for (i = 0; i < this._waypoints.length; i++) {
				if (this._waypoints[i].latLng) {
					m = this.options.createMarker(i, this._waypoints[i], this._waypoints.length);
					if (m) {
						m.addTo(this._map);
						if (this.options.draggableWaypoints) {
							this._hookWaypointEvents(m, i);
						}
					}
				} else {
					m = null;
				}
				this._markers.push(m);
			}
		},

		_fireChanged: function() {
			this.fire('waypointschanged', {waypoints: this.getWaypoints()});

			if (arguments.length >= 2) {
				this.fire('waypointsspliced', {
					index: Array.prototype.shift.call(arguments),
					nRemoved: Array.prototype.shift.call(arguments),
					added: arguments
				});
			}
		},

		_hookWaypointEvents: function(m, i, trackMouseMove) {
			var eventLatLng = function(e) {
					return trackMouseMove ? e.latlng : e.target.getLatLng();
				},
				dragStart = L.bind(function(e) {
					this.fire('waypointdragstart', {index: i, latlng: eventLatLng(e)});
				}, this),
				drag = L.bind(function(e) {
					this._waypoints[i].latLng = eventLatLng(e);
					this.fire('waypointdrag', {index: i, latlng: eventLatLng(e)});
				}, this),
				dragEnd = L.bind(function(e) {
					this._waypoints[i].latLng = eventLatLng(e);
					this._waypoints[i].name = '';
					if (this._geocoderElems) {
						this._geocoderElems[i].update(true);
					}
					this.fire('waypointdragend', {index: i, latlng: eventLatLng(e)});
					this._fireChanged();
				}, this),
				mouseMove,
				mouseUp;

			if (trackMouseMove) {
				mouseMove = L.bind(function(e) {
					this._markers[i].setLatLng(e.latlng);
					drag(e);
				}, this);
				mouseUp = L.bind(function(e) {
					this._map.dragging.enable();
					this._map.off('mouseup', mouseUp);
					this._map.off('mousemove', mouseMove);
					dragEnd(e);
				}, this);
				this._map.dragging.disable();
				this._map.on('mousemove', mouseMove);
				this._map.on('mouseup', mouseUp);
				dragStart({latlng: this._waypoints[i].latLng});
			} else {
				m.on('dragstart', dragStart);
				m.on('drag', drag);
				m.on('dragend', dragEnd);
			}
		},

		dragNewWaypoint: function(e) {
			var newWpIndex = e.afterIndex + 1;
			if (this.options.routeWhileDragging) {
				this.spliceWaypoints(newWpIndex, 0, e.latlng);
				this._hookWaypointEvents(this._markers[newWpIndex], newWpIndex, true);
			} else {
				this._dragNewWaypoint(newWpIndex, e.latlng);
			}
		},

		_dragNewWaypoint: function(newWpIndex, initialLatLng) {
			var wp = new Waypoint(initialLatLng),
				prevWp = this._waypoints[newWpIndex - 1],
				nextWp = this._waypoints[newWpIndex],
				marker = this.options.createMarker(newWpIndex, wp, this._waypoints.length + 1),
				lines = [],
				draggingEnabled = this._map.dragging.enabled(),
				mouseMove = L.bind(function(e) {
					var i,
						latLngs;
					if (marker) {
						marker.setLatLng(e.latlng);
					}
					for (i = 0; i < lines.length; i++) {
						latLngs = lines[i].getLatLngs();
						latLngs.splice(1, 1, e.latlng);
						lines[i].setLatLngs(latLngs);
					}

					L.DomEvent.stop(e);
				}, this),
				mouseUp = L.bind(function(e) {
					var i;
					if (marker) {
						this._map.removeLayer(marker);
					}
					for (i = 0; i < lines.length; i++) {
						this._map.removeLayer(lines[i]);
					}
					this._map.off('mousemove', mouseMove);
					this._map.off('mouseup', mouseUp);
					this.spliceWaypoints(newWpIndex, 0, e.latlng);
					if (draggingEnabled) {
						this._map.dragging.enable();
					}
				}, this),
				i;

			if (marker) {
				marker.addTo(this._map);
			}

			for (i = 0; i < this.options.dragStyles.length; i++) {
				lines.push(L.polyline([prevWp.latLng, initialLatLng, nextWp.latLng],
					this.options.dragStyles[i]).addTo(this._map));
			}

			if (draggingEnabled) {
				this._map.dragging.disable();
			}

			this._map.on('mousemove', mouseMove);
			this._map.on('mouseup', mouseUp);
		},

		_focusGeocoder: function(i) {
			if (this._geocoderElems[i]) {
				this._geocoderElems[i].focus();
			} else {
				document.activeElement.blur();
			}
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./geocoder-element":20,"./waypoint":27}],27:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	module.exports = L.Class.extend({
		options: {
			allowUTurn: false,
		},
		initialize: function(latLng, name, options) {
			L.Util.setOptions(this, options);
			this.latLng = L.latLng(latLng);
			this.name = name;
		}
	});
})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],28:[function(require,module,exports){
(function (global){
/*!
Copyright (c) 2016 Dominik Moritz

This file is part of the leaflet locate control. It is licensed under the MIT license.
You can find the project at: https://github.com/domoritz/leaflet-locatecontrol
*/
(function (factory, window) {
     // see https://github.com/Leaflet/Leaflet/blob/master/PLUGIN-GUIDE.md#module-loaders
     // for details on how to structure a leaflet plugin.

    // define an AMD module that relies on 'leaflet'
    if (typeof define === 'function' && define.amd) {
        define(['leaflet'], factory);

    // define a Common JS module that relies on 'leaflet'
    } else if (typeof exports === 'object') {
        if (typeof window !== 'undefined' && window.L) {
            module.exports = factory(L);
        } else {
            module.exports = factory((typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null));
        }
    }

    // attach your plugin to the global 'L' variable
    if (typeof window !== 'undefined' && window.L){
        window.L.Control.Locate = factory(L);
    }
} (function (L) {
    var LocateControl = L.Control.extend({
        options: {
            /** Position of the control */
            position: 'topleft',
            /** The layer that the user's location should be drawn on. By default creates a new layer. */
            layer: undefined,
            /**
             * Automatically sets the map view (zoom and pan) to the user's location as it updates.
             * While the map is following the user's location, the control is in the `following` state,
             * which changes the style of the control and the circle marker.
             *
             * Possible values:
             *  - false: never updates the map view when location changes.
             *  - 'once': set the view when the location is first determined
             *  - 'always': always updates the map view when location changes.
             *              The map view follows the users location.
             *  - 'untilPan': (default) like 'always', except stops updating the
             *                view if the user has manually panned the map.
             *                The map view follows the users location until she pans.
             */
            setView: 'untilPan',
            /** Keep the current map zoom level when setting the view and only pan. */
            keepCurrentZoomLevel: false,
            /** Smooth pan and zoom to the location of the marker. Only works in Leaflet 1.0+. */
            flyTo: false,
            /**
             * The user location can be inside and outside the current view when the user clicks on the
             * control that is already active. Both cases can be configures separately.
             * Possible values are:
             *  - 'setView': zoom and pan to the current location
             *  - 'stop': stop locating and remove the location marker
             */
            clickBehavior: {
                /** What should happen if the user clicks on the control while the location is within the current view. */
                inView: 'stop',
                /** What should happen if the user clicks on the control while the location is outside the current view. */
                outOfView: 'setView',
            },
            /**
             * If set, save the map bounds just before centering to the user's
             * location. When control is disabled, set the view back to the
             * bounds that were saved.
             */
            returnToPrevBounds: false,
            /**
             * Keep a cache of the location after the user deactivates the control. If set to false, the user has to wait
             * until the locate API returns a new location before they see where they are again.
             */
            cacheLocation: true,
            /** If set, a circle that shows the location accuracy is drawn. */
            drawCircle: true,
            /** If set, the marker at the users' location is drawn. */
            drawMarker: true,
            /** The class to be used to create the marker. For example L.CircleMarker or L.Marker */
            markerClass: L.CircleMarker,
            /** Accuracy circle style properties. */
            circleStyle: {
                color: '#136AEC',
                fillColor: '#136AEC',
                fillOpacity: 0.15,
                weight: 2,
                opacity: 0.5
            },
            /** Inner marker style properties. Only works if your marker class supports `setStyle`. */
            markerStyle: {
                color: '#136AEC',
                fillColor: '#2A93EE',
                fillOpacity: 0.7,
                weight: 2,
                opacity: 0.9,
                radius: 5
            },
            /**
             * Changes to accuracy circle and inner marker while following.
             * It is only necessary to provide the properties that should change.
             */
            followCircleStyle: {},
            followMarkerStyle: {
                // color: '#FFA500',
                // fillColor: '#FFB000'
            },
            /** The CSS class for the icon. For example fa-location-arrow or fa-map-marker */
            icon: 'fa fa-map-marker',
            iconLoading: 'fa fa-spinner fa-spin',
            /** The element to be created for icons. For example span or i */
            iconElementTag: 'span',
            /** Padding around the accuracy circle. */
            circlePadding: [0, 0],
            /** Use metric units. */
            metric: true,
            /** This event is called in case of any location error that is not a time out error. */
            onLocationError: function(err, control) {
                alert(err.message);
            },
            /**
             * This even is called when the user's location is outside the bounds set on the map.
             * The event is called repeatedly when the location changes.
             */
            onLocationOutsideMapBounds: function(control) {
                control.stop();
                alert(control.options.strings.outsideMapBoundsMsg);
            },
            /** Display a pop-up when the user click on the inner marker. */
            showPopup: true,
            strings: {
                title: "Show me where I am",
                metersUnit: "meters",
                feetUnit: "feet",
                popup: "You are within {distance} {unit} from this point",
                outsideMapBoundsMsg: "You seem located outside the boundaries of the map"
            },
            /** The default options passed to leaflets locate method. */
            locateOptions: {
                maxZoom: Infinity,
                watch: true,  // if you overwrite this, visualization cannot be updated
                setView: false // have to set this to false because we have to
                               // do setView manually
            }
        },

        initialize: function (options) {
            // set default options if nothing is set (merge one step deep)
            for (var i in options) {
                if (typeof this.options[i] === 'object') {
                    L.extend(this.options[i], options[i]);
                } else {
                    this.options[i] = options[i];
                }
            }

            // extend the follow marker style and circle from the normal style
            this.options.followMarkerStyle = L.extend({}, this.options.markerStyle, this.options.followMarkerStyle);
            this.options.followCircleStyle = L.extend({}, this.options.circleStyle, this.options.followCircleStyle);
        },

        /**
         * Add control to map. Returns the container for the control.
         */
        onAdd: function (map) {
            var container = L.DomUtil.create('div',
                'leaflet-control-locate leaflet-bar leaflet-control');

            this._layer = this.options.layer || new L.LayerGroup();
            this._layer.addTo(map);
            this._event = undefined;
            this._prevBounds = null;

            this._link = L.DomUtil.create('a', 'leaflet-bar-part leaflet-bar-part-single', container);
            this._link.title = this.options.strings.title;
            this._icon = L.DomUtil.create(this.options.iconElementTag, this.options.icon, this._link);

            L.DomEvent
                .on(this._link, 'click', L.DomEvent.stopPropagation)
                .on(this._link, 'click', L.DomEvent.preventDefault)
                .on(this._link, 'click', this._onClick, this)
                .on(this._link, 'dblclick', L.DomEvent.stopPropagation);

            this._resetVariables();

            this._map.on('unload', this._unload, this);

            return container;
        },

        /**
         * This method is called when the user clicks on the control.
         */
        _onClick: function() {
            this._justClicked = true;
            this._userPanned = false;

            if (this._active && !this._event) {
                // click while requesting
                this.stop();
            } else if (this._active && this._event !== undefined) {
                var behavior = this._map.getBounds().contains(this._event.latlng) ?
                    this.options.clickBehavior.inView : this.options.clickBehavior.outOfView;
                switch (behavior) {
                    case 'setView':
                        this.setView();
                        break;
                    case 'stop':
                        this.stop();
                        if (this.options.returnToPrevBounds) {
                            var f = this.options.flyTo ? this._map.flyToBounds : this._map.fitBounds;
                            f.bind(this._map)(this._prevBounds);
                        }
                        break;
                }
            } else {
                if (this.options.returnToPrevBounds) {
                  this._prevBounds = this._map.getBounds();
                }
                this.start();
            }

            this._updateContainerStyle();
        },

        /**
         * Starts the plugin:
         * - activates the engine
         * - draws the marker (if coordinates available)
         */
        start: function() {
            this._activate();

            if (this._event) {
                this._drawMarker(this._map);

                // if we already have a location but the user clicked on the control
                if (this.options.setView) {
                    this.setView();
                }
            }
            this._updateContainerStyle();
        },

        /**
         * Stops the plugin:
         * - deactivates the engine
         * - reinitializes the button
         * - removes the marker
         */
        stop: function() {
            this._deactivate();

            this._cleanClasses();
            this._resetVariables();

            this._removeMarker();
        },

        /**
         * This method launches the location engine.
         * It is called before the marker is updated,
         * event if it does not mean that the event will be ready.
         *
         * Override it if you want to add more functionalities.
         * It should set the this._active to true and do nothing if
         * this._active is true.
         */
        _activate: function() {
            if (!this._active) {
                this._map.locate(this.options.locateOptions);
                this._active = true;

                // bind event listeners
                this._map.on('locationfound', this._onLocationFound, this);
                this._map.on('locationerror', this._onLocationError, this);
                this._map.on('dragstart', this._onDrag, this);
            }
        },

        /**
         * Called to stop the location engine.
         *
         * Override it to shutdown any functionalities you added on start.
         */
        _deactivate: function() {
            this._map.stopLocate();
            this._active = false;

            if (!this.options.cacheLocation) {
                this._event = undefined;
            }

            // unbind event listeners
            this._map.off('locationfound', this._onLocationFound, this);
            this._map.off('locationerror', this._onLocationError, this);
            this._map.off('dragstart', this._onDrag, this);
        },

        /**
         * Zoom (unless we should keep the zoom level) and an to the current view.
         */
        setView: function() {
            this._drawMarker();
            if (this._isOutsideMapBounds()) {
                this._event = undefined;  // clear the current location so we can get back into the bounds
                this.options.onLocationOutsideMapBounds(this);
            } else {
                if (this.options.keepCurrentZoomLevel) {
                    var f = this.options.flyTo ? this._map.flyTo : this._map.panTo;
                    f.bind(this._map)([this._event.latitude, this._event.longitude]);
                } else {
                    var f = this.options.flyTo ? this._map.flyToBounds : this._map.fitBounds;
                    f.bind(this._map)(this._event.bounds, {
                        padding: this.options.circlePadding,
                        maxZoom: this.options.locateOptions.maxZoom
                    });
                }
            }
        },

        /**
         * Draw the marker and accuracy circle on the map.
         *
         * Uses the event retrieved from onLocationFound from the map.
         */
        _drawMarker: function() {
            if (this._event.accuracy === undefined) {
                this._event.accuracy = 0;
            }

            var radius = this._event.accuracy;
            var latlng = this._event.latlng;

            // circle with the radius of the location's accuracy
            if (this.options.drawCircle) {
                var style = this._isFollowing() ? this.options.followCircleStyle : this.options.circleStyle;

                if (!this._circle) {
                    this._circle = L.circle(latlng, radius, style).addTo(this._layer);
                } else {
                    this._circle.setLatLng(latlng).setRadius(radius).setStyle(style);
                }
            }

            var distance, unit;
            if (this.options.metric) {
                distance = radius.toFixed(0);
                unit =  this.options.strings.metersUnit;
            } else {
                distance = (radius * 3.2808399).toFixed(0);
                unit = this.options.strings.feetUnit;
            }

            // small inner marker
            if (this.options.drawMarker) {
                var mStyle = this._isFollowing() ? this.options.followMarkerStyle : this.options.markerStyle;
                if (!this._marker) {
                    this._marker = new this.options.markerClass(latlng, mStyle).addTo(this._layer);
                } else {
                    this._marker.setLatLng(latlng);
                    // If the markerClass can be updated with setStyle, update it.
                    if (this._marker.setStyle) {
                        this._marker.setStyle(mStyle);
                    }
                }
            }

            var t = this.options.strings.popup;
            if (this.options.showPopup && t && this._marker) {
                this._marker
                    .bindPopup(L.Util.template(t, {distance: distance, unit: unit}))
                    ._popup.setLatLng(latlng);
            }
        },

        /**
         * Remove the marker from map.
         */
        _removeMarker: function() {
            this._layer.clearLayers();
            this._marker = undefined;
            this._circle = undefined;
        },

        /**
         * Unload the plugin and all event listeners.
         * Kind of the opposite of onAdd.
         */
        _unload: function() {
            this.stop();
            this._map.off('unload', this._unload, this);
        },

        /**
         * Calls deactivate and dispatches an error.
         */
        _onLocationError: function(err) {
            // ignore time out error if the location is watched
            if (err.code == 3 && this.options.locateOptions.watch) {
                return;
            }

            this.stop();
            this.options.onLocationError(err, this);
        },

        /**
         * Stores the received event and updates the marker.
         */
        _onLocationFound: function(e) {
            // no need to do anything if the location has not changed
            if (this._event &&
                (this._event.latlng.lat === e.latlng.lat &&
                 this._event.latlng.lng === e.latlng.lng &&
                     this._event.accuracy === e.accuracy)) {
                return;
            }

            if (!this._active) {
                // we may have a stray event
                return;
            }

            this._event = e;

            this._drawMarker();
            this._updateContainerStyle();

            switch (this.options.setView) {
                case 'once':
                    if (this._justClicked) {
                        this.setView();
                    }
                    break;
                case 'untilPan':
                    if (!this._userPanned) {
                        this.setView();
                    }
                    break;
                case 'always':
                    this.setView();
                    break;
                case false:
                    // don't set the view
                    break;
            }

            this._justClicked = false;
        },

        /**
         * When the user drags. Need a separate even so we can bind and unbind even listeners.
         */
        _onDrag: function() {
            // only react to drags once we have a location
            if (this._event) {
                this._userPanned = true;
                this._updateContainerStyle();
                this._drawMarker();
            }
        },

        /**
         * Compute whether the map is following the user location with pan and zoom.
         */
        _isFollowing: function() {
            if (!this._active) {
                return false;
            }

            if (this.options.setView === 'always') {
                return true;
            } else if (this.options.setView === 'untilPan') {
                return !this._userPanned;
            }
        },

        /**
         * Check if location is in map bounds
         */
        _isOutsideMapBounds: function() {
            if (this._event === undefined) {
                return false;
            }
            return this._map.options.maxBounds &&
                !this._map.options.maxBounds.contains(this._event.latlng);
        },

        /**
         * Toggles button class between following and active.
         */
        _updateContainerStyle: function() {
            if (!this._container) {
                return;
            }

            if (this._active && !this._event) {
                // active but don't have a location yet
                this._setClasses('requesting');
            } else if (this._isFollowing()) {
                this._setClasses('following');
            } else if (this._active) {
                this._setClasses('active');
            } else {
                this._cleanClasses();
            }
        },

        /**
         * Sets the CSS classes for the state.
         */
        _setClasses: function(state) {
            if (state == 'requesting') {
                L.DomUtil.removeClasses(this._container, "active following");
                L.DomUtil.addClasses(this._container, "requesting");

                L.DomUtil.removeClasses(this._icon, this.options.icon);
                L.DomUtil.addClasses(this._icon, this.options.iconLoading);
            } else if (state == 'active') {
                L.DomUtil.removeClasses(this._container, "requesting following");
                L.DomUtil.addClasses(this._container, "active");

                L.DomUtil.removeClasses(this._icon, this.options.iconLoading);
                L.DomUtil.addClasses(this._icon, this.options.icon);
            } else if (state == 'following') {
                L.DomUtil.removeClasses(this._container, "requesting");
                L.DomUtil.addClasses(this._container, "active following");

                L.DomUtil.removeClasses(this._icon, this.options.iconLoading);
                L.DomUtil.addClasses(this._icon, this.options.icon);
            }
        },

        /**
         * Removes all classes from button.
         */
        _cleanClasses: function() {
            L.DomUtil.removeClass(this._container, "requesting");
            L.DomUtil.removeClass(this._container, "active");
            L.DomUtil.removeClass(this._container, "following");

            L.DomUtil.removeClasses(this._icon, this.options.iconLoading);
            L.DomUtil.addClasses(this._icon, this.options.icon);
        },

        /**
         * Reinitializes state variables.
         */
        _resetVariables: function() {
            // whether locate is active or not
            this._active = false;

            // true if the control was clicked for the first time
            // we need this so we can pan and zoom once we have the location
            this._justClicked = false;

            // true if the user has panned the map after clicking the control
            this._userPanned = false;
        }
    });

    L.control.locate = function (options) {
        return new L.Control.Locate(options);
    };

    (function(){
      // leaflet.js raises bug when trying to addClass / removeClass multiple classes at once
      // Let's create a wrapper on it which fixes it.
      var LDomUtilApplyClassesMethod = function(method, element, classNames) {
        classNames = classNames.split(' ');
        classNames.forEach(function(className) {
            L.DomUtil[method].call(this, element, className);
        });
      };

      L.DomUtil.addClasses = function(el, names) { LDomUtilApplyClassesMethod('addClass', el, names); };
      L.DomUtil.removeClasses = function(el, names) { LDomUtilApplyClassesMethod('removeClass', el, names); };
    })();

    return LocateControl;
}, window));

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],29:[function(require,module,exports){
arguments[4][1][0].apply(exports,arguments)
},{"dup":1}],30:[function(require,module,exports){
'use strict';

/**
 * Based off of [the offical Google document](https://developers.google.com/maps/documentation/utilities/polylinealgorithm)
 *
 * Some parts from [this implementation](http://facstaff.unca.edu/mcmcclur/GoogleMaps/EncodePolyline/PolylineEncoder.js)
 * by [Mark McClure](http://facstaff.unca.edu/mcmcclur/)
 *
 * @module polyline
 */

var polyline = {};

function py2_round(value) {
    // Google's polyline algorithm uses the same rounding strategy as Python 2, which is different from JS for negative values
    return Math.floor(Math.abs(value) + 0.5) * Math.sign(value);
}

function encode(current, previous, factor) {
    current = py2_round(current * factor);
    previous = py2_round(previous * factor);
    var coordinate = current - previous;
    coordinate <<= 1;
    if (current - previous < 0) {
        coordinate = ~coordinate;
    }
    var output = '';
    while (coordinate >= 0x20) {
        output += String.fromCharCode((0x20 | (coordinate & 0x1f)) + 63);
        coordinate >>= 5;
    }
    output += String.fromCharCode(coordinate + 63);
    return output;
}

/**
 * Decodes to a [latitude, longitude] coordinates array.
 *
 * This is adapted from the implementation in Project-OSRM.
 *
 * @param {String} str
 * @param {Number} precision
 * @returns {Array}
 *
 * @see https://github.com/Project-OSRM/osrm-frontend/blob/master/WebContent/routing/OSRM.RoutingGeometry.js
 */
polyline.decode = function(str, precision) {
    var index = 0,
        lat = 0,
        lng = 0,
        coordinates = [],
        shift = 0,
        result = 0,
        byte = null,
        latitude_change,
        longitude_change,
        factor = Math.pow(10, precision || 5);

    // Coordinates have variable length when encoded, so just keep
    // track of whether we've hit the end of the string. In each
    // loop iteration, a single coordinate is decoded.
    while (index < str.length) {

        // Reset shift, result, and byte
        byte = null;
        shift = 0;
        result = 0;

        do {
            byte = str.charCodeAt(index++) - 63;
            result |= (byte & 0x1f) << shift;
            shift += 5;
        } while (byte >= 0x20);

        latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

        shift = result = 0;

        do {
            byte = str.charCodeAt(index++) - 63;
            result |= (byte & 0x1f) << shift;
            shift += 5;
        } while (byte >= 0x20);

        longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

        lat += latitude_change;
        lng += longitude_change;

        coordinates.push([lat / factor, lng / factor]);
    }

    return coordinates;
};

/**
 * Encodes the given [latitude, longitude] coordinates array.
 *
 * @param {Array.<Array.<Number>>} coordinates
 * @param {Number} precision
 * @returns {String}
 */
polyline.encode = function(coordinates, precision) {
    if (!coordinates.length) { return ''; }

    var factor = Math.pow(10, precision || 5),
        output = encode(coordinates[0][0], 0, factor) + encode(coordinates[0][1], 0, factor);

    for (var i = 1; i < coordinates.length; i++) {
        var a = coordinates[i], b = coordinates[i - 1];
        output += encode(a[0], b[0], factor);
        output += encode(a[1], b[1], factor);
    }

    return output;
};

function flipped(coords) {
    var flipped = [];
    for (var i = 0; i < coords.length; i++) {
        flipped.push(coords[i].slice().reverse());
    }
    return flipped;
}

/**
 * Encodes a GeoJSON LineString feature/geometry.
 *
 * @param {Object} geojson
 * @param {Number} precision
 * @returns {String}
 */
polyline.fromGeoJSON = function(geojson, precision) {
    if (geojson && geojson.type === 'Feature') {
        geojson = geojson.geometry;
    }
    if (!geojson || geojson.type !== 'LineString') {
        throw new Error('Input must be a GeoJSON LineString');
    }
    return polyline.encode(flipped(geojson.coordinates), precision);
};

/**
 * Decodes to a GeoJSON LineString geometry.
 *
 * @param {String} str
 * @param {Number} precision
 * @returns {Object}
 */
polyline.toGeoJSON = function(str, precision) {
    var coords = polyline.decode(str, precision);
    return {
        type: 'LineString',
        coordinates: flipped(coords)
    };
};

if (typeof module === 'object' && module.exports) {
    module.exports = polyline;
}

},{}],31:[function(require,module,exports){
(function (global){
(function() {
  'use strict';

  var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

  //L.extend(L.Routing, require('./L.Routing.Localization'));
  module.exports = L.Class.extend({
    options: {
      units: 'metric',
      unitNames: {
        meters: 'm',
        kilometers: 'km',
        yards: 'yd',
        miles: 'mi',
        hours: 'h',
        minutes: 'mÃ­n',
        seconds: 's'
      },
      language: 'en',
      roundingSensitivity: 1,
      distanceTemplate: '{value} {unit}'
    },

    initialize: function(options) {
      L.setOptions(this, options);
    },

    formatDistance: function(d /* Number (meters) */) {
      var un = this.options.unitNames,
          v,
        data;
      if (this.options.units === 'imperial') {
        //valhalla returns distance in km
        d  = d * 1000;
        d = d / 1.609344;
        if (d >= 1000) {
          data = {
            value: (this._round(d) / 1000),
            unit: un.miles
          };
        } else {
          data = {
            value: this._round(d / 1.760),
            unit: un.yards
          };
        }
      } else {
        v = d;
        data = {
          value: v >= 1 ? v: v*1000,
          unit: v >= 1 ? un.kilometers : un.meters
        };
      }

       return L.Util.template(this.options.distanceTemplate, data);
    },

    _round: function(d) {
      var pow10 = Math.pow(10, (Math.floor(d / this.options.roundingSensitivity) + '').length - 1),
        r = Math.floor(d / pow10),
        p = (r > 5) ? pow10 : pow10 / 2;

      return Math.round(d / p) * p;
    },

    formatTime: function(t /* Number (seconds) */) {
      if (t > 86400) {
        return Math.round(t / 3600) + ' h';
      } else if (t > 3600) {
        return Math.floor(t / 3600) + ' h ' +
          Math.round((t % 3600) / 60) + ' min';
      } else if (t > 300) {
        return Math.round(t / 60) + ' min';
      } else if (t > 60) {
        return Math.floor(t / 60) + ' min' +
          (t % 60 !== 0 ? ' ' + (t % 60) + ' s' : '');
      } else {
        return t + ' s';
      }
    },

    formatInstruction: function(instr, i) {
      // Valhalla returns instructions itself.
      return instr.instruction;
    },

    getIconName: function(instr, i) {
      // you can find all Valhalla's direction types at https://github.com/valhalla/odin/blob/master/proto/tripdirections.proto
      switch (instr.type) {
        case 0:
          return 'kNone';
        case 1:
          return 'kStart';
        case 2:
          return 'kStartRight';
        case 3:
          return 'kStartLeft';
        case 4:
          return 'kDestination';
        case 5:
          return 'kDestinationRight';
        case 6:
          return 'kDestinationLeft';
        case 7:
          return 'kBecomes';
        case 8:
          return 'kContinue';
        case 9:
          return 'kSlightRight';
        case 10:
          return 'kRight';
        case 11:
          return 'kSharpRight';
        case 12:
          return 'kUturnRight';
        case 13:
          return 'kUturnLeft';
        case 14:
          return 'kSharpLeft';
        case 15:
          return 'kLeft';
        case 16:
          return 'kSlightLeft';
        case 17:
          return 'kRampStraight';
        case 18:
          return 'kRampRight';
        case 19:
          return 'kRampLeft';
        case 20:
          return 'kExitRight';
        case 21:
          return 'kExitLeft';
        case 22:
          return 'kStayStraight';
        case 23:
          return 'kStayRight';
        case 24:
          return 'kStayLeft';
        case 25:
          return 'kMerge';
        case 26:
          return 'kRoundaboutEnter';
        case 27:
          return 'kRoundaboutExit';
        case 28:
          return 'kFerryEnter';
        case 29:
          return 'kFerryExit';
        // lrm-mapzen unifies transit commands and give them same icons
        case 30:
        case 31: //'kTransitTransfer'
        case 32: //'kTransitRemainOn'
        case 33: //'kTransitConnectionStart'
        case 34: //'kTransitConnectionTransfer'
        case 35: //'kTransitConnectionDestination'
        case 36: //'kTransitConnectionDestination'
          if (instr.edited_travel_type) return 'kTransit' + this._getCapitalizedName(instr.edited_travel_type);
          else return 'kTransit';
      }
    },

    _getInstructionTemplate: function(instr, i) {
      return instr.instruction + " " +instr.length;
    },
    _getCapitalizedName: function(name) {
      return name.charAt(0).toUpperCase() + name.slice(1);
    }
  });

})();
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],32:[function(require,module,exports){
(function (global){
(function() {
	'use strict';

	var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

	module.exports = L.LayerGroup.extend({
		includes: L.Mixin.Events,

		options: {
			styles: [
				{color: 'white', opacity: 0.8, weight: 8},
				{color: '#06a6d4', opacity: 1, weight: 6}
			],
			missingRouteStyles: [
				{color: 'black', opacity: 0.15, weight: 8},
				{color: 'white', opacity: 0.6, weight: 6},
				{color: 'gray', opacity: 0.8, weight: 4, dashArray: '7,12'}
			],
			addWaypoints: true,
			extendToWaypoints: true,
			missingRouteTolerance: 10
		},

		initialize: function(route, options) {
			L.setOptions(this, options);
			L.LayerGroup.prototype.initialize.call(this, options);
			this._route = route;

			if (this.options.extendToWaypoints) {
				this._extendToWaypoints();
			}

			if (route.subRoutes) {
				for(var i = 0; i < route.subRoutes.length; i++) {
					if(!route.subRoutes[i].styles) route.subRoutes[i].styles = this.options.styles;
					this._addSegment(
						route.subRoutes[i].coordinates,
						route.subRoutes[i].styles,
						this.options.addWaypoints);
				}
			} else {
			 this._addSegment(
			 	route.coordinates,
			 	this.options.styles,
			 	this.options.addWaypoints);
			}
		},

		addTo: function(map) {
			map.addLayer(this);
			return this;
		},
		getBounds: function() {
			return L.latLngBounds(this._route.coordinates);
		},

		_findWaypointIndices: function() {
			var wps = this._route.inputWaypoints,
			    indices = [],
			    i;
			for (i = 0; i < wps.length; i++) {
				indices.push(this._findClosestRoutePoint(wps[i].latLng));
			}

			return indices;
		},

		_findClosestRoutePoint: function(latlng) {
			var minDist = Number.MAX_VALUE,
				minIndex,
			    i,
			    d;

			for (i = this._route.coordinates.length - 1; i >= 0 ; i--) {
				// TODO: maybe do this in pixel space instead?
				d = latlng.distanceTo(this._route.coordinates[i]);
				if (d < minDist) {
					minIndex = i;
					minDist = d;
				}
			}

			return minIndex;
		},

		_extendToWaypoints: function() {
			var wps = this._route.inputWaypoints,
				wpIndices = this._getWaypointIndices(),
			    i,
			    wpLatLng,
			    routeCoord;

			for (i = 0; i < wps.length; i++) {
				wpLatLng = wps[i].latLng;
				routeCoord = L.latLng(this._route.coordinates[wpIndices[i]]);
				if (wpLatLng.distanceTo(routeCoord) >
					this.options.missingRouteTolerance) {
					this._addSegment([wpLatLng, routeCoord],
						this.options.missingRouteStyles);
				}
			}
		},

		_addSegment: function(coords, styles, mouselistener) {
			var i,
				pl;
			for (i = 0; i < styles.length; i++) {
				pl = L.polyline(coords, styles[i]);
				this.addLayer(pl);
				if (mouselistener) {
					pl.on('mousedown', this._onLineTouched, this);
				}
			}
		},

		_findNearestWpBefore: function(i) {
			var wpIndices = this._getWaypointIndices(),
				j = wpIndices.length - 1;
			while (j >= 0 && wpIndices[j] > i) {
				j--;
			}

			return j;
		},

		_onLineTouched: function(e) {
			var afterIndex = this._findNearestWpBefore(this._findClosestRoutePoint(e.latlng));
			this.fire('linetouched', {
				afterIndex: afterIndex,
				latlng: e.latlng
			});
		},

		_getWaypointIndices: function() {
			if (!this._wpIndices) {
				this._wpIndices = this._route.waypointIndices || this._findWaypointIndices();
			}

			return this._wpIndices;
		}
	});

})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],33:[function(require,module,exports){
(function (global){
(function() {
  'use strict';

  var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
  var corslite = require('@mapbox/corslite');
  var polyline = require('@mapbox/polyline');

  var Waypoint = require('./mapzenWaypoint');

  module.exports = L.Class.extend({
    options: {
      serviceUrl: 'https://valhalla.mapzen.com/route?',
      timeout: 30 * 1000
    },

    initialize: function(accessToken, options) {
      L.Util.setOptions(this, options);
      // There is currently no way to differentiate the options for Leaflet Routing Machine itself from options for route call
      // So we resort the options here
      // In future, lrm-mapzen will consider exposing routingOptions object to users
      this.options.routingOptions = {};
      for (var key in options) {
        if (key !== 'serviceUrl' || key !== 'timeout') {
          this.options.routingOptions[key] = options[key];
        }
      }
      this._accessToken = accessToken;
    },

    route: function(waypoints, callback, context, options) {
      var timedOut = false,
        wps = [],
        url,
        timer,
        wp,
        i;
      var routingOptions = L.extend(this.options.routingOptions, options);

      url = this.buildRouteUrl(waypoints, routingOptions);
      timer = setTimeout(function() {
                timedOut = true;
                callback.call(context || callback, {
                  status: -1,
                  message: 'Time out.'
                });
              }, this.options.timeout);

      // Create a copy of the waypoints, since they
      // might otherwise be asynchronously modified while
      // the request is being processed.
      for (i = 0; i < waypoints.length; i++) {
        wp = waypoints[i];
        wps.push(new Waypoint(L.latLng(wp.latLng), wp.name || "", wp.options || {}))
      }

      corslite(url, L.bind(function(err, resp) {
        var data;
        clearTimeout(timer);
        if (!timedOut) {
          if (!err) {
            data = JSON.parse(resp.responseText);
            this._routeDone(data, wps, routingOptions, callback, context);
          } else {
            console.log("Error : " + err.response);
            callback.call(context || callback, {
              status: err.status,
              message: err.response
            });
          }
        }
      }, this), true);

      return this;
    },

    _routeDone: function(response, inputWaypoints, routingOptions, callback, context) {

      var coordinates,
          alts,
          outputWaypoints,
          i;
      context = context || callback;
      if (response.trip.status !== 0) {
        callback.call(context, {
          status: response.status,
          message: response.status_message
        });
        return;
      }

      var insts = [];
      var coordinates = [];
      var shapeIndex =  0;

      for(var i = 0; i < response.trip.legs.length; i++){
        var coord = polyline.decode(response.trip.legs[i].shape, 6);

        for(var k = 0; k < coord.length; k++){
          coordinates.push(L.latLng(coord[k][0], coord[k][1]));
        }

        for(var j =0; j < response.trip.legs[i].maneuvers.length; j++){
          var res = response.trip.legs[i].maneuvers[j];
          res.distance = response.trip.legs[i].maneuvers[j]["length"];
          res.index = shapeIndex + response.trip.legs[i].maneuvers[j]["begin_shape_index"];
          insts.push(res);
        }

        if(routingOptions.costing === 'multimodal') insts = this._unifyTransitManeuver(insts);

        shapeIndex += response.trip.legs[i].maneuvers[response.trip.legs[i].maneuvers.length-1]["begin_shape_index"];
      }

      outputWaypoints = this._toWaypoints(inputWaypoints, response.trip.locations);
      var subRoutes;
      if (routingOptions.costing == 'multimodal') subRoutes = this._getSubRoutes(response.trip.legs)

      alts = [{
        name: this._trimLocationKey(inputWaypoints[0].latLng) + " , " + this._trimLocationKey(inputWaypoints[inputWaypoints.length-1].latLng) ,
        unit: response.trip.units,
        costing: routingOptions.costing,
        coordinates: coordinates,
        subRoutes: subRoutes,
        instructions: insts,//response.route_instructions ? this._convertInstructions(response.route_instructions) : [],
        summary: response.trip.summary ? this._convertSummary(response.trip.summary) : [],
        inputWaypoints: inputWaypoints,
        outputWaypoints: outputWaypoints,
        actualWaypoints: outputWaypoints, // DEPRECATE THIS on v2.0
        waypointIndices: this._clampIndices([0,response.trip.legs[0].maneuvers.length], coordinates)
      }];

      callback.call(context, null, alts);
    },

    // lrm mapzen is trying to unify manuver of subroutes,
    // travle type number including transit routing is > 30 including entering the station, exiting the station
    // look at the api docs for more info (docs link coming soon)
    _unifyTransitManeuver: function(insts) {

      var transitType;
      var newInsts = insts;

      for(var i = 0; i < newInsts.length; i++) {
        if(newInsts[i].type == 30) {
          transitType = newInsts[i].travel_type;
          break;
        }
      }

      for(var j = 0; j < newInsts.length; j++) {
        if(newInsts[j].type > 29) newInsts[j].edited_travel_type = transitType;
      }

      return newInsts;

    },

    //creates section of the polyline based on change of travel mode for multimodal
    _getSubRoutes: function(legs) {

      var subRoute = [];

      for (var i = 0; i < legs.length; i++) {

        var coords = polyline.decode(legs[i].shape, 6);

        var lastTravelType;
        var transitIndices = [];
        for(var j = 0; j < legs[i].maneuvers.length; j++){

          var res = legs[i].maneuvers[j];
          var travelType = res.travel_type;

          if(travelType !== lastTravelType || res.type === 31 /*this is for transfer*/) {
            //transit_info only exists in the transit maneuvers
            //loop thru maneuvers and populate indices array with begin shape index
            //also populate subRoute array to contain the travel type & color associated with the transit polyline sub-section
            //otherwise just populate with travel type and use fallback style
            if(res.begin_shape_index > 0) transitIndices.push(res.begin_shape_index);
            if(res.transit_info) subRoute.push({ travel_type: travelType, styles: this._getPolylineColor(res.transit_info.color) })
            else subRoute.push({travel_type: travelType})
          }

          lastTravelType = travelType;
        }

        //add coords length to indices array
        transitIndices.push(coords.length);

        //logic to create the subsets of the polyline by indexing into the shape
        var index_marker = 0;
        for(var index = 0; index < transitIndices.length; index++) {
          var subRouteArr = [];
          var overwrapping = 0;
          //if index != the last indice, we want to overwrap (or add 1) so that routes connect
          if(index !== transitIndices.length-1) overwrapping = 1;
          for (var ti = index_marker; ti < transitIndices[index] + overwrapping; ti++){
            subRouteArr.push(coords[ti]);
          }

          var temp_array = subRouteArr;
          index_marker = transitIndices[index];
          subRoute[index].coordinates = temp_array;
        }
      }
      return subRoute;
    },

    _getPolylineColor: function(intColor) {

      // isolate red, green, and blue components
      var red = (intColor >> 16) & 0xff,
          green = (intColor >> 8) & 0xff,
          blue = (intColor >> 0) & 0xff;

      // calculate luminance in YUV colorspace based on
      // https://en.wikipedia.org/wiki/YUV#Conversion_to.2Ffrom_RGB
      var lum = 0.299 * red + 0.587 * green + 0.114 * blue,
          is_light = (lum > 0xbb);

      // generate a CSS color string like 'RRGGBB'
      var paddedHex = 0x1000000 | (intColor & 0xffffff),
          lineColor = paddedHex.toString(16).substring(1, 7);

      var polylineColor = [
            // Color of outline depending on luminance against background.
            (is_light ? {color: '#000', opacity: 0.8, weight: 8}
                      : {color: '#fff', opacity: 0.8, weight: 8}),
            // Color of the polyline subset.
            {color: '#'+lineColor.toUpperCase(), opacity: 1, weight: 6}
          ];

      return polylineColor;
   },

    _toWaypoints: function(inputWaypoints, vias) {
      var wps = [],
          i;
      for (i = 0; i < vias.length; i++) {
        var etcInfo = {};
        for (var key in vias[i]) {
          if(key !== 'lat' && key !== 'lon') {
            etcInfo[key] = vias[i][key];
          }
        }
        wps.push(new Waypoint(L.latLng([vias[i]["lat"],vias[i]["lon"]]),
                                    null,
                                    etcInfo));
      }
      return wps;
    },

    buildRouteUrl: function(waypoints, options) {
      var locs = [];

      for (var i = 0; i < waypoints.length; i++) {
        var loc = {
          lat: waypoints[i].latLng.lat,
          lon: waypoints[i].latLng.lng,
        }
        for (var key in waypoints[i].options) {
          if (waypoints[i].options[key]) loc[key] = waypoints[i].options[key];
        }
        locs.push(loc);
      }

      var paramsToPass = L.extend(options, { locations: locs });
      var params = JSON.stringify(paramsToPass);

      return this.options.serviceUrl + 'json=' +
              params + '&api_key=' + this._accessToken;
    },

    _locationKey: function(location) {
      return location.lat + ',' + location.lng;
    },

    _trimLocationKey: function(location){
      var lat = location.lat;
      var lng = location.lng;

      var nameLat = Math.floor(location.lat * 1000)/1000;
      var nameLng = Math.floor(location.lng * 1000)/1000;

      return nameLat + ' , ' + nameLng;

    },

    _convertSummary: function(route) {
      return {
        totalDistance: route.length,
        totalTime: route.time
      };
    },

    _convertInstructions: function(instructions) {
      var result = [],
          i,
          instr,
          type,
          driveDir;

      for (i = 0; i < instructions.length; i++) {
        instr = instructions[i];
        type = this._drivingDirectionType(instr[0]);
        driveDir = instr[0].split('-');
        if (type) {
          result.push({
            type: type,
            distance: instr[2],
            time: instr[4],
            road: instr[1],
            direction: instr[6],
            exit: driveDir.length > 1 ? driveDir[1] : undefined,
            index: instr[3]
          });
        }
      }
      return result;
    },

    _clampIndices: function(indices, coords) {
      var maxCoordIndex = coords.length - 1,
        i;
      for (i = 0; i < indices.length; i++) {
        indices[i] = Math.min(maxCoordIndex, Math.max(indices[i], 0));
      }
    }
  });

})();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./mapzenWaypoint":34,"@mapbox/corslite":29,"@mapbox/polyline":30}],34:[function(require,module,exports){
(function (global){
(function() {
  'use strict';

  var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

  module.exports = L.Class.extend({
    options: {
    // lrm-mapzen passes these options of locations to the request call
    // to see more options https://mapzen.com/documentation/mobility/turn-by-turn/api-reference/#locations
      type: null, // 'break' or 'through'. If no type is provided, the type is assumed to be a break.
      name: null,
      haeding: null,
      heading_tolerance: null,
      street: null,
      way_id: null,
      minimum_reachability: null,
      radius: null
    },
    initialize: function(latLng, name, options) {
      L.Util.setOptions(this, options);
      this.latLng = L.latLng(latLng);
      this.name = name;
    }
  });
})();
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],35:[function(require,module,exports){
// (c) 2017 Mapzen
//
// MAPZEN SCARAB (aka BUG for US BROADCAST TELEVISION and DOG in the UK)
// http://en.wikipedia.org/wiki/Digital_on-screen_graphic
//
// Identifies full-screen demo pages with Mapzen brand and provides helpful
// social media links.
// ----------------------------------------------------------------------------
/* global module, ga */
var MapzenScarab = (function () {
  'use strict'

  var DEFAULT_LINK = 'https://mapzen.com/'
  var TRACKING_CATEGORY = 'demo'
  var ANALYTICS_PROPERTY_ID = 'UA-47035811-1'

  // Globals
  var opts
    // opts.name      Name of demo
    // opts.link      Link to go to
    // opts.tweet     prewritten tweet
    // opts.analytics track?
    // opts.repo      Link to GitHub repository
    // opts.description Information about map

  var infoDescriptionEl

  function _track (action, label, value, nonInteraction) {
    if (opts.analytics === false) return false

    if (typeof ga === 'undefined') {
      return false
    }

    ga('send', 'event', TRACKING_CATEGORY, action, label, value, nonInteraction)
  }

  function _loadAnalytics () {
    /* eslint-disable */
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', ANALYTICS_PROPERTY_ID, 'auto');
    ga('send', 'pageview');
    /* eslint-enable */
  }

  function _popupWindow (url, title, w, h) {
    // Borrowed from rrssb
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screen.left
    var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screen.top

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : window.screen.width
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : window.screen.height

    var left = ((width / 2) - (w / 2)) + dualScreenLeft
    var top = ((height / 3) - (h / 3)) + dualScreenTop

    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left)

    // Puts focus on the newWindow
    if (window.focus) {
      newWindow.focus()
    }
  }

  function _buildTwitterLink () {
    var base = 'https://twitter.com/intent/tweet'
    var url = encodeURIComponent(window.location.href)
    var text
    var params

    if (opts.tweet) {
      text = encodeURIComponent(opts.tweet)
    } else if (opts.name) {
      text = encodeURIComponent(opts.name + ', powered by @mapzen')
    } else {
      text = encodeURIComponent('Check out this project by @mapzen!')
    }

    params = '?text=' + text + '&url=' + url
    return base + params
  }

  function _buildFacebookLink () {
    var base = 'https://www.facebook.com/sharer/sharer.php?u='
    var url = encodeURIComponent(window.location.href)
    return base + url
  }

  function _createElsAndAppend () {
    var mapzenLink = opts.link || DEFAULT_LINK
    var mapzenTitle = (opts.name) ? opts.name + ' Â· Powered by Mapzen' : 'Powered by Mapzen'
    var el = document.createElement('div')

    // Create container
    el.id = 'mz-bug'
    el.className = 'mz-bug-container'
    el.setAttribute('role', 'widget')

    // Create buttons
    var mapzenEl = _createButtonEl('mapzen', mapzenLink, mapzenTitle, _onClickMapzen)
    var twitterEl = _createButtonEl('twitter', _buildTwitterLink(), 'Share this on Twitter', _onClickTwitter)
    var facebookEl = _createButtonEl('facebook', _buildFacebookLink(), 'Share this on Facebook', _onClickFacebook)

    // Build DOM
    el.appendChild(mapzenEl)
    el.appendChild(twitterEl)
    el.appendChild(facebookEl)

    // Creating github icon button if needed
    if (opts.repo) {
      var githubEl = _createButtonEl('github', opts.repo, 'View source on GitHub', _onClickGitHub)
      el.appendChild(githubEl)
    }

    // Creating info button and adding to container only if description is provided
    if (opts.description) {
      var infoEl = _createInfoButton('info', _onClickInfo)
      el.appendChild(infoEl)
    }

    document.body.appendChild(el)
    return el
  }

  function _createInfoButton(id, clickHandler) {
    var infoButton = document.createElement('div')
    var infoLogo = document.createElement('div')
    infoLogo.className = 'mz-bug-' + id + '-logo'
    infoLogo.addEventListener('click', clickHandler)
    infoButton.className = 'mz-bug-' + id
    infoButton.className += ' mz-bug-icons'

    infoButton.appendChild(infoLogo)
    return infoButton
  }

  function _createButtonEl (id, linkHref, linkTitle, clickHandler) {
    var linkEl = document.createElement('a')
    var logoEl = document.createElement('div')

    logoEl.className = 'mz-bug-' + id + '-logo'
    linkEl.href = linkHref
    linkEl.target = '_blank'
    linkEl.className = 'mz-bug-' + id + '-link'
    linkEl.className += ' mz-bug-icons'
    linkEl.title = linkTitle
    linkEl.addEventListener('click', clickHandler)

    linkEl.appendChild(logoEl)
    return linkEl
  }

  function _onClickMapzen (event) {
    _track('click', 'mapzen logo', opts.name)
  }

  function _onClickTwitter (event) {
    event.preventDefault()
    var link = _buildTwitterLink()
    _popupWindow(link, 'Twitter', 580, 470)
    _track('click', 'twitter', opts.name)
  }

  function _onClickFacebook (event) {
    event.preventDefault()
    var link = _buildFacebookLink()
    _popupWindow(link, 'Facebook', 580, 470)
    _track('click', 'facebook', opts.name)
  }

  function _onClickGitHub (event) {
    _track('click', 'github', opts.name)
  }

  // Clicking info button should lead to pop up description to open up
  // Clicking info button again should lead to description box closing
  // If no description provided, do not open description box
  function _onClickInfo(event) {
    var elem = infoDescriptionEl
    if (elem.style.display === 'block') {
      elem.style.display = 'none'
    } else {
      elem.style.display = 'block'
    }
  }

  function _buildDescription(id, container) {
    var infoBox = document.createElement('div')
    infoBox.className = "mz-bug-" + id
    infoBox.textContent = opts.description 
    infoBox.style.width = container.offsetWidth + 'px'
    infoBox.style.marginLeft = container.style.marginLeft

    document.body.appendChild(infoBox)
    return infoBox
  }

  function resizeDescription(container) {
    var containerWidth = container.offsetWidth 
    infoDescriptionEl.style.width = containerWidth + 'px'
    infoDescriptionEl.style.marginLeft = container.style.marginLeft
  }

  function centerScarab(container) {
    var containerWidth = container.offsetWidth
    var offsetMargin = -1 * containerWidth / 2
    container.style.marginLeft = offsetMargin + 'px'
  }

  var MapzenScarab = function (options) {
    // nifty JS constructor pattern via browserify documentation
    // https://github.com/substack/browserify-handbook#reusable-components
    if (!(this instanceof MapzenScarab)) return new MapzenScarab(options)

    // If iframed, exit & do nothing.
    if (window.self !== window.top) {
      return false
    }

    this.setOptions(options)

    this.el = _createElsAndAppend()
    this.twitterEl = this.el.querySelector('.mz-bug-twitter-link')
    this.facebookEl = this.el.querySelector('.mz-bug-facebook-link')

    centerScarab(this.el);
    window.addEventListener('resize', function(event) {
      centerScarab(this.el)
    }.bind(this))

    // Build links
    this.rebuildLinks()
    // Rebuild links if hash changes
    window.onhashchange = function () {
      this.rebuildLinks()
    }.bind(this)

    if (opts.description) {
      infoDescriptionEl = _buildDescription('description', this.el)
      window.addEventListener('resize', function(event) {
        resizeDescription(this.el)
      }.bind(this))
    }

    // Check if Google Analytics is present soon in the future; if not, load it.
    window.setTimeout(function () {
      if (typeof ga === 'undefined') {
        _loadAnalytics()
        _track('analytics', 'fallback', null, true)
      }

      _track('bug', 'active', opts.name, true)
    }, 0)
  }

  MapzenScarab.prototype.rebuildLinks = function () {
    this.twitterEl.href = _buildTwitterLink()
    this.facebookEl.href = _buildFacebookLink()
  }

  MapzenScarab.prototype.hide = function () {
    this.el.style.display = 'none'
  }

  MapzenScarab.prototype.show = function () {
    this.el.style.display = 'block'
  }

  MapzenScarab.prototype.setOptions = function (options) {
    // Default options
    opts = opts || {
      analytics: true,
      name: null
    }

    // Copy options values
    if (typeof options === 'object') {
      for (var i in options) {
        opts[i] = options[i]
      }
    }

    this.opts = opts
  }

  return MapzenScarab
}())

// Export as browserify module if present, otherwise, it is global to window
if (typeof module === 'object' && typeof module.exports === 'object') {
  module.exports = MapzenScarab
} else {
  window.MapzenScarab = MapzenScarab
}

},{}],36:[function(require,module,exports){
'use strict';

/**
 * Based off of [the offical Google document](https://developers.google.com/maps/documentation/utilities/polylinealgorithm)
 *
 * Some parts from [this implementation](http://facstaff.unca.edu/mcmcclur/GoogleMaps/EncodePolyline/PolylineEncoder.js)
 * by [Mark McClure](http://facstaff.unca.edu/mcmcclur/)
 *
 * @module polyline
 */

var polyline = {};

function encode(coordinate, factor) {
    coordinate = Math.round(coordinate * factor);
    coordinate <<= 1;
    if (coordinate < 0) {
        coordinate = ~coordinate;
    }
    var output = '';
    while (coordinate >= 0x20) {
        output += String.fromCharCode((0x20 | (coordinate & 0x1f)) + 63);
        coordinate >>= 5;
    }
    output += String.fromCharCode(coordinate + 63);
    return output;
}

/**
 * Decodes to a [latitude, longitude] coordinates array.
 *
 * This is adapted from the implementation in Project-OSRM.
 *
 * @param {String} str
 * @param {Number} precision
 * @returns {Array}
 *
 * @see https://github.com/Project-OSRM/osrm-frontend/blob/master/WebContent/routing/OSRM.RoutingGeometry.js
 */
polyline.decode = function(str, precision) {
    var index = 0,
        lat = 0,
        lng = 0,
        coordinates = [],
        shift = 0,
        result = 0,
        byte = null,
        latitude_change,
        longitude_change,
        factor = Math.pow(10, precision || 5);

    // Coordinates have variable length when encoded, so just keep
    // track of whether we've hit the end of the string. In each
    // loop iteration, a single coordinate is decoded.
    while (index < str.length) {

        // Reset shift, result, and byte
        byte = null;
        shift = 0;
        result = 0;

        do {
            byte = str.charCodeAt(index++) - 63;
            result |= (byte & 0x1f) << shift;
            shift += 5;
        } while (byte >= 0x20);

        latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

        shift = result = 0;

        do {
            byte = str.charCodeAt(index++) - 63;
            result |= (byte & 0x1f) << shift;
            shift += 5;
        } while (byte >= 0x20);

        longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

        lat += latitude_change;
        lng += longitude_change;

        coordinates.push([lat / factor, lng / factor]);
    }

    return coordinates;
};

/**
 * Encodes the given [latitude, longitude] coordinates array.
 *
 * @param {Array.<Array.<Number>>} coordinates
 * @param {Number} precision
 * @returns {String}
 */
polyline.encode = function(coordinates, precision) {
    if (!coordinates.length) { return ''; }

    var factor = Math.pow(10, precision || 5),
        output = encode(coordinates[0][0], factor) + encode(coordinates[0][1], factor);

    for (var i = 1; i < coordinates.length; i++) {
        var a = coordinates[i], b = coordinates[i - 1];
        output += encode(a[0] - b[0], factor);
        output += encode(a[1] - b[1], factor);
    }

    return output;
};

function flipped(coords) {
    var flipped = [];
    for (var i = 0; i < coords.length; i++) {
        flipped.push(coords[i].slice().reverse());
    }
    return flipped;
}

/**
 * Encodes a GeoJSON LineString feature/geometry.
 *
 * @param {Object} geojson
 * @param {Number} precision
 * @returns {String}
 */
polyline.fromGeoJSON = function(geojson, precision) {
    if (geojson && geojson.type === 'Feature') {
        geojson = geojson.geometry;
    }
    if (!geojson || geojson.type !== 'LineString') {
        throw new Error('Input must be a GeoJSON LineString');
    }
    return polyline.encode(flipped(geojson.coordinates), precision);
};

/**
 * Decodes to a GeoJSON LineString geometry.
 *
 * @param {String} str
 * @param {Number} precision
 * @returns {Object}
 */
polyline.toGeoJSON = function(str, precision) {
    var coords = polyline.decode(str, precision);
    return {
        type: 'LineString',
        coordinates: flipped(coords)
    };
};

if (typeof module === 'object' && module.exports) {
    module.exports = polyline;
}

},{}],37:[function(require,module,exports){
(function (global){
/**
 * Mapzen API Key Check
 */

var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

/**
 * The URL_PATTERN handles the old vector.mapzen.com origin (until it is fully
 * deprecated) as well as the new v1 tile.mapzen.com endpoint.
 *
 * Extensions include both vector and raster tile services.
 */
var URL_PATTERN = /((https?:)?\/\/(vector|tile).mapzen.com([a-z]|[A-Z]|[0-9]|\/|\{|\}|\.|\||:)+(topojson|geojson|mvt|png|tif|gz))/;

/**
 * A basic check to see if an API key string looks like a valid key.
 * Not *is* a valid key, just *looks like* one.
 *
 * @param {string} apiKey Mapzen API key string
 */
var isValidMapzenApiKey = function (apiKey) {
  return (typeof apiKey === 'string' && apiKey.match(/^[-a-z]+-[0-9a-zA-Z_-]{5,7}$/));
};

var warningCounter = 0;

var getKeyAndOptions = function (_key, _options) {
  var key;
  var options = {};

  if (typeof _key !== 'string' && typeof _key !== 'object') {
    // When nothing is passed
    key = L.Mapzen.apiKey;
  } else if (typeof _key === 'object') {
    // When the key is omitted and options is passed
    key = L.Mapzen.apiKey;
    options = _key;
  } else {
    key = _key;
    options = _options || options;
  }
  return {
    key: key,
    options: options
  };
};

/**
 * Throw console warning about missing API key
 *
 * @param {string} component Name of component with missing API key (optional)
 */
var throwApiKeyWarning = function (component) {
  component = component || 'all Mapzen Services';

  console.warn('A valid API key is required for access to ' + component);

  // Show expanded warning the first time
  if (warningCounter === 0) {
    console.warn('****************************** \n' +
                 'Generate your free API key at  \n' +
                 'https://mapzen.com/developers  \n' +
                 '******************************');
  }
  warningCounter++;
};

module.exports = {
  URL_PATTERN: URL_PATTERN,
  isValidMapzenApiKey: isValidMapzenApiKey,
  throwApiKeyWarning: throwApiKeyWarning,
  getKeyAndOptions: getKeyAndOptions
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],38:[function(require,module,exports){
// Mapzen House style for Tangram
var style = {
  BubbleWrap: 'https://mapzen.com/carto/bubble-wrap-style/7/bubble-wrap-style.zip',
  BubbleWrapMoreLabels: 'https://mapzen.com/carto/bubble-wrap-style-more-labels/7/bubble-wrap-style-more-labels.zip',
  BubbleWrapNoLabels: 'https://mapzen.com/carto/bubble-wrap-style-no-labels/7/bubble-wrap-style-no-labels.zip',
  Cinnabar: 'https://mapzen.com/carto/cinnabar-style/7/cinnabar-style.zip',
  CinnabarMoreLabels: 'https://mapzen.com/carto/cinnabar-style-more-labels/7/cinnabar-style-more-labels.zip',
  CinnabarNoLabels: 'https://mapzen.com/carto/cinnabar-style-no-labels/7/cinnabar-style-no-labels.zip',
  Refill: 'https://mapzen.com/carto/refill-style/7/refill-style.zip',
  RefillMoreLabels: 'https://mapzen.com/carto/refill-style-more-labels/7/refill-style-more-labels.zip',
  RefillNoLabels: 'https://mapzen.com/carto/refill-style-no-labels/7/refill-style-no-labels.zip',
  Zinc: 'https://mapzen.com/carto/zinc-style/6/zinc-style.zip',
  ZincMoreLabels: 'https://mapzen.com/carto/zinc-style-more-labels/6/zinc-style-more-labels.zip',
  ZincNoLabels: 'https://mapzen.com/carto/zinc-style-no-labels/6/zinc-style-no-labels.zip',
  Walkabout: 'https://mapzen.com/carto/walkabout-style/5/walkabout-style.zip',
  WalkaboutMoreLabels: 'https://mapzen.com/carto/walkabout-style-more-labels/5/walkabout-style-more-labels.zip',
  WalkaboutNoLabels: 'https://mapzen.com/carto/walkabout-style-no-labels/5/walkabout-style-no-labels.zip',
  Tron: 'https://mapzen.com/carto/tron-style/4/tron-style.zip',
  TronMoreLabels: 'https://mapzen.com/carto/tron-style-more-labels/4/tron-style-more-labels.zip',
  TronNoLabels: 'https://mapzen.com/carto/tron-style-no-labels/4/tron-style-no-labels.zip'
};

module.exports = style;

},{}],39:[function(require,module,exports){
(function (global){
'use strict';
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

var Hash = L.Class.extend({

  // We keep hash data in _hashData obj
  _hashData: {},
  _changing: false,
  _map: null,
  _geocoder: null,
  _throttleLimit: 500,

  initialize: function (options) {
    if (options.map) {
      this._map = options.map;
      this._startMapEvents();
    }

    if (options.geocoder) {
      this._geocoder = options.geocoder;
      this._startGeocoderEvents();
    }

    this._setupHash();
    // L.DomEvent.on(window, 'onhashchange', this._setupHash, this);
  },

  _setupHash: function () {
    var currentDataObj = Formatter.parseHashToObj(window.location.hash);
    this._hashData = currentDataObj;
    if (this._hashData) {
      // When there is place query in hash, it takes priority to the coord data.
      if (this._hashData.place) {
        this._hashData.place = decodeURIComponent(this._hashData.place);
        this._geocoder.place(this._hashData.place);
      } else if (this._hashData.lat && this._hashData.lng && this._hashData.z) {
        // boolean changing is to prevent recursive hash change
        // Hash doesn't get updated while map is setting the view
        this._changing = true;
        if (this._map) this._map.setView([this._hashData.lat, this._hashData.lng], this._hashData.z);
        this._changing = false;
      }
    } else {
      // When there is no hash, setup hash data and get current map status
      this._hashData = {};
      if (this._map) {
        this._updateLatLng();
        this._updateZoom();
      }
    }
  },

  _startMapEvents: function () {
    L.DomEvent.on(this._map, 'moveend', this._throttle(this._updateLatLng, this._throttleLimit), this);
    L.DomEvent.on(this._map, 'zoomend', this._throttle(this._updateZoom, this._throttleLimit), this);
  },

  _startGeocoderEvents: function () {
    L.DomEvent.on(this._geocoder, 'select', this._updatePlace, this);
    L.DomEvent.on(this._geocoder, 'reset', this._resetPlace, this);
  },

  _updateLatLng: function () {
    if (!this._changing) {
      var center = this._map.getCenter();
      var zoom = this._map.getZoom();

      var precision = this._precision(zoom);
      var newLat = center.lat.toFixed(precision);
      var newLng = center.lng.toFixed(precision);
      this._hashData.lat = newLat;
      this._hashData.lng = newLng;

      this._updateHash();
    }
  },

  _updateZoom: function () {
    if (!this._changing) {
      var zoom = this._map.getZoom();
      this._hashData.z = this._roundZDown(zoom);
      this._updateHash();
    }
  },

  _updatePlace: function (e) {
    this._hashData.place = e.feature.properties.gid;
    this._updateHash();
  },

  _reset: function () {
    this.hashData = {};
    history.replaceState({}, document.title, '.');
  },

  _resetCoords: function () {
    this._hashData = Formatter.deleteProperty(this._hashData, 'lat');
    this._hashData = Formatter.deleteProperty(this._hashData, 'lng');
    this._hashData = Formatter.deleteProperty(this._hashData, 'z');
    this._updateHash();
  },

  _resetPlace: function () {
    this._hashData = Formatter.deleteProperty(this._hashData, 'place');
    this._updateHash();
  },

  _updateHash: function () {
    var formattedData = Formatter.formatToHash(this._hashData);
    window.history.replaceState({}, null, '#' + formattedData);
  },

  _precision: function (z) {
    return Math.max(0, Math.ceil(Math.log(z) / Math.LN2));
  },

  _roundZDown: function (z) {
    if (z % 1 === 0) return z;
    else return z.toFixed(4);
  },

  _throttle: function (callback, limit) {
    var wait = false;
    return function () {
      if (!wait) {
        callback.bind(this).call();
        wait = true;
        setTimeout(function () {
          wait = false;
        }, limit);
      }
    };
  }
});

var Formatter = {
  parseHashToObj: function (rawHash) {
    var dObj = {};

    if (this.isEmpty(rawHash)) {
      return null;
    } else {
      var hashVal = rawHash.replace('#', '');
      var valArrs = hashVal.split('&');

      for (var val in valArrs) {
        var keyAndValue = valArrs[val].split('=');
        dObj[keyAndValue[0]] = keyAndValue[1];
      }
      return dObj;
    }
  },
  isEmpty: function (str) {
    if (str.length === 0 || !str) return true;
    else return false;
  },
  deleteProperty: function (dobj, _prop) {
    var newObj = {};
    for (var p in dobj) {
      if (p !== _prop) {
        newObj[p] = dobj[p];
      }
    }
    return newObj;
  },

  formatToHash: function (obj) {
    var str = [];
    for (var p in obj) {
      // Nulls or undefined is just empty string
      if (obj[p] === null || typeof obj[p] === 'undefined') {
        obj[p] = '';
      }
      if (obj.hasOwnProperty(p)) {
        str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
      }
    }
    return str.join('&');
  }
};

module.exports = Hash;

module.exports.hash = function (options) {
  return new Hash(options);
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],40:[function(require,module,exports){
(function (global){
// Copyright (c) 2016 Dominik Moritz
// This code is the body  of Leaflet LocateControl https://github.com/domoritz/leaflet-locatecontrol
// Mapzen.js detached the commonJS part embedding Leaflet inside of Leaflet Locate Control and trimeed to follow MapzenJS lint rule

var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
var Locator = require('leaflet.locatecontrol');

module.exports = Locator;

module.exports.locator = function (opts) {
  var mapzenOptions = {
    position: 'bottomright',
    drawCircle: false,
    follow: false,
    showPopup: false,
    drawMarker: false,
    markerStyle: {
      opacity: 0
    },
    strings: {
      title: 'Get current location'
    },
    icon: 'mz-geolocator-icon',
    // We piggy back on geocoder plugin styles and use their load icon so it is the same.
    // Re-using the class name means we don't duplicate the embedded image style in the compiled bundle.
    iconLoading: 'mz-geolocator-icon mz-geolocator-active leaflet-pelias-search-icon leaflet-pelias-loading'
  };

  var extendedOptions = L.extend({}, mapzenOptions, opts);
  var locator = new Locator(extendedOptions);

  return locator;
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"leaflet.locatecontrol":28}],41:[function(require,module,exports){
(function (global){
'use strict';
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

var MapControl = L.Map.extend({
  // L.Evented is present in Leaflet v1+
  // L.Mixin.Events is legacy; was deprecated in Leaflet v1 and started
  // logging deprecation warnings in console in v1.1
  includes: L.Evented ? L.Evented.prototype : L.Mixin.Events,
  options: {
    attribution: 'Â© <a href="https://www.mapzen.com/rights">Mapzen</a>,  <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>, and <a href="https://www.mapzen.com/rights/#services-and-data-sources">others</a>',
    zoomSnap: 0,
    worldCopyJump: true,
    _useTangram: true,
    apiKey: null
  },

  // overriding Leaflet's map initializer
  initialize: function (element, options) {
    var opts = L.Util.setOptions(this, options);
    L.Map.prototype.initialize.call(this, element, opts);

    this._setGlobalApiKey(opts);

    if (this.options._useTangram) {
      var tangramOptions = opts.tangramOptions || {};

      // debugTangram is deprecated; remove in v1.0
      if (this.options.debugTangram) {
        tangramOptions = L.extend({}, tangramOptions, {debug: true});
        console.warn('Mapzen.js warning: `options.debugTangram` is deprecated and will be removed in v1.0. Please use `options.tangramOptions.debug`.');
      }
      // As of v1.0, scene will need to be part of tangramOptions
      if (this.options.scene) {
        tangramOptions = L.extend({}, tangramOptions, {scene: this.options.scene});
        console.warn('Mapzen.js warning: `options.scene` is deprecated and will be removed in v1.0. Please use `options.tangramOptions.scene`.');
      }

      this._tangram = L.Mapzen._tangram(tangramOptions);

      this._tangram.addTo(this);

      var self = this;
      self._tangram.on('loaded', function (e) {
        self.fire('tangramloaded', {
          tangramLayer: e.layer,
          tangramVersion: e.version
        });
      });
    }

    this._setDefaultUIPositions();
    this._addAttribution();
    this._checkConditions(false);
  },

  _setGlobalApiKey: function (opts) {
    this.options.apiKey = opts.apiKey || L.Mapzen.apiKey;

    // Update global (to be used by other services as needed)
    L.Mapzen.apiKey = this.options.apiKey;

    // Going forward, all API key checks should be performed on individual components
  },

  _checkConditions: function (force) {
    if (this._isThisIframed()) {
      // do not scroll zoom when it is iframed
      this.scrollWheelZoom.disable();
      this.scrollWheelZoom = false; // This is for Leaflet v1.0

      var anchors = document.querySelectorAll('a');

      for (var i = 0, j = anchors.length; i < j; i++) {
        var el = anchors[i];
        // Only set target when not explicitly specified
        // to avoid overwriting intentional targeting behavior
        // Unless the force parameter is true, then targets of
        // '_blank' are forced to to be '_top'
        if (!el.target || (force === true && el.target === '_blank')) {
          el.target = '_top';
        }
      }
    }
    // do not show zoom control buttons on mobile
    // need to add more check to detect touch device
    if ('ontouchstart' in window) {
      this._disableZoomControl();
    }
  },

  _isThisIframed: function () {
    return (window.self !== window.top);
  },

  _disableZoomControl: function () {
    if (this.options.zoomControl) {
      this.zoomControl._container.hidden = true;
    }
  },

  _setDefaultUIPositions: function () {
    if (this.options.zoomControl) {
      this.zoomControl.setPosition('bottomright');
    }
  },

  _addAttribution: function () {
    // Adding Mapzen attribution to Leaflet
    if (this.attributionControl) {
      var tempAttr = this.options.attributionText || this.options.attribution;
      this.attributionControl.setPrefix(tempAttr);
      this.attributionControl.addAttribution('<a href="http://leafletjs.com/">Leaflet</a>');
    }
  }
});

module.exports = MapControl;

module.exports.map = function (element, options) {
  return new MapControl(element, options);
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],42:[function(require,module,exports){
(function (global){
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
var Control = require('leaflet-routing-machine/src/control');
var Itinerary = require('leaflet-routing-machine/src/itinerary');
var ItineraryBuilder = require('leaflet-routing-machine/src/itinerary-builder');
var MapzenLine = require('lrm-mapzen/src/mapzenLine');
var Plan = require('leaflet-routing-machine/src/plan');
var MapzenWaypoint = require('lrm-mapzen/src/mapzenWaypoint');
var MapzenFormatter = require('lrm-mapzen/src/mapzenFormatter');
var ErrorControl = require('leaflet-routing-machine/src/error-control');
var GeocoderElement = require('leaflet-routing-machine/src/geocoder-element');
var MapzenControlGeocoder = require('leaflet-control-geocoder/src/geocoders/mapzen');
var MapzenRouter = require('lrm-mapzen/src/mapzenRouter');
var APIKeyCheck = require('./apiKeyCheck');

module.exports = {
  Control: Control,
  Itinerary: Itinerary,
  ItineraryBuilder: ItineraryBuilder,
  Line: MapzenLine,
  Plan: Plan,
  Waypoint: MapzenWaypoint,
  MapzenRouter: MapzenRouter,
  Formatter: MapzenFormatter,
  GeocoderElement: GeocoderElement
};

module.exports.routing = {
  control: function (_options) {
    var defaultOptions = {
      formatter: new MapzenFormatter(),
      routeLine: function (route, options) {
        return new MapzenLine(route, options);
      },
      summaryTemplate: '<div class="routing-info {costing}">{distance}, {time}</div>'
    };
    var options = L.extend({}, defaultOptions, _options);
    return new Control(options);
  },

  itinerary: function (options) {
    return Itinerary(options);
  },
  itineraryBuilder: function (options) {
    return new ItineraryBuilder(options);
  },
  line: function (route, options) {
    return new MapzenLine(route, options);
  },
  plan: function (waypoints, options) {
    return new Plan(waypoints, options);
  },
  waypoint: function (latLng, name, options) {
    return new MapzenWaypoint(latLng, name, options);
  },
  formatter: function (options) {
    return new MapzenFormatter(options);
  },
  router: function (key, options) {
    var params = APIKeyCheck.getKeyAndOptions(key, options);
    if (!APIKeyCheck.isValidMapzenApiKey(params.key)) {
      APIKeyCheck.throwApiKeyWarning('Routing');
    }
    return new MapzenRouter(params.key, params.options);
  },
  geocoderElement: function (wp, i, nWps, plan) {
    return new GeocoderElement(wp, i, nWps, plan);
  },

  geocoder: function (key, options) {
    var params = APIKeyCheck.getKeyAndOptions(key, options);
    if (!APIKeyCheck.isValidMapzenApiKey(params.key)) {
      APIKeyCheck.throwApiKeyWarning('Search');
    }
    return new MapzenControlGeocoder.class(params.key, params.options); // eslint-disable-line
  },

  errorControl: function (routingControl, options) {
    return new ErrorControl(routingControl, options);
  }
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./apiKeyCheck":37,"leaflet-control-geocoder/src/geocoders/mapzen":2,"leaflet-routing-machine/src/control":17,"leaflet-routing-machine/src/error-control":18,"leaflet-routing-machine/src/geocoder-element":20,"leaflet-routing-machine/src/itinerary":22,"leaflet-routing-machine/src/itinerary-builder":21,"leaflet-routing-machine/src/plan":26,"lrm-mapzen/src/mapzenFormatter":31,"lrm-mapzen/src/mapzenLine":32,"lrm-mapzen/src/mapzenRouter":33,"lrm-mapzen/src/mapzenWaypoint":34}],43:[function(require,module,exports){
var APIKeyCheck = require('./apiKeyCheck');
var Geocoder = require('leaflet-geocoder-mapzen/src/core');
var corslite = require('corslite');

Geocoder.prototype.getSearchResult = function (input, callback) {
  var param = {
    text: input
  };
  var params = this.getParams(param);
  corslite(this.options.url + '/search?' + this.serialize(params), callback, true);
};

Geocoder.prototype.getAutocompleteResult = function (input, callback) {
  var param = {
    text: input
  };

  var params = this.getParams(param);
  corslite(this.options.url + '/autocomplete?' + this.serialize(params), callback, true);
};

module.exports = Geocoder;

module.exports.geocoder = function (key, options) {
  var params = APIKeyCheck.getKeyAndOptions(key, options);
  // If there is no attribution user passes,
  // Geocoder will skip the attribution since mapzen.js's map compoent is handling it already.
  if (params.options && !params.options.attribution) params.options.attribution = '';
  if (!APIKeyCheck.isValidMapzenApiKey(params.key)) {
    APIKeyCheck.throwApiKeyWarning('Search');
  }

  return new Geocoder(params.key, params.options);
};

},{"./apiKeyCheck":37,"corslite":1,"leaflet-geocoder-mapzen/src/core":6}],44:[function(require,module,exports){
(function (global){
// Tangram can't be bundled from source since it needs to be able to access a full copy of itself
// (either as a URL or full string of all source code) in order to load itself into web workers
// This script injects the Tangram with script tag, so that Tangram doesn't need to be included with outside tag
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);
var APIKeyCheck = require('./apiKeyCheck');
var BasemapStyles = require('./basemapStyles');

var tangramLayerInstance;
var tangramVersion = '0.13';
var tangramPath = 'https://mapzen.com/tangram/' + tangramVersion + '/';

var TangramLayer = L.Class.extend({
  includes: L.Evented ? L.Evented.prototype : L.Mixin.Events,
  options: {
    fallbackTileURL: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    tangramURL: tangramPath + 'tangram.min.js',
    scene: BasemapStyles.BubbleWrapMoreLabels
  },

  initialize: function (opts) {
    this.options = L.Util.setOptions(this, opts);

    if (opts && opts.debug) {
      this.options.tangramURL = tangramPath + 'tangram.debug.js';
    }
    this.hasWebGL = this._hasWebGL();

    this._setUpApiKey();

    // Start importing script
    // When there is no Tangram object available but webGL is available
    if (typeof Tangram === 'undefined' && this.hasWebGL) {
      this._importScript(this.options.tangramURL);
    } else {
      // Not more than one Tangram instance is allowed.
      // console.log('Tangram is already on the page.');
    }
  },

  addTo: function (map) {
    if (typeof Tangram === 'undefined') {
      if (this.hasWebGL) {
        // If Tangram is not loaded yet, add layer when script is loaded
        this.oScript.onload = this.setUpTangramLayer.bind(this, map);
      } else {
        if (map.options.fallbackTile) {
          console.log('WebGL is not available, falling back to fallbackTile option.');
          map.options.fallbackTile.addTo(map);
        } else {
          // When WebGL is not avilable
          console.log('WebGL is not available, falling back to OSM default tile.');
          var fallbackTileInstance = L.tileLayer(this.options.fallbackTileURL, {});
          fallbackTileInstance.addTo(map);
        }
      }
    } else {
      this.setUpTangramLayer(map);
    }
  },

  setUpTangramLayer: function (map) {
    this._layer = Tangram.leafletLayer(this.options).addTo(map);
    var self = this;

    self._layer.scene.subscribe({

      // Check for existing API key at load (before scene renders)
      load: function (scene) {
        var globalKey = self.options.apiKey;

        // If a key has been set (via L.Mapzen.apiKey or options.apiKey),
        // inject the key into any scene file calling Mapzen vector tiles.
        // This will overwrite any existing API keys set in the scene file.
        if (globalKey && APIKeyCheck.isValidMapzenApiKey(globalKey)) {
          self._injectApiKey(scene.config, globalKey);
          return;
        }

        // If no key has been set, make sure key already exists in scene file
        if (self._isApiKeyMissing(scene) === true) {
          APIKeyCheck.throwApiKeyWarning('Mapzen Vector Tiles');
        } else {
          // Carry on. Scene already has or doesn't require an API key.
        }
      }
    });

    // Fire 'loaded' event when Tangram layer has been initialized
    self._layer.on('init', function () {
      self.fire('loaded', {
        layer: self._layer,
        version: Tangram.version
      });
    });
  },

  _setUpApiKey: function () {
    // If there is no api key in the option object, grab the global one.
    this.options.apiKey = this.options.apiKey || L.Mapzen.apiKey;
  },

  /**
   * Adapted from Tangram Frame's API-key check
   *
   * Parses a Tangram scene object for sources that specify a Mapzen
   * vector tile service URL, and checks whether an API key is specified.
   *
   * @param {Object} scene - Tangram scene object
   */
  _isApiKeyMissing: function (scene) {
    var keyIsMissing = false;

    for (var i = 0, j = Object.keys(scene.config.sources); i < j.length; i++) {
      var source = scene.config.sources[j[i]];
      var valid = false;

      // Check if the source URL is a Mapzen-hosted vector tile service
      if (!source.url.match(APIKeyCheck.URL_PATTERN)) continue;

      // Check if the API key is set on the params object
      if (source.url_params && source.url_params.api_key) {
        var apiKey = source.url_params.api_key;
        var globalApi = scene.config.global ? scene.config.global.sdk_mapzen_api_key : '';
        // Check if the global property is valid
        if (apiKey === 'global.sdk_mapzen_api_key' && APIKeyCheck.isValidMapzenApiKey(globalApi)) {
          valid = true;
        } else if (APIKeyCheck.isValidMapzenApiKey(apiKey)) {
          valid = true;
        }
      } else if (source.url.match(/(\?|&)api_key=[-a-z]+-[0-9a-zA-Z_-]{7}/)) {
        // Check if there is an api_key param in the query string
        valid = true;
      }

      if (!valid) {
        keyIsMissing = true;
        break;
      }
    }
    return keyIsMissing;
  },

  /**
   * Adapted from Tangram Play's automatic API-key insertion code
   *
   * Parses a Tangram scene config object for sources that specify a Mapzen
   * vector tile service URL, and injects an API key if the vector tile
   * service is hosted at vector.mapzen.com or tile.mapzen.com.
   *
   * This mutates the original `config` object by necessity. Tangram does not
   * expect it to be passed back in after it's modified.
   *
   * @param {Object} config - Tangram scene config object
   * @param {string} apiKey - the API key to inject
   */
  _injectApiKey: function (config, apiKey) {
    for (var i = 0, j = Object.keys(config.sources); i < j.length; i++) {
      var source = config.sources[j[i]];

      // Check if the source URL is a Mapzen-hosted vector tile service
      if (source.url.match(APIKeyCheck.URL_PATTERN)) {
        // Add a default API key as a url_params setting.
        var params = L.extend({}, source.url_params, {
          api_key: apiKey
        });

        // Mutate the original on purpose.
        source.url_params = params;
      }
    }
  },

  _importScript: function (sSrc) {
    this.oScript = document.createElement('script');
    this.oScript.type = 'text/javascript';
    this.oScript.onerror = this._loadError;

    if (document.currentScript) document.currentScript.parentNode.insertBefore(this.oScript, document.currentScript);
    // If browser doesn't support currentscript position
    // insert script inside of head
    else document.getElementsByTagName('head')[0].appendChild(this.oScript);
    this.oScript.src = sSrc;
  },

  _loadError: function (oError) {
    console.log(oError);
    throw new URIError('The script ' + oError.target.src + ' is not accessible.');
  },

  _hasWebGL: function () {
    try {
      var canvas = document.createElement('canvas');
      return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (x) {
      return false;
    }
  }
});

module.exports = TangramLayer;

module.exports.tangramLayer = function (opts) {
  // Tangram can't have more than one map on a browser context.
  if (!tangramLayerInstance) {
    tangramLayerInstance = new TangramLayer(opts);
  } else {
    // console.log('Only one Tangram map on page can be drawn. Please look at https://github.com/tangrams/tangram/issues/350');
  }
  return tangramLayerInstance;
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./apiKeyCheck":37,"./basemapStyles":38}],45:[function(require,module,exports){
(function (global){
'use strict';
var L = (typeof window !== "undefined" ? window['L'] : typeof global !== "undefined" ? global['L'] : null);

var MapControl = require('./components/mapControl');
var Bug = require('mapzen-scarab');
var Locator = require('./components/locator');
var Geocoder = require('./components/search');
var Hash = require('./components/hash');
var BasemapStyles = require('./components/basemapStyles');
var TangramLayer = require('./components/tangram');
var RoutingMachine = require('./components/routing');

L.Mapzen = module.exports = {
  Map: MapControl,
  map: MapControl.map,
  geocoder: Geocoder.geocoder,
  locator: Locator.locator,
  routing: RoutingMachine.routing,
  bug: Bug,
  hash: Hash.hash,
  HouseStyles: BasemapStyles,
  BasemapStyles: BasemapStyles,
  _tangram: TangramLayer.tangramLayer
};

// Set Icon Path manually (Leaflet detects the path based on where Leaflet script is)
// Leaflet 0.7 and < 1.0 handle image path differently
if (parseFloat(L.version.substring(0, 3)) < 1.0) L.Icon.Default.imagePath = 'https://mapzen.com/js/images';
else L.Icon.Default.prototype.options.imagePath = 'https://mapzen.com/js/images/';

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./components/basemapStyles":38,"./components/hash":39,"./components/locator":40,"./components/mapControl":41,"./components/routing":42,"./components/search":43,"./components/tangram":44,"mapzen-scarab":35}]},{},[45])(45)
});