<?php
WaxEvent::add("model.Derivative.setup", function(){
  $model = WaxEvent::data();
  if(!$model->columns['finance']) $model->define("finance", "ManyToManyField", array('target_model'=>'VehicleFinance', 'group'=>'relationships'));  
});

CMSApplication::register_module("finance", array("display_name"=>"Finance", "link"=>"/admin/finance/"));
?>