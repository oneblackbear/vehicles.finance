<?
class VehicleFinance extends WaxModel{


  public function setup(){
    $this->define("title", 'CharField', array("scaffold"=>true));
    $this->define("group_title", 'CharField', array("scaffold"=>true));
    $this->define("sort", 'CharField', array("editable"=>FALSE));

    /******* Consumer Finance Options *******************/
    $this->define("cash_price", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("deposit", 'CharField', array("label"=>"Deposit / Part Exchange","group"=>"Hire Purchase"));
    $this->define("total_credit", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("purchase_fee", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("credit_facility_fee", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("total_amount_payable", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("initial_payment", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("number_of_monthly_payments", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("monthly_payments", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("optional_final_payment", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("duration_of_agreement", 'CharField', array("group"=>"Hire Purchase"));
    $this->define("representative_apr", 'CharField', array("label"=>"Representative APR","group"=>"Hire Purchase"));
    $this->define("interest_rate", 'CharField', array("label"=>"Interest rate (fixed)","group"=>"Hire Purchase"));
    
    /******** Contract Hire Options ********************/
    $this->define("list_price", 'CharField', array("group"=>"Contract Hire"));
    $this->define("contract_hire_rental", 'CharField', array("group"=>"Contract Hire"));
    $this->define("contract_hire_rental_inc_maintenance", 'CharField', array("group"=>"Contract Hire"));
    $this->define("term", 'CharField', array("group"=>"Contract Hire"));
    $this->define("payment_plan", 'CharField', array("group"=>"Contract Hire"));
    $this->define("contract_mileage", 'CharField', array("group"=>"Contract Hire"));
    
    
    $this->define("terms", 'TextField', array("label"=>"Terms & Conditions"));
    $this->define("status", 'BooleanField', array("default"=>1));
    $this->define("derivatives", "ManyToManyField", array('target_model'=>'Derivative', 'group'=>'relationships', 'scaffold'=>true));
    
  }
  
  public function scope_live() {
    $this->filter("status",1);
  }

  public function before_save(){
    if(!$this->cash_price) $this->cash_price = 0;
  }


}
