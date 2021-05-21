/*
* file: igk.winui.articleviewer
* author: C.A.D. BONDJE DOUE
*/
(function(){
	"use strict";
	igk.system.createNS("igk.winui.articleviewer", {
		init: function(){//block size 
			var p = igk.getParentScript();			
			//get childs div and register it
			if (!p)
				return;				
			var h = igk.winui.articleviewer.initViewBox(p, "igk-article-viewer-box", true, false);				
			return h;
		},	
		initViewBox: function(target, classname, updatesize, withanimation)
		{
			if ((!target) || (!classname))
				return;
			
			var p = target;	
			var m_sel = $igk(p).select("."+classname);
			
			if (m_sel.getCount()<=0)
				return;
			
				
			var m_width = 0;//primary width : owner width
			var m_height = 0;
			var d = null;	
			var m_withanimation = false;
				if (typeof(withanimation) == 
					igk.constants.undef)
					m_withanimation = true;
				else
					m_withanimation =  withanimation;
			var dummy = document.createElement("div");
			$igk(dummy).addClass(classname).setCss({display:"none"});
			//dummy.parentNode = p;
			//p.appendChild(dummy);
			//igk.show_prop(dummy);
			
			var j = 0;
			var m_blocks = new Array();
	
			
			$igk(p).setCss(
				{
					overflow:"hidden",
					width: "100%"
				});			
			
			function updateSize(autoAnimate){//update the size
				var columns = [];// column setting
					function getColumnInfo(index)
					{
						if (columns[index])
							return columns[index];
						c  = {
							bottom: 0
						};
						columns.push(c);
						return c;
					};
					function getMaxHeight(){
						var h = 0;
						for(var i = 0; i< columns.length; i++)
						{
							h = Math.max(h, columns[i].bottom);
						}
						return h;
					};
				if ((p.clientWidth != m_width) && (m_blocks.length>0))
				{//moving if changed
					//client width changed...
					
					var c = m_blocks[0];							
					var w = $igk(dummy).getPixel("width", c);					
					var h = $igk(dummy).getPixel("height", c);
					var mLeft = $igk(dummy).getPixel("marginLeft");
					var mRight = $igk(dummy).getPixel("marginRight");
					var mTop = $igk(dummy).getPixel("marginTop");
					var mBottom = $igk(dummy).getPixel("marginBottom");
					var offsetx = mLeft;
					var offsety = mTop;
					m_width = p.clientWidth;
					var v_W = m_width -(mLeft+mRight) ; //large width		
					
					var xs = Math.max(1 , Math.floor(v_W /(w +mRight+mLeft)));
					
					var column = xs;
					var offsetx = 0;
					if (column>1)
						offsetx = mLeft+ (m_width/2)  - (((w +mRight+mLeft)*column)/2);
					else{
						
						offsetx = (m_width/2)  - (((w -(mRight+mLeft))*column)/2);
						w -= (mLeft+mRight);
					}
					
					var j = 0;
					var x = offsetx;
					var y = mTop;
					var item = null;
					var cinfo = 0;//column info
					//update article viewer size
					for(var i = 0; i < m_blocks.length; i++)
					{	
						cinfo = getColumnInfo(i %column);
						if ((i>0) && ((i %column)==0))
						{							
							x = offsetx;
							y +=  (h + mTop);
							j++;
						}	
						item = m_blocks[i];
						//w = $igk(item).getPixel("width", c);
						h = $igk(item).getPixel("height", c);
						//y +=  (h + mTop);
						y = cinfo.bottom + mTop;//.= y+h;
						//console.debug("wid:::::"+w+"x"+h);
						$igk(item).setCss({width: w+"px" , height: h+"px", overflow:"auto", margin:"0px"});
						if (autoAnimate){
							//alert(x);
							igk.animation.animate(item, {left: x+"px", top: y+"px"}, {duration:200, complete:function(){  }});
						}
						else{						
							$igk(item).setCss( {left: x+"px", top: y+"px"});
						}
						x += w + mRight+ mLeft;
						
						cinfo.bottom = y+h;
					}	
					//update height position
					igk.animation.animate(p, {height: (getMaxHeight()+mBottom)+"px"}, 
					{
					duration:200,
					complete: function(){
						if (m_width != p.clientWidth){							
							updateSize(p);
							return;
						}
					}});
				}
			};
			
			m_sel.each(function(){	
				
				var d = this.owner;
				
					m_blocks[j] = d;
					$igk(d).setCss(
					{
					position:"absolute", //make asboslute 					
					verticalAlign:"top"
					});					
					j++;
				
				return true;//allow to continues
			});		
			var m_eventContext = igk.window.RegEventContext(p, $igk(p));			
			if (m_eventContext)
			{
				m_eventContext.reg_window( "resize", function(){
					updateSize(true);
				});
			}
			else{
				alert("igk.winui.articleviewer ... event context failed");
			}						
			//igk.ready(function(){ alert("ready"); updateSize(); });
			//bind to document complete
			var v_out = {
				updateSize: updateSize
			};
			if (updatesize){
				igk.ctrl.bindPreloadDocument("articleviewer", function(){ v_out.updateSize(m_withanimation);});
			}
			return v_out;
		}
	});
})();