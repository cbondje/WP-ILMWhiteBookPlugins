<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: gd utility class

if(!extension_loaded("gd")){
    if(!function_exists("dl") || !@dl("gd.so")){
        return;
    }
}
define("IGK_GD_SUPPORT", 1);
?><?php



///<summary></summary>
///<param name="src"></param>
///<param name="w"></param>
///<param name="h"></param>
///<param name="type" default="1"></param>
///<param name="compression"></param>
/**
* 
* @param mixed $src
* @param mixed $w
* @param mixed $h
* @param mixed $type the default value is 1
* @param mixed $compression from 0-100 the default value is 0= no compression
*/
function igk_gd_resize_proportional($src, $w, $h, $type=1, $compression=0){
    $ih=imagecreatefromstring($src);
    $W=imagesx($ih);
    $H=imagesy($ih);
    igk_wln($W . " x ".$H);

    $ow=$w;
    $oh=$h;
    $ex=$w/ $W;
    $ey=$h/ $H;
    $ex=min($ex, $ey);
    $x=(( - $W * $ex) + $w)/2.0;
    $y=(( - $H * $ex) + $h)/2.0;
    $img=imagecreatetruecolor($w, $h);
    $black=imagecolorallocate($img, 0, 0, 0);
    imagecolortransparent($img, $black);
    $sh=imagescale($ih, ceil($ex * $W), ceil($ex * $H));
    imagecopy($img, $sh, $x, $y, 0, 0, $w, $h);
    $g=igk_ob_get_func(function($t) use (& $img, $compression){
        if($t == 1){
            $clevel = 9 - ($compression * 9 / 100);
            echo imagepng($img, null, $clevel);
        }
        else{
            echo imagejpeg($img, null, $compression);
        }
    }, $type);
    imagedestroy($sh);
    imagedestroy($ih);
    imagedestroy($img);
    return $g;
}
///<summary>Represente class: IGKGD</summary>
/**
* Represente IGKGD class
*/
class IGKGD {
    private $m_height;
    private $m_himg;
    private $m_width;
    ///<summary></summary>
    ///<param name="w"></param>
    ///<param name="h"></param>
    ///<param name="himg"></param>
    /**
    * 
    * @param mixed $w
    * @param mixed $h
    * @param mixed $himg
    */
    private function __construct($w, $h, $himg){
        $this->m_width=$w;
        $this->m_height=$h;
        $this->m_himg=$himg;
    }
    public function getWidth(){
        if ($this->m_width==-1)
            $this->m_width = imagesx($this->m_himg);
        return $this->m_width;
    }
    public function getHeight(){
        if ($this->m_height==-1)
            $this->m_height = imagesy($this->m_himg);
        return $this->m_height;
    }
    public function setAntialias($b){
        imageantialias($this->m_himg, $b);
    }
    public function setAlphaBlending($b){
        imagealphablending($this->m_himg, $b);
    }
    ///<summary></summary>
    ///<param name="color"></param>
    /**
    * 
    * @param mixed $color
    */
    private function _createColor($color){
        $hcl=null;
        if(is_object($color))
            $hcl=imagecolorallocate($this->m_himg, $color->R, $color->G, $color->B);
        return $hcl;
    }
    ///<summary></summary>
    ///<param name="color"></param>
    /**
    * 
    * @param mixed $color
    */
    public function Clear($color){
        $hcl=imagecolorallocate($this->m_himg, $color->R, $color->G, $color->B);
        imagefill($this->m_himg, 0, 0, $hcl);
        imagecolordeallocate($this->m_himg, $hcl);
    }
    ///<summary></summary>
    ///<param name="color">color float object </param>
    /**
    * 
    * @param mixed $color
    */
    public function Clearf($color){
        if(is_string($color) && !empty($color)){
            $color=IGKColorf::FromString($color);
        }
        $this->Clear((object)array(
            "R"=>$color->R * 255,
            "G"=>$color->G * 255,
            "B"=>$color->B * 255
        ));
    }
    ///<summary></summary>
    ///<param name="webcolor"></param>
    /**
    * 
    * @param mixed $webcolor
    */
    public function Clearw($webcolor){
        $this->Clearf(IGKColorf::FromString($webcolor));
    }
    ///<summary></summary>
    ///<param name="imgwidth"></param>
    ///<param name="imgheight"></param>
    /**
    * 
    * @param mixed $imgwidth
    * @param mixed $imgheight
    */
    public static function Create($imgwidth, $imgheight){
        $v_img=imagecreatetruecolor($imgwidth, $imgheight);
        if(is_resource($v_img)){
            return new IGKGD($imgwidth, $imgheight, $v_img);
        }
        return null;
    }
    public static function CreateFromFile($filename){
        if (file_exists($filename)){
            $g = imagecreatefromstring(file_get_contents($filename));
            if ($g){
                $w = -1;
                $h = -1;
                return new IGKGD($w, $h, $g);
            }
        }
        return null;
    }
    public function renderURL(){
        return "data:image/png;base64,".base64_encode(igk_ob_get_func(function (){ 
            $this->render();
        }));
    }
    public function CreateBuffer(){
       $c = self::Create($this->getWidth(), $this->getHeight());
       $c->Clear((object)["R"=>255, "G"=>255, "B"=>255]);
       // $cl = imagecolorallocatealpha ($c->m_himg,0,255,0, 100);
       $tcl = imagecolorallocate ($c->m_himg, 255,255,255);
       // imagecolordeallocate($c->m_himg, $cl);
       imagealphablending($c->m_himg, true);
       imagecolortransparent ($c->m_himg, $tcl);
       // imagecolordeallocate($c->m_himg, $tcl);
       return $c;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function Dispose(){
        imagedestroy($this->m_himg);
    }
    public function DrawLine($color, $x1, $y1, $x2, $y2){
        $hcl=$this->_createColor($color);
        imageline($this->m_himg, $x1, $y1, $x2, $y2, $hcl);
        imagecolordeallocate($this->m_himg, $hcl);
    }
    ///<summary></summary>
    ///<param name="color"></param>
    ///<param name="center"></param>
    ///<param name="radius"></param>
    /**
    * 
    * @param mixed $color
    * @param mixed $center
    * @param mixed $radius
    */
    public function DrawEllipse($color, $center, $radius){
        $hcl=$this->_createColor($color);
        imageellipse($this->m_himg, $center->X, $center->Y, abs($radius->X * 2.0), abs($radius->Y * 2.0), $hcl);
        imagecolordeallocate($this->m_himg, $hcl);
    }
    ///<summary></summary>
    ///<param name="himg"></param>
    ///<param name="x"></param>
    ///<param name="y"></param>
    ///<param name="w" default="-1"></param>
    ///<param name="h" default="-1"></param>
    /**
    * 
    * @param mixed $himg
    * @param mixed $x
    * @param mixed $y
    * @param mixed $w the default value is -1
    * @param mixed $h the default value is -1
    */
    public function DrawImage($himg, $x, $y, $w=-1, $h=-1){
        if (get_class($himg) === __CLASS__){
            $himg = $himg->m_himg;
        }

        $rs=(($w == -1) && ($h == -1));
        $w= ($w == -1) ? imagesx($himg): $w;
        $h= ($h == -1) ? imagesy($himg): $h;
        if(!$rs){
            //$img=imagecreatetruecolor($w, $h);
            $sh=imagescale($himg, $w, $h);
            imagecopy($this->m_himg, $sh, $x, $y, 0, 0, $w, $h);
            //imagedestroy($img);
            imagedestroy($sh);
        }
        else{

            imagealphablending($himg, false);
            imagealphablending($this->m_himg, false);
            imagecopy($this->m_himg, $himg, $x, $y, 0, 0, $w, $h);
        }
    }
    public function FillImage($himg, $x, $y, $w=-1, $h=-1){
        $this->DrawImage($himg, $x, $y, $w, $h);
        return;

        if (get_class($himg) === __CLASS__){
            $himg = $himg->m_himg;
        }

        $rs=(($w == -1) && ($h == -1));
        $w= ($w == -1) ? imagesx($himg): $w;
        $h= ($h == -1) ? imagesy($himg): $h;
        if(!$rs){
            //$img=imagecreatetruecolor($w, $h);
            $sh=imagescale($himg, $w, $h);
            imagecopymerge($this->m_himg, $sh, $x, $y, 0, 0, $w, $h,50);
            //imagedestroy($img);
            imagedestroy($sh);
        }
        else
            imagecopymerge($this->m_himg, $himg, $x, $y, 0, 0, $w, $h,10);
    }
    ///<summary></summary>
    ///<param name="color"></param>
    ///<param name="rect"></param>
    ///<param name="y" default="null"></param>
    ///<param name="width" default="null"></param>
    ///<param name="height" default="null"></param>
    /**
    * 
    * @param mixed $color
    * @param mixed $rect
    * @param mixed $y the default value is null
    * @param mixed $width the default value is null
    * @param mixed $height the default value is null
    */
    public function DrawRectangle($color, $rect, $y=null, $width=null, $height=null){
        if(is_string($color))
            $color=IGKColor::FromString($color);
        if(!is_object($rect)){
            $rect =new IGKRectanglef($rect, $y, $width, $height);
        }
        $hcl=imagecolorallocate($this->m_himg, $color->R, $color->G, $color->B);

        imagerectangle($this->m_himg, $rect->X, $rect->Y, $rect->X + $rect->Width, $rect->y + $rect->Height, $hcl);
        imagecolordeallocate($this->m_himg, $hcl);
    }
    public function Scale($scalex, $scaley){

    }
    ///<summary></summary>
    ///<param name="string"></param>
    ///<param name="font"></param>
    ///<param name="size"></param>
    ///<param name="x"></param>
    ///<param name="y"></param>
    ///<param name="color"></param>
    /**
    * 
    * @param mixed $string
    * @param mixed $font
    * @param mixed $size
    * @param mixed $x
    * @param mixed $y
    * @param mixed $color
    */
    public function DrawString($string, $font, $size, $x, $y, $color){
        $hcl=imagecolorallocate($this->m_himg, $color->R, $color->G, $color->B);
        $r=imagefttext($this->m_himg, $size, 0, $x, $y, $hcl, $font, $string);
        imagecolordeallocate($this->m_himg, $hcl);
        return (object)array(
            "x"=>$r[0],
            "y"=>$r[7],
            "width"=>abs($r[0] - $r[4]),
            "height"=>abs($r[5] - $r[1])
        );
    }
    ///<summary></summary>
    ///<param name="color"></param>
    ///<param name="center"></param>
    ///<param name="radius"></param>
    /**
    * 
    * @param mixed $color
    * @param mixed $center
    * @param mixed $radius
    */
    public function FillEllipse($color, $center, $radius){
        $hcl=$this->_createColor($color);
        imagefilledellipse($this->m_himg, $center->X, $center->Y, abs($radius->X * 2.0), abs($radius->Y * 2.0), $hcl);
        imagecolordeallocate($this->m_himg, $hcl);
    }
    ///<summary></summary>
    ///<param name="color"></param>
    ///<param name="rectx"></param>
    ///<param name="y" default="null"></param>
    ///<param name="width" default="null"></param>
    ///<param name="height" default="null"></param>
    /**
    * 
    * @param mixed $color
    * @param mixed $rectx
    * @param mixed $y the default value is null
    * @param mixed $width the default value is null
    * @param mixed $height the default value is null
    */
    public function FillRectangle($color, $rectx, $y=null, $width=null, $height=null){
        if(is_string($color))
            $color=IGKColor::FromString($color);
        if(!is_object($rectx)){
            $rectx=new IGKRectanglef($rectx, $y, $width, $height);
        }
        $hcl=imagecolorallocate($this->m_himg, $color->R, $color->G, $color->B);
        imagefilledrectangle($this->m_himg, $rectx->X, $rectx->Y, $rectx->X + $rectx->Width, $rectx->Y + $rectx->Height, $hcl);
        imagecolordeallocate($this->m_himg, $hcl);
    }
    ///<summary></summary>
    ///<param name="himg"></param>
    /**
    * 
    * @param mixed $himg
    */
    public static function FromGd($himg){
        return new IGKGD(imagesx($himg), imagesy($himg), $himg);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function render($type=null, $quality=null){
        if (($type===null) || ($type=="PNG"))
            return imagepng($this->m_himg);
        return imagejpeg($this->m_himg, null, $quality);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function RenderText(){
        IGKOb::Start();
        $this->Render();
        $c=IGKOb::Content();
        IGKOb::Clear();
        return $c;
    }
    public function setLineWidth($size){
        imagesetthickness($this->m_himg, $size);
    }
    public function getClip(){
        return imagegetclip($this->m_himg); //, $x, $y, $x+ $w, $y+$h);
    }
    public function clip($x, $y, $w, $h){
        imagesetclip($this->m_himg, $x, $y, $x+ $w, $y+$h);
    }
    public function resetclip(){
        imagesetclip($this->m_himg, 0,0, imagesx($this->m_himg), imagesy($this->m_himg));
    }
    public function FillPolygon($points, $color){
        $pt = count($points)/2;
        if (!($pt>=3)){
            return;
        }
        $allocColor = $this->_allocColor($color);
        imagefilledpolygon($this->m_himg, $points, $pt, $allocColor);
        imagecolordeallocate($this->m_himg, $allocColor);
    }
    public function DrawPolygon($points, $color){
        $pt = count($points)/2;
        if (!($pt>=3)){
            return;
        }
        $allocColor = $this->_allocColor($color);
        imagepolygon($this->m_himg, $points, $pt, $allocColor);
        imagecolordeallocate($this->m_himg, $allocColor);
    }
    private function _allocColor($color){
        if(is_string($color))
             $color=IGKColor::FromString($color);
        $hcl=imagecolorallocate($this->m_himg,  $color->R, $color->G, $color->B);
        return $hcl;
    }
}


