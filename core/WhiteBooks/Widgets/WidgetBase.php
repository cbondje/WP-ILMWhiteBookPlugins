<?php
namespace ILYEUM\WhiteBooks\Widgets;
use WP_Widget;
use function ilm_resources_gets as __;
use function ilm_getv as getv;
/**
 * book details widget
 * @package 
 */
abstract class WidgetBase extends WP_Widget{
    public function __construct($desc="")
    {
        $id = str_replace("\\", "/", static::class);
        parent::__construct(
            str_replace("/", "_", $id),
            "ILM - ".basename($id),
            [
                "description"=>__($desc)
            ]
        );
    }
    public function widget($args, $instance){
    }
    public function form($instance){        
    }
    public function update($new_instance, $old_instance){ 
        return [];      
    }
}