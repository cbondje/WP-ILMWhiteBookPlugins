/*script

*/

(function(){
"uses strict";
igk.system.createNS("igk.ctrl.cookieswarning",{
close:function (id){
	
	var t = document.getElementById(id);
	
	$igk(t).setCss({opacity:"1"}).animate(
		{
			opacity : 0
		},		
		{
			duration:200,
			interval:20,
			update:function(){
			},
			complete: function(){
			t.parentNode.removeChild(t);
			igk.web.setcookies("igk-app-cookieswarning-inform", "true");
			
	}});
	
}
});
})();