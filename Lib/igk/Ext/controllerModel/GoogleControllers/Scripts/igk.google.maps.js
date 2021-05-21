//--@
//file: igk.google.maps.js
//author:C.A.D. BONDJE DOUE
//date: 02/01/2018
//version:1.0
//--

"use strict";

(function(){
	
	function hierachilist(p){
		var tab = [];
		var rt = [{i:p,t:''}];
		var q = 0;
		while((q = rt.pop()) !=null){
			for(var u in q.i){
				tab[q.t+u]=(q.t+u).replace(/\./g," ");
				tab.push(q.t+u);				
				if (typeof(q.i[u]) == "object"){
					rt.push({i:q.i[u], t:q.t+u+"."});
				}
			}		
		}
		return tab;		
	}
	function _getProperty(n_h){
		var cl = igk.convert.toHtmlColor(n_h.getComputedStyle("color"));
		var disp = n_h.getComputedStyle("visibility");					
		 // if (cl == dc){
			 // continue;
		 // }
		 
		var ms = [];
		ms.push({color:cl});
		if (disp!="visible")
			ms.push({visibility:'off'});
		return ms;
	}
	function initDisplayStyle(map, t){
		var p = {administrative:{country:1,land_parcel:1,locality:1,neighborhood:1,province:1}, landscape:{man_made:1, natural: {terrain:1,landcoder:1}},
		poi:{attraction:1, business:1,government:1, medical:1,park:1, place_of_worship:1,school:1, sports_complex:1}, 
		road:{arterial:1, hightway:{controlled_access:1}, local:1},
		transit:{line:1,station:{airport:1, bus:1,rail:1}},
		whater:1
		};
		
		var m = {all:1, geometry:{fill:1, stroke:1}, labels:{icon:1,text:{fill:1,stroke:1}}};
		
		var tab = hierachilist(p);
		var ptab = hierachilist(m);
		var styles = [];
		
		var dc =  igk.convert.toHtmlColor(igk.css.getComputedSrcStyle(t,"", "color"));	
		//poi labels icon
		var gc = 34;
		var ok = 1;
		var tset = {};
		var pset ={};
		var out= igk.createNode("div");
		// console.debug(dc);
		var ti = 1;	

		var n_h = $igk(t).add("div").addClass("dispn");
		
		var ms = _getProperty(n_h);
		var bvjs = JSON.stringify(ms);
		// console.debug(bvjs);

		
		for(var l =0; l< tab.length; l++){		
				var ss =  tab[l];
				for(var y = ptab.length-1; y>=0; y--){
					var k = (ss +" "+ptab[y] ).replace(/\./g, " ");	

					// if (k == "poi place_of_worship all"){
						// console.error("sample");
					// }

					
					var parent = ss.substr(0, ss.trim().lastIndexOf("."));
					n_h.o.className = k;
					ms = _getProperty(n_h);
					var vjs = JSON.stringify(ms);
					
					if (bvjs == vjs)continue;
					
					if (parent.length>0){							
						//console.debug("check "+parent);
						if (tset[parent] == vjs){							
							continue;		
						}
						//find parent
						var dcb = 0;
						while (parent.indexOf(".") != -1){
							parent = parent.substr(0, parent.lastIndexOf("."));
							if (tset[parent]==vjs){
								dcb=1;
								break;
							}
						}				
						if (dcb)continue;
						
						//console.debug("not found "+parent);
						//tset[ss] =vjs;
					}else{
						tset[ss] = vjs;
					}
					
					
					 
					out.add("div").setHtml( (ti++)+": "+ss+"=="+k+":"+vjs);
					
					
		
					styles.push({
							featureType:tab[l],
							elementType:ptab[y],
							stylers:ms
						});
					
					// var hc = igk.convert.toHtmlColor(igk.css.getComputedSrcStyle(t,k, "color"));
					// //console.debug("store  : "+ k);
					// if (hc != dc){
						// if (gc<=0)
							// break;
						
						// // var parent = ss.substr(0, ss.trim().lastIndexOf("."));
						// // if (parent.length>0){
							// // if (tset[parent]==hc){
								// // continue;
							// // }
							// // tset[parent] = hc;
						// // }else{
							// // tset[ss] = hc;
						// // }
						
						// //console.debug('parent =='+k+'=='+parent + " "+ss.trim().lastIndexOf("."));
						
						// pset[k]= hc;
						// console.debug("store  : "+ k+ "  ::: "+ptab[y] + " ::: "+ok+ ":::"+hc);
						// styles.push({
							// featureType:tab[l],
							// elementType:ptab[y],
							// stylers:[{color:hc}]
						// });
						// gc--;
						// ok++;
						// break;
					// }
				}
		}
		n_h.remove();
		// console.debug(tset);
		// console.debug(styles.pop());
		// console.debug(styles);
		
		var STYLE_LENGTH = 2047;
		var v_ct = JSON.stringify(styles);
		// json size limitation
		// console.debug(v_ct.length);		
		// igk.io.file.download("application/json", "data.json", v_ct); 
		// map.mapTypes.set("normal", new google.maps.StyledMapType(styles));
		// map.setMapTypeId('normal');		
		//map.setOptions({styles: data.styles});
		t.parentNode.appendChild(out.o);
	}
	
var m_maps=[];
var m_sys=0;

function _initmaps(){
	$igk(document).reg_event("transitionend", function(e){
		
		if ("width|height".indexOf(e.propertyName)!=-1){
			//trigger refresh
			for(var i = 0; i < m_maps.length;i++){
				google.maps.event.trigger(m_maps[i],'resize');
			}
		}
		//console.debug(e);
	});
};

function _markerInfo(){
	var m_marks=[];
	
	igk.appendProperties(this,{
		clearAll:function(){
			//clear all marker
			for(var i=0; i<m_marks.length;i++){
				m_marks[i].setMap(null);
			}
			m_marks = [];
		},
		add:function(m){
			//add marker
			if (m_marks)
			m_marks.push(m);
		}
	});
};
function _refresh(m){	
	google.maps.event.trigger(m,"resize");
}
igk.system.createNS("igk.google.maps", {
	getMapAtIndex:function(i){
		return m_maps[i];
	},
	getMapCount:function(){
		return m_maps.length;
	},
	refresh:function(i){
		var m = m_maps[i];
		if(m)
		google.maps.event.trigger(m,"resize");
	},
	addMarker:function(map, x,y, l,t){	
		//add manual marker
		//@map:map of to set
		//@x: coord lat
		//@y: coord lng
		//@l: label
		//@t: title of the marker
		return new google.maps.Marker({
		position: {lat:x, lng:y},
		map: map,
	    title: t || 'title',
	    label:l,
		icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png'
		});		
	},
	setMarker: function(map, markers, opts){
		//set marker objects
		//@map : map or index
		//@markers : marker to set
		//@opts: options that will store some event like click on the marker
		if (typeof(map)=='number'){
			map = m_maps[map];
		}
		
		if (!map)
			return;
		var v_cm= $igk(map);
		if(v_cm.markers){
			v_cm.markers.clearAll();
		}else{
			v_cm.markers = new _markerInfo();
		}
		var mxi=0, mxa=0, myi=0,mya=0;
		var bounds = 0;
		for(var i = 0; i<markers.length;i++){
			var m = markers[i];			
			var mk = igk.google.maps.addMarker(map, parseFloat( m.lat),parseFloat(m.lng));
			mk.icon = m.icon;
			mk.title= m.title;			
			mk.inf=m;
			
		
			if (opts && opts.click){
				mk.addListener('click', opts.click);
			}
			//bounds.extend(mk.getPosition());
			v_cm.markers.add(mk);
			//caculate the center
			if (i==0){
			mxi = m.lat;
			mxa = m.lat;
			myi = m.lng;
			mya = m.lng;	
				bounds = new google.maps.LatLngBounds(m,m);//mk);
				//bounds.extend(mk.getPosition());
			}else{
			mxi = Math.min(mxi, m.lat);
			mxa = Math.max(mxa, m.lat);
			myi = Math.min(myi, m.lng);
			mya = Math.max(mya, m.lng);
				bounds.extend(mk.getPosition());
			}
			// console.debug('add marker');
			// console.debug(m);
	
		}
		var center = {
			lat : mxi + (mxa - mxi) /2.0,
			lng : myi + (mya - myi) /2.0
		};
		// console.debug("center : ")
		// console.debug(center);
		setTimeout(function(){
		map.fitBounds(bounds);
		//map.setCenter(bounds.getCenter());
		map.setCenter(center);
		_refresh(map);
		},500);
		
		// console.debug(bounds.getCenter().lat()+"," + bounds.getCenter().lng());
		// console.debug(center);
	},
	initMap:function (t) {
	t = t || igk.getParentScript();
	if (!t){
		console.debug('not found');
		return;
	}
	if (!m_sys){
		_initmaps();
		m_sys=1;
	}
	var q = $igk(t);
	
	//default center zoom
	var s = q.getAttribute("igk:data");
	var data = igk.initObj(igk.JSON.parse(s), {
		zoom:7, 
		center:{lat:50.41438075875331, lng:4.904006734252908}}
	);
	
	// console.debug(data);
	
	
	//var myLatLng = {lat: -25.363, lng: 131.044};
	// var myLatLng2 = {lat:50.843004, lng:4.359926};

	var map = new google.maps.Map(t, {
	  zoom: data.zoom,
	  center: data.center,
	  disableDefaultUI:true	  
	  //,language:'ar',
	  //,styles:data.styles, work great
	  //, styles:[
		// {elementType: 'labels.text.fill', stylers: [{color: '#00FF0000'}]} //all text transparent text color
		// ,{featureType: 'water', elementType: 'geometry',  stylers: [{color: '#00000000'}]} // remplissage eaux/lac/riviere
		// ,{featureType: 'water', elementType: 'geometry',  stylers: [{color: '#0065FF'}]} // remplissage eaux/lac/riviere
		// ,{ featureType: 'transit.station',  elementType: 'geometry',  stylers: [{color: '#FF0000'}]}
		// ,{ featureType: 'poi',  elementType: 'all',  stylers: [{visibility: 'off'}]}
		// ,{ featureType: 'transit',  elementType: 'all',  stylers: [{visibility: 'off'}]}
		
		// ,{ featureType: 'road',  elementType: 'geometry',  stylers: [{ color: '#444444'}]}//default road color
		// ,{ featureType: 'road.highway',  elementType: 'labels.text.fill',  stylers: [{color: '#FF0000'}]}
		// ,{ featureType: 'road.local',  elementType: 'labels.text.fill',  stylers: [{ color: '#FF0000'}]}
		// ,{ featureType: 'road',  elementType: 'labels.text.fill',  stylers: [{ color: '#0000FF'}]}
		// ,{ featureType: 'road',  elementType: 'labels',  stylers: [{ color: '#00FFFFFF'}]}
		// ,{ featureType: 'road.arterial',  elementType: 'labels.text.fill',  stylers: [{ color: '#00FFD1'}]}		
		// ,{ featureType: 'landscape',  elementType: 'geometry',  stylers: [{ color: '#555'}]}		
		// ,{ featureType: 'landscape.natural.terrain',  elementType: 'geometry',  stylers: [{ color: '#606060'}]}		
		// // ,{ featureType: 'landscape.natural.landcover ',  elementType: 'geometry',  stylers: [{ color: '#000000'}]}		
		
	  // ]
	});

	//var d = $igk(t).add("div").addClass("dispn options");
	
	//initDisplayStyle(map, t);
	
	
	//var m = igk.google.maps.addMarker(map, 50.841229, 4.354099);
	// m.addListener('click', function(){		
		// console.debug(this);
		// var wnd = new google.maps.InfoWindow({content:'this <b style="color:red"> is </b>e'});
		// wnd.open(map, this);
	// });
	// console.debug(data);
	if (data.markers){
		var mxi=0, mxa=0, myi=0,mya=0;
		for(var i = 0; i<data.markers.length;i++){
			var m = data.markers[i];			
			var mk = igk.google.maps.addMarker(map, parseFloat( m.lat),parseFloat(m.lng));
			mk.icon = data.icons[m.idx] || m.icon || data.iconmark;
			mk.title= m.title;
			mk.addListener('click', (function(mk,data, m){
				return function(d){
					var d = igk.createNode('div');					
					var c = $igk(m.target).first();
					d.add('h2').setHtml(mk.title);
					d.add('div').setHtml( c ? c.getHtml(): "");
					var wnd = new google.maps.InfoWindow({content:d.getHtml()});
					wnd.open(map, this);
				};
			})(mk, data, m));
			
			if (i==0){
			mxi = m.lat;
			mxa = m.lat;
			myi = m.lng;
			mya = m.lng;	
			}else{
			mxi = Math.min(mxi, m.lat);
			mxa = Math.max(mxa, m.lat);
			myi = Math.min(myi, m.lng);
			mya = Math.max(mya, m.lng);
			}
			// console.debug('add marker');
			// console.debug(m);
		}
		var center = {
			lat : mxi + (mxa - mxi) /2.0,
			lng : myi + (mya - myi) /2.0
		};
		// console.debug("center : ")
		// console.debug(center);
		map.setCenter(center);
	}	
	m_maps.push(map);
	if (document.readyState!="complete"){
		//google.maps.event.trigger(map,"resize");
		igk.ready(function(){
			// console.debug(document.readyState);
			google.maps.event.trigger(map,"resize");	
		});
	}
	//console.debug("s");
		
	if (data.styles){
		// console.debug("init maps set style");
		// map.styles =data.styles;	
		map.setOptions({styles: data.styles});
	}
	
	
	}
});

})();