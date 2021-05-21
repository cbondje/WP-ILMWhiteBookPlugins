<?php
//init controller zone
$CF = igk_ctrl_zone_init(__FILE__);

function igk_html_node_layoutPresentation($type="1-2"){
$CF = igk_ctrl_zone(__FILE__);
$c = igk_createNode("div");
$c->setClass("igk-winui-layoutp t-".$type);

$t = array("t"=>$c);
igk_include_file($CF->getViewDir()."/view.".$type.".".IGK_DEFAULT_VIEW_EXT, $t);

$c->addOnRenderCallback(igk_create_expression_callback(
	file_get_contents(dirname(__FILE__)."/.style.func")
	,
	array("node"=>$c,
	"type"=>$type,
	"CF"=>$CF
	)
	));
	return $c;
}


function igk_html_demo_layoutPresentation($t){

	// $p = $t->addContainer()->addRow()->addCol("igk-col-4-2 igk-col-sm-4-4 igk-col-xsm-4-4 no-padding")->addDiv()->addlayoutPresentation();
	$p = $t->addlayoutPresentation();
	$p->mainCell->Content =igk_html_from_string(<<<EOF
 Lorem ipsum dolor sit amet, lacinia congue nunc turpis, feugiat amet eget nullam nulla ac et, eros est mi donec duis fermentum aliquet, mauris eget magna habitant. Fusce donec nonummy metus turpis, dolor faucibus faucibus at. Leo ipsum magna vestibulum, senectus non ut, dui massa a sed ipsum ac suscipit, integer iaculis vestibulum ante rutrum pellentesque, sed accumsan eu molestie. Sed varius pretium, mauris adipiscing dapibus venenatis nullam. Rhoncus class excepturi neque eget, amet neque laoreet sagittis lorem consequat, lacus adipiscing sed. Viverra nam cras in diam, amet ante ipsam magna, aliquam id sit fusce lorem magna enim, velit nisl curabitur lectus sit diam neque, ligula libero donec ipsum ut pellentesque duis. Lacus viverra rutrum turpis sed, lacus amet porta montes mauris volutpat eu, class vel. Nullam ipsum sit, suspendisse neque urna augue lacus id. Enim id. Libero at. Porta dui consectetuer, nunc metus, lectus molestias dolor, ornare id sagittis, hendrerit posuere mi proin eleifend placerat. Sit vel, quam nonummy nibh lorem litora, pellentesque lectus vestibulum eleifend neque quam facilisis.

Donec vestibulum donec ac nullam eu vulputate, et mattis quis eget lorem curabitur in, dictumst in lacus, quam veritatis, iaculis voluptate nulla magna qui. Duis eu, morbi neque amet eu, vitae lacinia ante euismod nunc sapien esse, wisi exercitation, aliquam purus nullam. Ut nunc mauris ligula tincidunt tellus, molestie arcu nunc a mi, augue neque dictum nisl, tellus augue. Erat vitae et tincidunt at. Nec id urna elit leo arcu, ligula commodo et morbi esse. Tortor nullam augue ut ac. Varius quisque, nam ac bibendum rutrum amet vel nisl, lorem a justo, pellentesque suspendisse. Sit velit sit egestas pellentesque velit. Sagittis platea pellentesque at non ad, eu etiam, accumsan proin etiam sit nulla. Neque eget mauris lacus quis aliquet leo, vel elementum quam dictum dolor.

Et risus consectetuer adipiscing maecenas vitae arcu, ipsum pulvinar, nascetur fusce aenean commodo condimentum maecenas ligula, nulla id aliquet volutpat, interdum vitae. Lobortis assumenda donec id nisl in, sapien vitae eget metus id eget, turpis tortor maecenas do eu gravida vel, elit molestie pharetra adipiscing duis augue, dolor ultrices. Ipsum lorem malesuada, mi tortor, nisl erat, vestibulum sed vivamus pretium, vitae erat id volutpat et. Sem in et urna aliquet justo gravida, quis tempus non urna sed. Euismod quam nam aliquet, justo eget sit lacus, et mi mauris consectetuer tempor sed elit, vestibulum ac in ipsum integer vivamus habitasse. Magna neque malesuada in in congue etiam, urna placerat dolor, leo venenatis magna mi tempor ipsum augue, quam gravida felis malesuada aliquam pharetra lacus. Mauris et, vel odio augue vivamus nibh, elementum morbi morbi phasellus, quam justo donec nonummy orci nec. Mauris tellus eleifend semper ornare enim. Vestibulum bibendum turpis etiam scelerisque aliquid venenatis, mauris fermentum eu feugiat, nullam pede pede tempor. Eros et donec. Placerat magna quis donec, ad pede erat sed.

Varius dolor risus enim phasellus quisque sit. In ligula, nunc sociosqu dignissim, maecenas at magnis tincidunt sed ante sed. Lobortis condimentum dui, vel diam. Optio proin leo, officia accusamus. Aliquam eros, erat dolor enim. Fames morbi, quis convallis. Non deserunt egestas dictumst duis, aliquam orci repellendus.
EOF
);
	$p->cell1->Content = igk_html_from_string(<<<EOF
 Lorem ipsum dolor sit amet, ut sed in mattis orci condimentum rutrum, elit vel diam pellentesque nostra viverra, eget augue ut id, at arcu, turpis dignissim. Dui in commodo eros ornare convallis arcu, eget porttitor, mattis etiam, nulla felis wisi nulla, ante nulla eum. Posuere in blandit est tincidunt ac, dui velit eget, ut morbi orci nonummy sed urna nunc, vitae ut scelerisque sollicitudin id dapibus, orci sed. Aliquam aliquet sodales laoreet velit, arcu auctor et odio. Condimentum aliquet suscipit inceptos ut suspendisse fusce. Arcu eu. Sit fermentum justo facilisis ut integer, tristique eget, elit nunc non mattis, sagittis massa a eu quis, nisl tortor. At lacus, lorem ac vitae sapien.

Vel ligula mollis, leo quis arcu vel, massa elit litora necessitatibus nunc pretium, nunc mi aenean ultrices rhoncus auctor, risus at nec. Etiam praesent mauris ultrices porta urna in, auctor libero, egestas ipsum tellus justo venenatis, erat nec eu pede blandit non. Tellus duis, cupidatat et dolor ligula dui wisi amet. Amet gravida quis, eros sed porttitor lacus urna dolor, sagittis ornare consequat, diam amet quam nulla praesent ipsum officia, volutpat felis id libero eros non. Velit nec pretium nonummy rutrum consequat. Scelerisque cras libero convallis suscipit, leo in, amet lobortis at nec arcu elit fringilla, libero mauris sit ut ac. Aptent consequat cras dui senectus incididunt, aut libero quis aenean proin elit, tempor neque.

Non est fringilla, sed scelerisque mauris interdum. Porta vestibulum dolor wisi, sit dapibus, tempor ac molestie enim augue, lacus eu vel amet interdum, in vitae nam. Euismod adipiscing, sem nibh eget semper turpis, nisl sodales tempor, esse faucibus sollicitudin blandit morbi. Nascetur vel aenean ligula quis pretium, luctus cras cursus nascetur rutrum, lectus mattis faucibus. Sit torquent in elementum. Tortor tempus vivamus nullam, sit sit. Adipiscing quam, sem in elit vivamus malesuada, velit velit magna praesent, mi malesuada curabitur maecenas. Imperdiet faucibus dui vulputate metus, vitae sit sit mauris, dolor praesent vestibulum viverra mollis illo.
EOF
);
	$p->cell2->content = igk_html_from_string(<<<EOF
Lorem ipsum dolor sit amet, imperdiet cursus nec et augue, neque duis mauris rhoncus. In nullam curabitur nibh wisi, nonummy purus donec amet nec suspendisse saepe, sodales nulla sed nisl ipsum amet vehicula. Nisl est vitae sapien nec tempus consectetuer, amet mauris vitae, gravida aliquam risus placerat a magna ultrices, lacus blandit pulvinar auctor convallis aenean mauris. Iaculis distinctio, libero proin proin nam vulputate, sollicitudin curabitur in quis, interdum adipiscing varius at et vehicula, blandit cras. Dui nunc tempus justo nonummy lacinia, risus elit pede nec a, in veniam metus, auctor turpis quam sed. Risus fusce felis eget turpis pellentesque pede, wisi nunc ac aliquam, magna vivamus integer, nunc libero ad.

Vivamus cras quam erat ligula, nunc sapien malesuada lacus, suspendisse sed, sed est quam amet suscipit dolor. Quam nec massa felis. Sollicitudin orci orci metus quam, aliquam suscipit semper leo aenean quam, sapien nam auctor a, tortor aenean, augue ullamcorper amet eros aenean ac. Elementum in mauris lectus odio quis, elementum lacinia velit est a arcu, fusce aute pellentesque leo, sed sem etiam proin potenti maecenas. Nunc mauris, urna erat fusce accumsan omnis sem tristique, quam convallis erat nullam semper a, erat faucibus felis montes pellentesque at, ac vitae wisi. Dictum viverra elit pede vivamus maecenas id. Egestas nec at lectus, urna praesent eu eget gravida massa.

EOF
);

	$t->addDiv()->Content = "Layout Presentation demos";
}


igk_reg_widget("layoutPresentation");

?>