<?php
class AdminFinanceController extends AdminComponent{
  public $module_name = "finance";
  public $model_class = 'VehicleFinance';
  public $display_name = "Finance";
  public $dashboard = false;
  public $tree_layout = false;
  public $per_page = 60;
  public $filter_fields=array(
  	'text' => array('columns'=>array('title', 'payment_type','finance_type'), 'partial'=>'_filters_text', 'fuzzy'=>true),
    'derivatives' => array('columns'=>array('derivatives'), 'partial'=>'_filters_select', 'opposite_join_column'=>'vehicle_finance'),
  );
}
