<?php
namespace ILYEUM\wp\Models;

use ILYEUM\WhiteBooks\Models\ModelBase;

class WP_Post extends WP_ModelBase{
    protected $table = "%sysprefix%posts";

    protected $primaryKey = "ID";
}