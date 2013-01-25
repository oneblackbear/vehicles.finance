<?php
class AdminFinanceController extends AdminComponent{
  public $module_name = "finance";
  public $model_class = 'VehicleFinance';
  public $display_name = "Finance";
  public $dashboard = false;
  public $tree_layout = false;
}
