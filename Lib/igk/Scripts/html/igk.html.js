window.igk.system.createNS("igk.html",{
	appendFile  : function(target, fileobj){
		switch(fileobj.type)
		{
			case "image/jpeg":
				var img= 	document.createElement("img");
				img.src = window.URL.createObjectURL(fileobj);
				img.alt = fileobj.name;
				target.appendChild(img);
				break;
		}
	},
	URL : window.URL? window.URL : window.webkitURL
});