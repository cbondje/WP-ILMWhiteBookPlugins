/*
file: igk.media.webplayer.js
player div that render image in multiple by format
*/
igk.system.createNS("igk.media.webplayer", {
init: function(target, uri, width, height){
	//init web player
	target.style.backgroundImage = "url('"+uri+"')";
	//target.style.border = "1px solid black";
	var img = document.createElement("img");
	img.src = uri;
	var m_imgw = img.width;
	
	img = null;
	target.style.backgroundRepeat = "no-repeat";
	target.style.width = width? width: "32px";
	target.style.height = height? height: "32px";
	
	function __construct(){
		var m_timeout = null;
		var m_isplaying = false;
		var m_pos = 0;
		var m_width = m_imgw;
		this.target = target;
		this.play = function(){
			var q = this;			
			m_isplaying = true;
			q.update();
		};
		this.update = function(){
			
			if (m_isplaying)
			{
				m_pos = (m_pos +32) % m_width;				
				var q = this;
				this.target.style.backgroundPosition = -m_pos+"px 0px";
				m_timeout = setTimeout(function(){ q.update() } , 72);
			}
		};
		this.pause = function(){
			clearTimeout(m_timeout);
			m_isplaying = false;
		};
		this.stop = function(){
			this.pause();
			m_pos = 0;
		};
		
		
	}
	var o = new __construct();
	o.play();
	return o;
}
});