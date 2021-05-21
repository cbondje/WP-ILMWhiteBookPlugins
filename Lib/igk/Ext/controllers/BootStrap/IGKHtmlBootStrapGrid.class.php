<?php

class IGKHtmlBootStrapGrid extends IGKHtmlItem
{
	private $m_row;
	private $m_cell;

	public function __construct(){
		parent::__construct("div");
		$this["class"]="igk-grid";
	}
	public function addRow(){
		$t = $this->add("div");
		$t["class"]="igk-grid-row";
		$this->m_row = $t;
		return $t;
	}
	public function addCell($hoverColor=null){

		$r = $this->m_row == null? $this->addRow() : $this->m_row;
		$t = new IGKHtmlBootStrapGridCell();
		$t["class"]="igk-grid-cell";
		$this->m_cell = $t;
		$t->setHoverColor ($hoverColor);
		$r->add($t);
		return $t;
	}
	public function innerHTML(& $options =null){
		$s = parent::innerHTML();
		$c =  IGKHtmlItem::CreateWebNode("script");
		$c->Content = <<<EOF
(function(ps){ \$ns_igk.ready(function(){
var q = ps.select(":igk-cell-hover-color");
q.reg_event("mouseover", function(){
var c = this.getAttribute("igk-cell-hover-color");
q["igk-cell-hover-oldcl"] = \$igk(this).getComputedStyle("backgroundColor");
\$igk(this).firstChild().setCss({backgroundColor: c});
}).reg_event("mouseout", function(){ \$igk(this).firstChild().setCss({backgroundColor: this.getAttribute("igk-cell-hover-oldcl")} ); } );
;
});})(\$igk(\$ns_igk.getParentScript()));
EOF;
		$s .= $c->Render($options);
		return $s;
	}
}
