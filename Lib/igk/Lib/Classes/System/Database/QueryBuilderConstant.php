<?php
namespace IGK\System\Database;

/**
 * define use query builder constant
 * @package IGK\System\Database
 */
abstract class QueryBuilderConstant {
    const LeftJoin = "LEFT JOIN";
    const InnerJoin = "INNER JOIN";
    const Joins = "Joins"; 
    const GroupBy = "GroupBy";
    const Limit = "Limit";
    const Distinct = "Distinct";
}