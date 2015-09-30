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

  public function events(){
    parent::events();
  
    WaxEvent::add("cms.layout.sublinks", function(){
      $obj = WaxEvent::data();
      $mods = CMSApplication::get_modules();
      $obj->quick_links = array_merge(
        $obj->quick_links,
        array("import new ".$mods[$obj->module_name]['display_name']=>'/admin/'.$obj->module_name."/import/")
      );
    });
  }
  
  public function import(){
    $this->import_error = array();
    $prefix = "finance_import";
    $file_name = 'import_file';
    $this->form = new WaxForm(array("form_prefix"=>$prefix));
  
    $this->form->add_element('payment_type', "SelectInput", array(
      "choices"=>array(
        "Hire Purchase"=>"Hire Purchase",
        "Lease Purchase"=>"Lease Purchase",
        "Contract Hire"=>"Contract Hire",
    )));
  
    $this->form->add_element('finance_type', "SelectInput", array(
      "choices"=>array(
        "Private"=>"Private",
        "Business"=>"Business",
    )));
  
    $this->form->add_element('clear', "SelectInput", array(
      "choices"=>array(
        "No",
        "Yes",
    )));
  
    $this->form->add_element($file_name, "FileInput");
  
    $mapping = array(
      "Hire Purchase"=>array(
        'Private'=>array(
          "Title"=>"title",
          "Group Title"=>"group_title",
          "Cash Price"=>"cash_price",
          "Deposit"=>"deposit",
          "Amount of Credit"=>"total_credit",
          "Number of Monthly Payments"=>"number_of_monthly_payments",
          "Monthly Payments of"=>"monthly_payments",
          "Duration of Agreement"=>"duration_of_agreement",
          "APR"=>"representative_apr",
        ),
        'Business'=>array(
          "Title"=>"title",
          "Group Title"=>"group_title",
          "Cash Price"=>"cash_price",
          "Deposit"=>"deposit",
          "Amount of Credit"=>"total_credit",
          "Number of Monthly Payments"=>"number_of_monthly_payments",
          "Monthly Payments of"=>"monthly_payments",
          "Duration of Agreement"=>"duration_of_agreement",
          "APR"=>"representative_apr",
          "Interest Rate"=>"interest_rate",
        ),
      ),
      "Lease Purchase"=>array(
        'Private'=>array(
          "Title"=>"title",
          "Group Title"=>"group_title",
          "Cash Price"=>"cash_price",
          "Deposit"=>"deposit",
          "Amount of Credit"=>"total_credit",
          "Number of Monthly Payments"=>"number_of_monthly_payments",
          "Monthly Payments of"=>"monthly_payments",
          "Duration of Agreement"=>"duration_of_agreement",
          "APR"=>"representative_apr",
          "Final Payment"=>"optional_final_payment",
        ),
        'Business'=>array(
          "Title"=>"title",
          "Group Title"=>"group_title",
          "Cash Price"=>"cash_price",
          "Deposit"=>"deposit",
          "Amount of Credit"=>"total_credit",
          "Number of Monthly Payments"=>"number_of_monthly_payments",
          "Monthly Payments of"=>"monthly_payments",
          "Duration of Agreement"=>"duration_of_agreement",
          "APR"=>"representative_apr",
          "Final Payment"=>"optional_final_payment",
        ),
      ),
      "Contract Hire"=>array(
        'Private'=>array(
          "Title"=>"title",
          "Group Title"=>"group_title",
          "List Price"=>"list_price",
          "Contract Hire Rental"=>"contract_hire_rental",
          "Maintenance Cost"=>"maintenance_cost",
          "Term"=>"term",
          "Payment Plan"=>"payment_plan",
        ),
        'Business'=>array(
          "Title"=>"title",
          "Group Title"=>"group_title",
          "List Price"=>"list_price",
          "Contract Hire Rental"=>"contract_hire_rental",
          "Maintenance Cost"=>"maintenance_cost",
          "Term"=>"term",
          "Payment Plan"=>"payment_plan",
        ),
      ),
    );
  
    if(($save = $this->form->save()) && 
        $save['payment_type'] && $save['finance_type'] &&
        ($file = $_FILES[$prefix]) && 
        ($csv = $this->read_csv($file['tmp_name'][$file_name])))
    {
  
      if($save['clear']){
        $model = new VehicleFinance;
        $model->filter(array("payment_type"=>$save['payment_type'],"finance_type"=>$save['finance_type']))->all()->delete();
      }
  
      //loop over csv lines
      foreach($csv as $key=>$finance){
  
        //find derivative
        $vehicle = new Derivative("live");
        if(!($vehicle = $vehicle->filter("title",$finance["Vehicle"])->first())){
          $this->import_error[$key]["user_errors"] .= "Couldn't find derivative: ".$finance["Vehicle"]." ";
        }
  
        //make new finance
        $model = new VehicleFinance;
  
        //map fields
        if($mappings = $mapping[$save['payment_type']][$save['finance_type']]){
          foreach($mappings as $name=>$field){
            if($value = $finance[$name]){
              $model->$field = $value;
            }else{
              if($this->import_error[$key]["user_errors"]) $this->import_error[$key]["user_errors"] .= "<br>";
              $this->import_error[$key]["user_errors"] .= "Missing field or field empty: ".$name." ";
            }
          }
        }
  
        //other fields
        $model->payment_type = $save['payment_type'];
        $model->finance_type = $save['finance_type'];
  
        $model->sort = $key;
        $model->status = 1;
        $model->contract_mileage = "10000 PA";
  
        if($model = $model->save()){
          //attach derivative
          if($vehicle && $vehicle->primval) {
            $model->derivatives = $vehicle;
          }
        }else{
          if($this->import_error[$key]["user_errors"]) $this->import_error[$key]["user_errors"] .= "<br>";
          $this->import_error[$key]["user_errors"] .= "Failed saving... ";
        }
  
        if(!$this->import_error[$key]["user_errors"]){
          $this->import_error[$key]["user_messages"] = "Successfully imported entry.";
        }
      }
    }
  }
  
  protected function read_csv($file, $delimiter=","){
      if(($handle = fopen($file, "r")) == false) return false;
      $csv = array();
      $columns = array();
      $row=0;
      while (($data = fgetcsv($handle,0,$delimiter)) !== FALSE){
        if($row == 0) foreach($data as $i=>$col) $columns[]=$col;
        else foreach($columns as $i=>$col) $csv[$row-1][$col] = $data[$i];
        $row++;
      }
      fclose($handle);
      return $csv;
  }

}