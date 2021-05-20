<?php
namespace ILYEUM\WhiteBooks\Widgets;

use ILYEUM\WhiteBooks\Models\Books;
use ReflectionException;
use WP_Widget;
use function ilm_getv as getv;
use function ilm_getctrl as getctrl;
use function ilm_resources_gets as __;

/**
 * book details widget
 * @package 
 */
class BookDetails extends WidgetBase{
    public function __construct()
    {
        parent::__construct("show book details");
    }
    /**
     * view book details
     * @return void 
     */
    public function form($instance){
        $book_id = getv($instance, "book_id");
        $d = igk_createnode('div');
        $d->p()->Content = "Book Details";
        $d->fields([
            $this->get_field_name("book_id")=>["value"=>$book_id, "label_text"=>"BookId", "attribs"=>[
                "class"=>"widefat title"
            ]],
            ],null, "p");
        $d->renderAJX();
    }
     /**
     * update book info
     * @return void 
     */
    public function update($newinstance, $oldinstance){
        return [
            "book_id"=>sanitize_text_field(getv($newinstance, "book_id"))
        ];
    }
    /**
     * present the widget
     * @param mixed $instance 
     * @param mixed $args 
     * @return void 
     * @throws ReflectionException 
     */
    public function widget($instance, $args){ 
        $book_id = getv($args, "book_id");
        $book = $book_id ? Books::select_row($book_id) : null;
        $d = igk_createnode('div');
        $d->h2()->setClass("widget-title")->Content = __("Book Details");
        $dv = $d->div();
        if ($book){
            $dv->obdata(function()use($book){
                getctrl()->loader->view("book.details", ["book"=>$book]);
            });
        }else{
            $dv->Content = __("Book not found");
            $dv->div()->Content = "id:".$book_id;
            $dv->div()->Content = json_encode($args);
        }
        $d->renderAJX();
    }
   
}