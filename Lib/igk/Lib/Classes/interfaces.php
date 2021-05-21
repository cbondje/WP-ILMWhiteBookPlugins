<?php



///<summary>represent IIAction Result interface </summary>
/**
* represent IIAction Result interface
*/
interface IIGKActionResult{
    ///<summary></summary>
    /**
    * 
    */
    function index();
}




///<summary>Represente interface: IIGKConfigController</summary>
/**
* Represente IIGKConfigController interface
*/
interface IIGKConfigController {
    ///<summary></summary>
    /**
    * 
    */
    function showConfig();
}
///<summary>Represente interface: IIGKController</summary>
/**
* Represente IIGKController interface
*/
interface IIGKController{
    ///<summary></summary>
    /**
    * 
    */
    function getName();
    ///<summary></summary>
    /**
    * 
    */
    function getTargetNode();
    ///<summary></summary>
    /**
    * 
    */
    function getTargetNodeId();
    ///<summary></summary>
    /**
    * 
    */
    function initMenu();
    ///<summary></summary>
    /**
    * 
    */
    function View();
}
///<summary>Represente interface: IIGKControllerInitListener</summary>
/**
* Represente IIGKControllerInitListener interface
*/
interface IIGKControllerInitListener{
    ///<summary></summary>
    ///<param name="name"></param>
    /**
    * 
    * @param mixed $name
    */
    function addDir($name);
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="source"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $source
    */
    function addSource($name, $source);
}
///<summary>Represente interface: IIGKCssCtrlHost</summary>
/**
* Represente IIGKCssCtrlHost interface
*/
interface IIGKCssCtrlHost{
    ///<summary></summary>
    /**
    * 
    */
    function bindCss();
    ///<summary></summary>
    ///<param name="doc" default="null"></param>
    /**
    * 
    * @param mixed $doc the default value is null
    */
    function getIsCssActive($doc=null);
}
///<summary>Represente interface: IIGKCtrlDirManagement</summary>
/**
* Represente IIGKCtrlDirManagement interface
*/
interface IIGKCtrlDirManagement{
    ///<summary></summary>
    /**
    * 
    */
    function getDataDir();
    ///<summary></summary>
    /**
    * 
    */
    function getDeclaredDir();
    ///<summary></summary>
    /**
    * 
    */
    function getName();
    ///<summary></summary>
    /**
    * 
    */
    function getResourcesDir();
    ///<summary></summary>
    /**
    * 
    */
    function getStylesDir();
    ///<summary></summary>
    /**
    * 
    */
    function getViewDir();
}
///<summary>Represente interface: IIGKDataAdapter</summary>
/**
* Represente IIGKDataAdapter interface
*/
interface IIGKDataAdapter{
    ///<summary></summary>
    /**
    * 
    */
    function selectCount();
    ///<summary></summary>
    /**
    * 
    */
    function createDb();
    ///<summary></summary>
    /**
    * 
    */
    function setForeignKeyCheck();
}
///<summary>Represente interface: IIGKDataTable</summary>
/**
* Represente IIGKDataTable interface
*/
interface IIGKDataTable{}
///<summary>Represente interface: IIGKDbUtility</summary>
/**
* Represente IIGKDbUtility interface
*/
interface IIGKDbUtility{
    ///<summary></summary>
    ///<param name="table"></param>
    ///<param name="obj"></param>
    ///<param name="leaveopen" default="false"></param>
    /**
    * 
    * @param mixed $table
    * @param mixed $obj
    * @param mixed $leaveopen the default value is false
    */
    function insertIfNotExists($table, $obj, $leaveopen=false);
}
interface IIGKDbModel{
	function getTable();
}

