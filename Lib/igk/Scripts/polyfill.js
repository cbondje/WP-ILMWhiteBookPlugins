"use strict";
(function(){
	// console.debug('init igk.polyfill.js');
//ie 11 polyfill.js

var _N_ = window.Number.prototype;
var _N = window.Number;

if (!_N.isNaN)
	_N.isNaN = function(i){
		var g = ''+i;
		if (/[0-9]([0-9]?\.[0-9]+|[0-9]+)?/.test(g))
			return 0; 
		return 1;
	}; 
})();