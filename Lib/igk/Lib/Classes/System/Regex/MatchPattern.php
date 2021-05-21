<?php
namespace IGK\System\Regex;


class MatchPattern{
    const Int = "[0-9]+";
    const Float = "(([0-9]+)\.[0-9]+)|([0-9]+)(\.[0-9]+)?";
    const DateSearch = "[0-9]{4}((-|\/)([0-9]{2}))?";
}