///<summary>engine form builder interface</summary>
///<note>all id are mixed of string or array properties</summary>
/**
* engine form builder interface
*/
interface IIGKFormBuilderEngine{
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="type" default="'submit'"></param>
    ///<param name="text" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $type the default value is 'submit'
    * @param mixed $text the default value is null
    */
    function addButton($id, $type='submit', $text=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    ///<param name="attribs" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    * @param mixed $attribs the default value is null
    */
    function addCheckbox($id, $value=null, $attribs=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="type" default="'text'"></param>
    ///<param name="style" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $type the default value is 'text'
    * @param mixed $style the default value is null
    */
    function addControl($id, $type='text', $style=null);
    ///<summary></summary>
    /**
    * 
    */
    function addGroup();
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="class" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $class the default value is null
    */
    function addLabel($id, $class=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    ///<param name="type" default="'text'"></param>
    ///<param name="style" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    * @param mixed $type the default value is 'text'
    * @param mixed $style the default value is null
    */
    function addLabelControl($id, $value=null, $type='text', $style=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="entries"></param>
    ///<param name="filter" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $entries
    * @param mixed $filter the default value is null
    */
    function addLabelSelect($id, $entries, $filter=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    */
    function addLabelTextarea($id, $value=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    ///<param name="attribs" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    * @param mixed $attribs the default value is null
    */
    function addRadioButton($id, $value=null, $attribs=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    */
    function addTextarea($id, $value=null);
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    ///<param name="attribs" default="null"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    * @param mixed $attribs the default value is null
    */
    function addTextfield($id, $value=null, $attribs=null);
    ///<summary></summary>
    /**
    * 
    */
    function getView();
    ///<summary></summary>
    ///<param name="host"></param>
    /**
    * 
    * @param mixed $host
    */
    function setView($host);
}
///<summary>Represente interface: IIGKFrameController</summary>
/**
* Represente IIGKFrameController interface
*/
interface IIGKFrameController{
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="frame"></param>
    ///<param name="remove" default="true"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $frame
    * @param mixed $remove the default value is true
    */
    function ContainFrame($id, $frame, $remove=true);
}
///<summary>Represente interface: IIGKHtmlComponent</summary>
/**
* Represente IIGKHtmlComponent interface
*/
interface IIGKHtmlComponent{
    ///<summary></summary>
    /**
    * 
    */
    function getComponentId();
    ///<summary></summary>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $uri
    */
    function getComponentUri($uri);
    ///<summary></summary>
    /**
    * 
    */
    function getController();
    ///<summary></summary>
    ///<param name="listener"></param>
    ///<param name="param" default="null"></param>
    /**
    * 
    * @param mixed $listener
    * @param mixed $param the default value is null
    */
    function setComponentListener($listener, $param=null);
}
///<summary>use to indicate that an element can store a cookie to client size</summary>
/**
* use to indicate that an element can store a cookie to client size
*/
interface IIGKHtmlCookieItem{
    ///<summary></summary>
    /**
    * 
    */
    function getCookieId();
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    function setCookieId($v);
}
///<summary>Represente interface: IIGKHtmlGetValue</summary>
/**
* Represente IIGKHtmlGetValue interface
*/
interface IIGKHtmlGetValue {
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    function getValue($options=null);
}
///<summary>Represente interface: IIGKHtmlLoadContent</summary>
/**
* Represente IIGKHtmlLoadContent interface
*/
interface IIGKHtmlLoadContent {
    ///<summary></summary>
    ///<param name="data"></param>
    ///<param name="context" default="null"></param>
    /**
    * 
    * @param mixed $data
    * @param mixed $context the default value is null
    */
    function LoadExpression($data, $context=null);
    ///<summary></summary>
    ///<param name="file"></param>
    /**
    * 
    * @param mixed $file
    */
    function LoadFile($file);
    ///<summary></summary>
    ///<param name="ctr"></param>
    ///<param name="article"></param>
    /**
    * 
    * @param mixed $ctr
    * @param mixed $article
    */
    function LoadView($ctr, $article);
}
///<summary>Represente interface: IIGKHtmlUriItem</summary>
/**
* Represente IIGKHtmlUriItem interface
*/
interface IIGKHtmlUriItem{
    ///<summary></summary>
    /**
    * 
    */
    function getUri();
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    function setUri($v);
}
interface IIGKListener{
    function register($name, $callback);
}
///<summary>Represente interface: IIGKMailAttachmentContainer</summary>
/**
* Represente IIGKMailAttachmentContainer interface
*/
interface IIGKMailAttachmentContainer{
    ///<summary></summary>
    ///<param name="content"></param>
    ///<param name="type" default="IGK_CT_PLAIN_TEXT"></param>
    ///<param name="cid" default="null"></param>
    /**
    * 
    * @param mixed $content
    * @param mixed $type the default value is IGK_CT_PLAIN_TEXT
    * @param mixed $cid the default value is null
    */
    function attachContent($content, $type=IGK_CT_PLAIN_TEXT, $cid=null);
    ///<summary></summary>
    ///<param name="file"></param>
    ///<param name="type" default="IGK_CT_PLAIN_TEXT"></param>
    ///<param name="cid" default="null"></param>
    /**
    * 
    * @param mixed $file
    * @param mixed $type the default value is IGK_CT_PLAIN_TEXT
    * @param mixed $cid the default value is null
    */
    function attachFile($file, $type=IGK_CT_PLAIN_TEXT, $cid=null);
}
///<summary>notification message</summary>
/**
* notification message
*/
interface IIGKNotifyMessage {
    ///<summary></summary>
    ///<param name="message"></param>
    /**
    * 
    * @param mixed $message
    */
    function addError($message);
    ///<summary></summary>
    ///<param name="keymessage"></param>
    /**
    * 
    * @param mixed $keymessage
    */
    function addErrorr($keymessage);
    ///<summary></summary>
    ///<param name="message"></param>
    /**
    * 
    * @param mixed $message
    */
    function addInfo($message);
    ///<summary></summary>
    ///<param name="keymessage"></param>
    /**
    * 
    * @param mixed $keymessage
    */
    function addInfor($keymessage);
    ///<summary></summary>
    ///<param name="message"></param>
    /**
    * 
    * @param mixed $message
    */
    function addMsg($message);
    ///<summary></summary>
    ///<param name="keymessage"></param>
    /**
    * 
    * @param mixed $keymessage
    */
    function addMsgr($keymessage);
    ///<summary></summary>
    ///<param name="message"></param>
    /**
    * 
    * @param mixed $message
    */
    function addSuccess($message);
    ///<summary></summary>
    ///<param name="keymessage"></param>
    /**
    * 
    * @param mixed $keymessage
    */
    function addSuccessr($keymessage);
    ///<summary></summary>
    ///<param name="message"></param>
    /**
    * 
    * @param mixed $message
    */
    function addWarning($message);
    ///<summary></summary>
    ///<param name="keymessage"></param>
    /**
    * 
    * @param mixed $keymessage
    */
    function addWarningr($keymessage);
}
///<summary>Represente interface: IIGKParamHostService</summary>
/**
* Represente IIGKParamHostService interface
*/
interface IIGKParamHostService{
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="default" default="null"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $default the default value is null
    */
    function getParam($name, $default=null);
    ///<summary></summary>
    /**
    * 
    */
    function getParamKeys();
    ///<summary></summary>
    /**
    * 
    */
    function resetParam();
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $value
    */
    function setParam($name, $value);
}
///<summary>Represente interface: IIGKParentDocumentHost</summary>
/**
* Represente IIGKParentDocumentHost interface
*/
interface IIGKParentDocumentHost{
    ///<summary></summary>
    ///<param name="document"></param>
    /**
    * 
    * @param mixed $document
    */
    function BindScriptTo($document);
    ///<summary></summary>
    /**
    * 
    */
    function getDoc();
}
///<summary> represent query result interface </summary>
/**
*  represent query result interface
*/
interface IIGKQueryResult{
    ///<summary></summary>
    ///<param name="index"></param>
    /**
    * 
    * @param mixed $index
    */
    function getRowAtIndex($index);
    ///<summary></summary>
    /**
    * 
    */
    function getRows();
}
///<summary>Represente interface: IIGKSystemUser</summary>
/**
* Represente IIGKSystemUser interface
*/
interface IIGKSystemUser {
    ///<summary></summary>
    /**
    * 
    */
    function getLogin();
}
///<summary>Represente interface: IIGKUriActionListener</summary>
/**
* Represente IIGKUriActionListener interface
*/
interface IIGKUriActionListener{
    ///<summary></summary>
    ///<param name="e"></param>
    ///<param name="render" default="1"></param>
    /**
    * 
    * @param mixed $e
    * @param mixed $render the default value is 1
    */
    function invokeUriPattern($e, $render=1);
    ///<summary></summary>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $uri
    */
    function matche($uri);
}
///<summary>Represente interface: IIGKUriActionRegistrableController</summary>
/**
* Represente IIGKUriActionRegistrableController interface
*/
interface IIGKUriActionRegistrableController{
    ///<summary></summary>
    /**
    * 
    */
    function getBasicUriPattern();
    ///<summary>registrated invocation uri </summary>
    /**
    * registrated invocation uri
    */
    function getRegInvokeUri();
    ///<summary></summary>
    /**
    * 
    */
    function getRegUriAction();
}
///<summary>Represente interface: IIGKUserController</summary>
/**
* Represente IIGKUserController interface
*/
interface IIGKUserController{
    ///<summary></summary>
    /**
    * 
    */
    function connect();
    ///<summary></summary>
    /**
    * 
    */
    function signup();
}
///<summary>Represente interface: IIGKWebAdministrativeCtrl</summary>
/**
* Represente IIGKWebAdministrativeCtrl interface
*/
interface IIGKWebAdministrativeCtrl {
    ///<summary></summary>
    /**
    * 
    */
    function getConfigNode();
}
///<summary>Represente interface: IIGKWebPageChildCtrontroller</summary>
/**
* Represente IIGKWebPageChildCtrontroller interface
*/
interface IIGKWebPageChildCtrontroller{
    ///<summary></summary>
    /**
    * 
    */
    function getWebParentCtrl();
}
///<summary>db manager interface</summary>
/**
* db manager interface
*/
interface IIGKdbManager {
    ///<summary></summary>
    ///<param name="leaveopen" default="false"></param>
    /**
    * 
    * @param mixed $leaveopen the default value is false
    */
    function close($leaveopen=false);
    ///<summary></summary>
    /**
    * 
    */
    function connect();
    ///<summary></summary>
    ///<param name="tableName"></param>
    /**
    * 
    * @param mixed $tableName
    */
    function dropTable($tableName);
}
///<summary>represent a module listener interface</summary>
/**
* represent a module listener interface
*/
interface IIGKAppModuleListener extends IIGKConfigController{
    const DATA=1;
    const DATA2=self::DATA + 5;
    const DATA3=self::DATA2;
    ///<summary></summary>
    /**
    * 
    */
    function getBaseUri();
    ///<summary></summary>
    /**
    * 
    */
    function getConfigs();
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    function getTable($n);
}
///<summary>Represente interface: IIGKDataController</summary>
/**
* Represente IIGKDataController interface
*/
interface IIGKDataController extends IIGKController {
    ///<summary></summary>
    /**
    * 
    */
    function getDataAdapterName();
    ///<summary>return primary data table info or mixed array of table info</summary>
    /**
    * return primary data table info or mixed array of table info
    */
    function getDataTableInfo();
    ///<summary></summary>
    /**
    * 
    */
    function getDataTableName();
}
///<summary>Represente interface: IIGKWebController</summary>
/**
* Represente IIGKWebController interface
*/
interface IIGKWebController extends IIGKController {
    ///<summary></summary>
    /**
    * 
    */
    function getChilds();
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    function regChildController($ctrl);
    ///<summary></summary>
    ///<param name="ctrl"></param>
    /**
    * 
    * @param mixed $ctrl
    */
    function unregChildController($ctrl);
}
///<summary>Represente interface: IIGKQueryConditionalExpression</summary>
/**
* Represente IIGKQueryConditionalExpression interface
*/
interface IIGKQueryConditionalExpression extends IIGKHtmlGetValue {
    ///<summary></summary>
    ///<param name="expression"></param>
    ///<param name="operator" default="AND"></param>
    /**
    * 
    * @param mixed $expression
    * @param mixed $operator the default value is "AND"
    */
    function add($expression, $operator="AND");
    ///<summary></summary>
    /**
    * 
    */
    function getCount();
    ///<summary></summary>
    ///<param name="expression"></param>
    /**
    * 
    * @param mixed $expression
    */
    function remove($expression);
}
///<summary>Represente interface: IIGKWebPageController</summary>
/**
* Represente IIGKWebPageController interface
*/
interface IIGKWebPageController extends IIGKWebController {
    ///<summary></summary>
    ///<param name="file"></param>
    /**
    * 
    * @param mixed $file
    */
    function loadWebTheme($file);
    ///<summary></summary>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $uri
    */
    function manageErrorUriRequest($uri);
}