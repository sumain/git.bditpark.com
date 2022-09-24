<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerReports extends ControllerBase {

	protected $approval;
	protected $fields;
	protected $userid;
	protected $appstatus = [''=>'Status','100'=>'Packing','150'=>'Assaging for Pickup','200'=>'Pickuping','220'=>'Assaging for Deliver','250'=>'Deliver','300'=>'Delivered','320'=>'Cancel'];
    public function __construct() {
        parent::__construct();
        $this->load->model('modelReports');
        $this->load->model('modelProducts');;
        $this->load->model('modelEmployee');
        $this->load->model('modelReportCategory');
        $this->load->model('modelLocations');
		$this->userid= getName('user_id');
    }

    public function index() {
        $this->lists();
    }

     public function lists($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter2 = '';		
		$filter = [];
		
		$qty     = $this->input->get('qty');
		$name   = $this->input->get('name');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;
        $data['qty']     = $qty;	
        $data['name']    = $name;		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
        $filter['name']= $name;
        $filter['sales_products.qty']= $qty;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->lists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/lists/";
		
		$page['search_url']= '?name='.$name.'&qty='.$qty.'&fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'reports','2'=>'sale','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SAL'));
		
		if($admin){
			$data['main_content'] = 'inventory/reports/sale';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		//$data['locations'] = $this->modelLocations->getAll();
		//$data['racks'] = $this->modelRacks->getAll();
		$this->load->view(THEMES, $data);
    } 
	
	public function saleSummary($pa=1) {
        extract($this->authentication->user_groups_auth);
        $filter2 = '';		
		$filter = [];
		
		$current = date("Y-m-d");
		$date    = date('Y-m-d', strtotime('-120 day', strtotime($current)));
		
		
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        
		$fromdate =($fromdate)?$fromdate:$date;
        $data['todate']  = ($todate)?$todate:$current;	
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;	
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->saleSummary($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/saleSummary/";
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'reports','2'=>'sal_sum','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SAL'));
		
		$data['main_content'] = 'inventory/reports/sale_summary';
		
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$log['userid']  = getName('user_id');
		$log['product'] = 0;
		$log['notes']   = 'Sale report visited by ."'.getName('full_name').'"';
		$log['created_on']= dates();
		$this->modelCommon->insertTableData($log,'logs');
		$this->load->view(THEMES, $data);
    }
	
	public function purchase($pa=1) {
        extract($this->authentication->user_groups_auth);
        $filter = [];
		
		$supplier= $this->input->get('supplier');
		$order   = $this->input->get('order');
		$qty     = $this->input->get('qty');
		$name     = $this->input->get('name');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;
        $data['qty']     = $qty;	
        $data['name']    = $name;		
        $data['order']    = $order;		
        $data['supplier'] = $supplier;		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
        $filter['name']= $name;
        $filter['receipt']= $order;
        $filter['purchase_products.qty']= $qty;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->purchase($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/purchase/";
		
		$page['search_url']= '?name='.$name.'&qty='.$qty.'&fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'reports','2'=>'purch','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','PUREP','MD'));
		if($admin){
			$data['main_content'] = 'inventory/reports/purchase';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		//$data['locations'] = $this->modelLocations->getAll();
		//$data['racks'] = $this->modelRacks->getAll();
		$this->load->view(THEMES, $data);
    } 
	
	public function purchaseSummary($pa=1) {
        extract($this->authentication->user_groups_auth);
        $filter2 = '';		
		$filter = [];
		
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;	
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->purchaseSummary($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/purchaseSummary/";
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'reports','2'=>'pur_sum','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','PUREP'));
		if($admin){
			$data['main_content'] = 'inventory/reports/purchase_summary';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$log['userid']  = getName('user_id');
		$log['product'] = 0;
		$log['notes']   = 'Purchase report visited by ."'.getName('full_name').'"';
		$log['created_on']= dates();
		$this->modelCommon->insertTableData($log,'logs');
		
		$this->load->view(THEMES, $data);
    }
	
	
	public function otherExpenseSummary($pa=1) {
        extract($this->authentication->user_groups_auth);
        $filter2 = '';		
		$filter = [];
		
		$fromdate= $this->input->get('fromdate');
        $todate  = $this->input->get('todate');
        $catid   = $this->input->get('catid');
        
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;	
        $data['catid']  = $catid;	
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['categoryid']= $catid;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->otherExpenseSummary($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/otherExpenseSummary/";
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM'));
        $data['nav'] = array('1'=>'reports','2'=>'otherExpense','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		
		$data['main_content'] = 'inventory/reports/other_expense_summary';
		$data['categories'] = $this->modelReportCategory->getAll();
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$log['userid']  = getName('user_id');
		$log['product'] = 0;
		$log['notes']   = 'Other Expense report visited by ."'.getName('full_name').'"';
		$log['created_on']= dates();
		$this->modelCommon->insertTableData($log,'logs');
		
		$this->load->view(THEMES, $data);
    }
	
	public function otherPurchaseSummary($pa=1) {
        
		extract($this->authentication->user_groups_auth);
        $filter2 = '';		
		$filter = [];
		$current = date("Y-m-d");
		$date    = date('Y-m-d', strtotime('-60 day', strtotime($current)));
		
		$fromdate= $this->input->get('fromdate');
        $todate  = $this->input->get('todate');
        $catid   = $this->input->get('catid');
        
		$fromdate = ($fromdate)?$fromdate:$date;
		$todate   = ($todate)?$todate:$current;
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;
        $data['catid']  = $catid;	
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['categoryid']= $catid;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->otherPurchaseSummary($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/otherPurchaseSummary/";
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM'));
        $data['nav'] = array('1'=>'reports','2'=>'otherPur','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		
		$data['main_content'] = 'inventory/reports/other_purchase_summary';
		$data['categories'] = $this->modelReportCategory->getAll();
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$log['userid']  = getName('user_id');
		$log['product'] = 0;
		$log['notes']   = 'Other Puchase report visited by ."'.getName('full_name').'"';
		$log['created_on']= dates();
		$this->modelCommon->insertTableData($log,'logs');
		
		$this->load->view(THEMES, $data);
    }
	
	public function otherPurchaseAll($pa=1) {
        extract($this->authentication->user_groups_auth);
        $filter2 = '';		
		$filter = [];
		
		$fromdate= $this->input->get('fromdate');
        $todate  = $this->input->get('todate');
        $catid   = $this->input->get('catid');
        
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;	
        $data['catid']  = $catid;	
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['catid']= $catid;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->otherPurchaseAll($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/otherPurchaseAll/";
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM'));
        $data['nav'] = array('1'=>'reports','2'=>'otherPur','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		
		$data['main_content'] = 'inventory/reports/other_purchase_all';
		$data['categories'] = $this->modelReportCategory->getAll();
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		
		
		$this->load->view(THEMES, $data);
    }
	
	public function storeVisit($pa=1) {
        extract($this->authentication->user_groups_auth);
        $filter2 = '';		
		$filter = [];
		
		$fromdate= $this->input->get('fromdate');
        $todate  = $this->input->get('todate');
        $userid   = $this->input->get('userid');
        $locaid   = $this->input->get('locaid');
        
		
        $data['fromdate']= $fromdate;
        $data['todate']  = $todate;	
        $data['userid']  = $userid;	
        //$data['iproducts.locaid']  = $locaid;	
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/reports/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['userid']= $userid;
		$filter['iproducts.locaid']  = $locaid;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelReports->storeVisit($filter,$page);
		
		//printr($result);
		//exit;
		$data['rows']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reports/storeVisit/";
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'reports','2'=>'storeVisit','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','STRREP'));
		$data['main_content'] = 'not_authorized';	
		if($admin){
			$data['main_content'] = 'inventory/reports/store_visit_details';
		}
		$data['locaid'] = $locaid;
		$data['categories'] = $this->modelLocations->getAll();
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$log['userid']  = getName('user_id');
		$log['product'] = 0;
		$log['notes']   = 'Warehouse report visited by ."'.getName('full_name').'"';
		$log['created_on']= dates();
		$this->modelCommon->insertTableData($log,'logs');
			
		$this->load->view(THEMES, $data);
    }
}

//
