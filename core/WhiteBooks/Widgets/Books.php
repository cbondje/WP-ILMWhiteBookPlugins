<?php
namespace ILYEUM\WhiteBooks\Widgets;

use ILYEUM\WhiteBooks\Models\Books as BookModel;
use ReflectionException;
use WP_Widget;
use function ilm_getv as getv;
use function ilm_getctrl as getctrl;
use function ilm_resources_gets as __;

/**
 * book details widget
 * @package 
 */
class Books extends WidgetBase{
    public function __construct()
    {
        parent::__construct("Books");
    }
    /**
     * view book details
     * @return void 
     */
    public function form($instance){ 
    }
     /**
     * update book info
     * @return void 
     */
    public function update($newinstance, $oldinstance){
        return [];
    }
    /**
     * present the widget
     * @param mixed $instance 
     * @param mixed $args 
     * @return void 
     * @throws ReflectionException 
     */
    public function widget($instance, $args){    
        $books = BookModel::select_all();   
        $d = igk_createnode('div');
        $d->h2()->setClass("widget-title")->Content = __("Books");
        $dv = $d->div();
        if ($books){ 
            getctrl()->loader->view("books", ["books"=>$books]);
        }else{
            $dv->Content = __("Books not found");
        }
        $d->renderAJX();
    }
   
}