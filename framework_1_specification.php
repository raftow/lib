<?php
 // to check before bfunction generation :
 // select titre_u, titre_u_s, titre_short from c0pag.atable where id_module = $MODULE and (titre_u is null or titre_u_s is null or titre_short is null);   
 // update c0pag.atable set titre_u_s = titre_u where id_module = $MODULE and titre_u_s is null;
 // 
      


 $framework_screens_bfcode_starts_with = "f1-a";
 
 $no_screen["detail_owned"] = true;
 $no_screen["vobj"] = true;
 
 // jobroles codes
 // djs : DisplayJobroles
 // em  : Entity Manager Jobrole
 // bm  : Business Manager Jobrole
 // sm  : System Manager Jobrole
 // mm  : Monitoring Manager Jobrole
 
 $framework_mode_list = array(
       "edit"=>array(
           'categories' => array("blookup"=>"em", "entity"=>"em", "relation"=>"em", "detail"=>"em", "detail_generated"=>"em", 'detail_owned' =>"em"),
           'menu' => array("entity"=>true, "detail"=>true, ),
           'bf_type' => array("all"=>"SCREEN"),
           'label' => array("ar"=>"تحرير","en"=>"edit", "fr"=>"edition"),
           'titre' => "إنشاء [titre_u_s]",
           'titre_en' => "create [titre_u_en]",
           'bf_spec' => "",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/edit",
       ),
       
       "qedit"=>array(
           'categories' => array("enum"=>"em", "lookup"=>"em", "blookup"=>"em", "entity"=>"em", "relation"=>"em", "detail"=>"em", "detail_generated"=>"em", 'detail_owned' =>"em",),
           'menu' => array("lookup"=>true),
           'bf_type' => array("all"=>"SCREEN", "detail"=>"SCREEN_METHOD", "detail_generated"=>"SCREEN_METHOD", ),
           'label' => array("ar"=>"إدارة","en"=>"administration", "fr"=>"administration"),
           'titre' => "إدارة [titre_short]",
           'titre_en' => "[titre_short_en] administration",
           'bf_spec' => "ids=all&newo=3",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/qedit",
       ),
       
       "delete"=>array(
           'categories' => array("enum"=>"sm", "lookup"=>"sm", "blookup"=>"sm", "entity"=>"em", "relation"=>"em", "detail"=>"em", "detail_generated"=>"em", 'detail_owned' =>"em",),
           'bf_type' => array("all"=>"SCREEN_METHOD", ),
           'label' => array("ar"=>"مسح","en"=>"delete", "fr"=>"suppression"),
           'titre' => "مسح [titre_u_s]",
           'titre_en' => "delete [titre_u_en]",
           'bf_spec' => "",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/delete",
       ),
       
       "display"=>array(
           'categories' => array("enum"=>"sm", "lookup"=>"sm", "blookup"=>"sm", "entity"=>"djs", 'view'=>"djs", "relation"=>"djs", "detail"=>"djs", "detail_generated"=>"djs", 'detail_owned' =>"djs",),
           //'goals' => array("enum"=>"id:10", "lookup"=>"id:10", "blookup"=>"id:10", "entity"=>"id:10", "relation"=>"id:10", "detail"=>"id:10", "detail_generated"=>"id:10",),
           'bf_type' => array("all"=>"SCREEN", ),
           'label' => array("ar"=>"عرض","en"=>"display", "fr"=>"affichage"),
           'titre' => "عرض تفاصيل [titre_u_s]",
           'titre_en' => "display details of [titre_u_en]",
           'bf_spec' => "",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/display",
       ),
       
       "search"=>array(
           'categories' => array("blookup"=>"sm", "entity"=>"djs", 'view' =>"djs", "relation"=>"djs", "detail"=>"djs", "detail_generated"=>"djs", 'detail_owned' =>"djs",),
           //'goals' => array("blookup"=>"id:10", "entity"=>"id:10", 'view' =>"id:10", "relation"=>"id:10", "detail"=>"id:10", "detail_generated"=>"id:10",),
           'bf_type' => array("all"=>"SCREEN", ),
           'menu' => array(),
           'label' => array("ar"=>"بحث","en"=>"search", "fr"=>"recherche"),
           'titre' => "البحث في [titre_short]",
           'titre_en' => "[titre_short_en] search",
           'bf_spec' => "",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/search",
       ),
       
       "qsearch"=>array(
           'categories' => array("blookup"=>"sm", "entity"=>"djs", "relation"=>"djs", 'view' =>"djs", "detail"=>"djs", "detail_generated"=>"djs", 'detail_owned' =>"djs",),
           //'goals' => array("blookup"=>"id:10", "entity"=>"id:10", "relation"=>"id:10", 'view' =>"id:10", "detail"=>"id:10", "detail_generated"=>"id:10",),
           'bf_type' => array("all"=>"SCREEN", ),
           'menu' => array("blookup"=>true, "entity"=>true, "relation"=>true, "detail"=>true, 'view' =>true, "detail_generated"=>true, 'detail_owned' =>true),
           'label' => array("ar"=>"الاستعلام","en"=>"quick search", "fr"=>"recherche rapide"),
           'titre' => "الاستعلام عن [titre_u_s]",
           'titre_en' => "[titre_u_en] quick search",
           'bf_spec' => "",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/qsearch",
       ),
       
       "stats"=>array(
           'categories' => array("entity"=>"bm", "relation"=>"bm", "detail"=>"bm", "detail_generated"=>"bm", 'view' =>"bm", 'detail_owned' =>"bm",),
           'goals' => array("entity"=>"endswith:stats", "relation"=>"endswith:stats", "detail"=>"endswith:stats", "detail_generated"=>"endswith:stats", 'view' =>"endswith:stats", 'detail_owned' =>"endswith:stats",),
           'label' => array("ar"=>"إحصائيات","en"=>"stats", "fr"=>"stats"),
           'menu' => array("blookup"=>false, "entity"=>true, "relation"=>true, "detail"=>true, 'view' =>false, "detail_generated"=>false, 'detail_owned' =>true),
           'bf_type' => array("all"=>"SCREEN", ),
           'titre' => "إجراء إحصائيات حول [titre_short]",
           'titre_en' => "[titre_short_en] statistics",
           'bf_spec' => "",
           'bf_code' => "$framework_screens_bfcode_starts_with-tb[id]/stats",
       ),
       /* // use rfw for data gathering in analysis phase:
       "rfdata"=>array(
            categories=>array("enum"=>true, "lookup"=>false, "blookup"=>false, "entity"=>false, "relation"=>false,),
       ),*/
       
 );
 
 
?>