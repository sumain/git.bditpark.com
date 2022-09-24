<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerInventory extends ControllerBase {

	protected $approval;
	protected $fields;
	protected $userid;
	protected $appstatus = [''=>'Status','100'=>'Packing','150'=>'Assaging for Pickup','200'=>'Pickuping','220'=>'Assaging for Deliver','250'=>'Deliver','300'=>'Delivered','320'=>'Cancel'];
    public function __construct() {
        parent::__construct();
        $this->load->model('modelInventory');
        $this->load->model('modelRacks');
        $this->load->model('modelLocations');
        $this->load->model('modelLocations');
        $this->load->model('modelEmployee');
		$this->approval= ['50'=>'RM','100'=>'SV','150'=>'RM','200'=>'DRV','220'=>'RM','250'=>'DRV','300'=>'Delivered','320'=>'Cancel'];
		$this->fields= ['50'=>'raiseby','100'=>'supplier','150'=>'rm','200'=>'driver','220'=>'rm2','250'=>'driver2','300'=>'Delivered','320'=>'Cancel'];
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
		$rack     = $this->input->get('rack');
		$location = $this->input->get('location');
		$name     = $this->input->get('name');
		$sku      = $this->input->get('sku');
		
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        
		
        $data['fromdate']  = $fromdate;
        $data['todate']    = $todate;
        $data['rackid']    = $rack;
        $data['locaid']= $location;		
        $data['name']= $name;		
        $data['sku']= $sku;		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
        $filter['iproducts.rackid']    = $rack;
        $filter['iproducts.locaid']= $location;	
        $filter['iproducts.sku']= $sku;	
        $filter['name']= $name;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelInventory->inventory($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."inventory/lists/";
		
		$page['search_url']= '?location='.$location.'&rack='.$rack;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'reports','2'=>'invent','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','INVR'));
		if($admin){
			$data['main_content'] = 'inventory/reports/inventory_products';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$data['locations'] = $this->modelLocations->getAll();
		
		$log['userid']  = getName('user_id');
		$log['product'] = 0;
		$log['notes']   = 'Product Inventory report visited by ."'.getName('full_name').'"';
		$log['created_on']= dates();
		$this->modelCommon->insertTableData($log,'logs');
		
		$data['racks'] = $this->modelRacks->getAll();
		$this->load->view(THEMES, $data);
    }
	public function bulkSupplier($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter = [];
		$status= $this->input->get('status');
		$order = $this->input->get('order');
		$supplier = $this->input->get('supplier');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['orders.order_id']= $order;
		$filter['place_orders.status']= $status;
		$filter['place_orders.supplier_id']= $supplier;
		$filter['fromdate']= '';
		$filter['todate']= '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->bulkSupplier($filter,$page,'');
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."process/bulkSupplier/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 
		$data['status']= $status; 
		$data['emp']   = ''; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'Bulk','3'=>'Supplier');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD'));
		if( $admin){
			$data['main_content'] = 'process/bulk_supplier';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$data['employees'] = $this->modelEmployee->getEmployees(3);
		$data['suppliers'] = $this->modelEmployee->getEmployees(2);
		$data['supplier'] = $supplier;
		$this->load->view(THEMES, $data);
    }

	public function bulkSupplierAssigned() {
		//printr($this->input->post());
		
		$action = $this->input->post('action');
		
        if ($action) {
            $orderid = $this->input->post('orderid');
            $remark  = $this->input->post('remark');
			$supplier= $this->input->post('supplier');
			$product = $this->input->post('rowid');
			
			$date    = date("Y-m-d H:i:s");
			///printr($this->input->post());
			$new_id ='0';
			
			foreach($product as $k => $val){
				$rows = $this->modelCommon->getTableData('products',['id'=>$val]);
				
				$row  = $rows[0];
				$order_id = $row->orderid;
				$data =array();
				$data['product_id'] = $val;
				$data['raiseby_id']    = $this->userid;
				$data['name']       = utf8_decode($row->product);
				$data['qty']        = $row->amount;
				$data['orderid']    = $orderid[$k];
				$data['status']     = 100;
				$data['raiseby_remark']= $remark;
				$data['supplier_id']= $supplier;
				$data['driver_id']  = 0;
				$data['created_on']= $date;
				$data['updated_on']= $date;
				$data['raiseby_date']= $date;
				
				$new_id = $this->modelCommon->insertTableData($data,'place_orders');
				$this->modelCommon->updateTableData('orders',['state'=>100],['id'=>$order_id]);
				$log['userid']  = $this->userid;
				$log['orderid'] = $row->orderid;
				$log['product'] = $val;
				$log['notes']   = $remark;
				$log['created_on']= $date;
				$this->modelCommon->insertTableData($log,'logs');
			}
		}
		if ($new_id) {
			if(count($product) ==1)              
				redirect('process/processDetail/'.$new_id);
			else
				redirect('process/lists');
		}
        
		
	}
	
	public function bulkAssign($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter = [];
		$status= $this->input->get('status');
		$order = $this->input->get('order');
		$supplier = $this->input->get('supplier');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['orders.order_id']= $order;
		$filter['place_orders.status']= $status;
		$filter['place_orders.supplier_id']= $supplier;
		$filter['fromdate']= '';
		$filter['todate']= '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->bulkAssign($filter,$page,'');
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."process/bulkAssign/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 
		$data['status']= $status; 
		$data['emp']   = ''; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'Bulk','3'=>'Driver');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','RM'));
		if( $admin){
			$data['main_content'] = 'process/bulk';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$data['employees'] = $this->modelEmployee->getEmployees(3);
		$data['suppliers'] = $this->modelEmployee->getEmployees(2);
		$data['supplier'] = $supplier;
		$this->load->view(THEMES, $data);
    }
	public function bulkAssigned() {
		$action = $this->input->post('action');
		
        if ($action) {
			$date = date("Y-m-d H:i:s");
			$keys = array_keys($this->fields);
			//printr($keys);
			$empid = $this->input->post('emp');
			$remark = $this->input->post('remark');
			$status = $this->input->post('status');
			$rowid  = $this->input->post('rowid');
			$nextst = $keys[array_search($status,$keys)+1];
			$field = $this->fields[$keys[array_search($status,$keys)]];
			$next_field = $this->fields[$keys[array_search($nextst,$keys)]];
			$data=[];
			$data[$field.'_id']= $this->userid;
			$data['status']     = $nextst;
			$data[$next_field.'_id']  = $empid;
			$data[$field.'_remark']= $remark;
			$data[$field.'_date']  = $date;
			
			foreach($rowid as $k => $id){
				
				$this->modelCommon->updateTableData('place_orders',$data,['id'=>$id]);
				$rows = $this->modelCommon->getTableData('place_orders',['id'=>$id]);
				$log['userid']  = $this->userid;
				$log['orderid'] = $rows[0]->orderid;
				$log['product'] = $rows[0]->product_id;
				$text = ($nextst==200)?"Bulk, driver assign for pickup. ":"Bulk, driver assign for deliver. ";
				$log['notes']   = $text.$remark;
				$log['created_on']= $date;
				$this->modelCommon->insertTableData($log,'logs');
			}
			redirect('process/lists/');
			
		}
	}
	public function productGroup($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter = [];
		$status= $this->input->get('status');
		$order = $this->input->get('order');
		$supplier = $this->input->get('supplier');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['orders.order_id']= $order;
		$filter['place_orders.status']= $status;
		$filter['place_orders.supplier_id']= $supplier;
		$filter['fromdate']= '';
		$filter['todate']= '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->productGroup($filter,$page,'');
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."process/productGroup/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 
		$data['status']= $status; 
		$data['emp']   = ''; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'Group','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SV'));
		if( $admin){
			$data['main_content'] = 'process/produt_group';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$data['employees'] = $this->modelEmployee->getEmployees(3);
		$data['suppliers'] = $this->modelEmployee->getEmployees(2);
		$data['supplier'] = $supplier;
		$this->load->view(THEMES, $data);
    }
    public function forward() {
        
        $action = $this->input->post('action');
		//printr($this->input->post());
		//exit;
        if ($action) {
            $orderid = $this->input->post('orderid');
            $remark  = $this->input->post('remark');
            $status  = $this->input->post('status');
            $weight  = $this->input->post('weight');
            $price   = $this->input->post('price');
            $driver   = $this->input->post('driver');
			$keys   = array_keys($this->fields);
			
			$row = $this->modelCommon->getTableData('place_orders',['id'=>$orderid]);	
			$row = $row[0];	
			
			$field   = $this->fields[$status];
			$date    = date("Y-m-d H:i:s");
			if($action ==1){				
				$data[$field.'_remark']= $remark;
				$data[$field.'_id']    = $this->userid;				
			}elseif($action == 2){
				$data = $this->input->post('data');
				$field = $this->fields[$keys[array_search($status,$keys)-1]];
				$data[$field.'_id']= $this->userid;
				$data['status']     = $status;
				$data[$field.'_remark']= $remark;
				$data[$field.'_date']  = $date;
				if($status ==150){
					//$data['rm_id']  = $row->raiseby;
				}
				if($status ==220){
					//$data['rm2_id']  = $row->raiseby;
				}
				if($status ==300){
					$name = 'del'.time();
					$path = $this->root.'uploads/delivery/';
					$img_name = $this->modelCommon->uploadPicture($name,$path);
					//$data['rm2_id']  = $row->raiseby;
					if($img_name){
						$img['image'] = $img_name;
						$this->modelCommon->updateTableData('place_orders',$img,['id'=>$orderid]);
					}
					$deli = array();
					$deli['state'] = 300;
					$deli['driver'] = $this->userid;
					$deli['driver2_date'] = $date;
				    
					$this->modelCommon->updateTableData('orders',$deli,['id'=>$row->orderid]);
					
					$info = $data;
					$info['emp'] = 'driver2_id';
					$logdata = $this->getLogData($info,$orderid);
					
					$log['userid']  = $this->userid;
					$log['orderid'] = $row->orderid;
					$log['product'] = $row->product_id;
					$log['notes']   = 'Delivered order '.$logdata;
					$log['created_on']= $date;
					$this->modelCommon->insertTableData($log,'logs');
				}else{
					$info = $data;
					$info['emp'] = $field.'_id';
					$logdata = $this->getLogData($info,$orderid);
					
					$log['userid']  = $this->userid;
					$log['orderid'] = $row->orderid;
					$log['product'] = $row->product_id;
					$log['notes']   = $this->appstatus[$status].' '.$logdata.' '. $remark;
					$log['created_on']= $date;
					$this->modelCommon->insertTableData($log,'logs');
				}
				$this->modelCommon->updateTableData('place_orders',$data,['id'=>$orderid]);
			}else{				
				$data['status']     = 320;
				$data['rm_id']     = 0;
				$field = $this->fields[$keys[array_search($status,$keys)]];
				if($status == 100){
					$data['driver_id'] = 0;
				}
				$data[$field.'_remark']= $remark;
				$data[$field.'_id']    = $this->userid;
				$data[$field.'_date']  = $date;
				$this->modelCommon->updateTableData('place_orders',$data,['id'=>$orderid]);
				
				$info = $data;
				$info['emp'] = $field.'_id';
				$logdata = $this->getLogData($info,$orderid);
				
				$log['userid']  = $this->userid;
				$log['orderid'] = $orderid;
				$log['product'] = $row->product_id;
				$log['notes']   = 'Cancel assign '.$logdata. ' '. $remark;
				$log['created_on']= $date;
				$this->modelCommon->insertTableData($log,'logs');
			}
			 //$this->modelCommon->updateTableData('place_orders',$data,['id'=>$orderid]);	
			
            redirect('process/processDetail/'.$orderid);
            
        }
    }

	public function processDetail($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {            
            $row = $this->modelOrders->getProcessDetail($id);			
            $data['details'] = $row;			
        }
		
		$status = $row[0]->status;
		
		$keys = array_keys($this->approval);	 
		
		$previous =$keys[array_search($status,$keys)-1];
		if($status <300)
			$next     =$keys[array_search($status,$keys)+1];
		
		
		
		$data['auth']= $isAdmin;
		$data['userid']= $this->userid;
		$data['nav'] = array('1'=>'process','2'=>'orders','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin){
			$data['main_content'] = 'process/process_view';
		}
		
        $this->load->view(THEMES, $data);
    }
	
    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {            
            $row = $this->modelOrders->getProcessDetail($id);			
            $data['details'] = $row;			
        }
		
		$status = $row[0]->status;
		$driver = $row[0]->driver_id;
		
		$keys = array_keys($this->approval);
        if($status == 320){
			if($row[0]->driver2_id)
				$status = 220;
			else
				$status = 50;
			
		}
		$data['save'] = $status;
		$data['forward'] =$keys[array_search($status,$keys)+1];	
		//$data['cancel'] = $keys[array_search($status,$keys)-1];
		$data['cancel'] = 320;
		
		if($driver && $status < 200)
			$data['forward'] =$keys[array_search($status,$keys)+2];
		
		
		$data['auth']= $this->authentication->check_auth(array('ADMIN','MD','RM'));
		$data['drivers']= '';
		$data['nav'] = array('1'=>'process','2'=>'orders','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin ){
			$data['main_content'] = 'process/process_edit';
		}
		
		$condition =['employee.status'=>1,'ctype'=>3];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$data['drivers']= $this->modelEmployee->employeeLists($condition,$limit);
		
		$condition =['employee.status'=>1,'ctype'=>2];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['employee']= $rows['rows'];
		
		$authentication = $this->authentication->check_auth($this->approval[$status]);
		
		if($authentication == false ){
			if($data['auth'] == false){
				redirect('process/processDetail/'.$row[0]->id);
			}
		}
		
								
        $this->load->view(THEMES, $data);
    }

	public function report($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter = [];		
		$status = $this->input->get('status');
		$order = $this->input->get('order');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        $data['fromdate']  = $fromdate;
        $data['status']    = $status;
        $data['todate']    = $todate;
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['place_orders.status']= 300;
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['orders.order_id']= $order;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 20;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->reportList($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."process/report/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 		
		$data['isAdmin']= $isAdmin; 	
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'report','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','RM'));
		if( $admin){
			$data['main_content'] = 'process/reports';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$this->load->view(THEMES, $data);
    }
	
	function supplierReport($pa=1){
		
		extract($this->authentication->user_groups_auth);
		$today = date("Y-m-d");
		$from = date('Y-m-d', strtotime('-6 day', strtotime($today)));
		$filter = [];
		$order    = $this->input->get('order');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        $empid    = $this->input->get('emp');
        $type     = $this->input->get('type');
		
		$fromdate = ($fromdate)?$fromdate:$from; 
		$todate   = ($todate)?$todate:$today;
		
        $data['fromdate']  = $fromdate;
        $data['order']     = $order;
        $data['todate']    = $todate;
        $data['type']    = $type;
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['orders.order_id']= $order;
		
		if($type == 2){
			$filter['place_orders.supplier_id']= $empid;
			$filter['field']= 'supplier_date';
		}else{
			$filter['field']= 'driver2_date';
			$filter['orders.driver']= $empid;
		}
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		
		$result = $this->modelOrders->supplierReport($filter,$page,$type);
		
		$data['driver']    = $result['driver'];
		$data['supplier']  = $result['supplier'];
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate;
		$page['total_row']= 20;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 		
		$data['emp']  = $empid; 		
		$data['isAdmin']= $isAdmin; 	
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'report2','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','RM'));
		if( $admin){
			$data['main_content'] = 'process/reports2';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$data['employee'] = $this->modelEmployee->getEmployees();
		//printr($data['employee']);
		$this->load->view(THEMES, $data);
	}
	
	function driverDeliveryReport($pa=1){
		
		extract($this->authentication->user_groups_auth);
		$today = date("Y-m-d");
		$from = date('Y-m-d', strtotime('-6 day', strtotime($today)));
		$filter = [];
		$order    = $this->input->get('order');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        $empid    = $this->input->get('emp');
		if($isDRV){
			$empid = getName('user_id');
		}
		$fromdate = ($fromdate)?$fromdate:$from; 
		$todate   = ($todate)?$todate:$today;
		
        $data['fromdate']  = $fromdate;
        $data['order']     = $order;
        $data['todate']    = $todate;
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['orders.order_id']= $order;
		$filter['orders.driver']= $empid;
		$filter['state']= 300;
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		
		$result = $this->modelOrders->driverDeliveryReport($filter,$page);
		$data['result'] = $result['rows'];
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate.'&emp='.$empid;
		$page['total_row']= $result['ttl_rows'];
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 		
		$data['emp']  = $empid; 		
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD','RM'));
        $data['nav'] = array('1'=>'process','2'=>'driver1','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','RM','DRV'));
		if( $admin){
			$data['main_content'] = 'process/reports_driver';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$data['employee'] = $this->modelEmployee->getEmployees(3);
		//printr($data['employee']);
		$this->load->view(THEMES, $data);
	}
	
	
	function supplierDeliveryReport($pa=1){
		
		extract($this->authentication->user_groups_auth);
		$today = date("Y-m-d");
		$from = date('Y-m-d', strtotime('-6 day', strtotime($today)));
		$filter = [];
		$order    = $this->input->get('order');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        $empid    = $this->input->get('emp');
		if($isSV){
			$empid= getName('user_id');
		}
		$fromdate = ($fromdate)?$fromdate:$from; 
		$todate   = ($todate)?$todate:$today;
		
        $data['fromdate']  = $fromdate;
        $data['order']     = $order;
        $data['todate']    = $todate;
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['place_orders.status']= 300;
		$filter['orders.order_id']= $order;
		$filter['place_orders.supplier_id']= $empid;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		
		$result = $this->modelOrders->supplierDeliveryReport($filter,$page);
		$data['result'] = $result['rows'];
		
		$page['search_url']= '?fromdate='.$fromdate.'&todate='.$todate.'&emp='.$empid;
		$page['total_row']= $result['ttl_rows'];
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 		
		$data['emp']  = $empid; 		
		$data['isAdmin']= $isAdmin; 	
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'supplier1','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SV','RM','DRV'));
		if( $admin){
			$data['main_content'] = 'process/reports_supplier';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$data['employee'] = $this->modelEmployee->getEmployees(2);
		//printr($data['employee']);
		$this->load->view(THEMES, $data);
	}
	function getLogData($data,$id){
		
		$empid = $data['emp'];

		$this->db->select("concat(emp.name,' ',emp.last_name) as name,order_id",false);
		$this->db->where("place_orders.id",$id);				
		$this->db->join("orders",'orders.id=place_orders.orderid','left');
		$this->db->join("employee AS emp","emp.id=place_orders.$empid",'left');
		
		$emp = $this->db->get('place_orders')->result();
		//exit;
		return $emp = $emp[0]->order_id.' '. $emp[0]->name;
	}
	
	
	public function deliveryProducts($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter = [];
		$status= 250;
		$order = $this->input->get('order');
		$supplier = $this->input->get('supplier');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['orders.order_id']= $order;
		$filter['place_orders.status']= $status;
		$filter['place_orders.supplier_id']= $supplier;
		$filter['fromdate']= '';
		$filter['todate']= '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->bulkAssign($filter,$page,'');
		//printr($result);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."process/deliveryProducts/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['order']= $order; 
		$data['status']= $status; 
		$data['emp']   = ''; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN'));
        $data['nav'] = array('1'=>'process','2'=>'driver','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','DRV'));
		if( $admin){
			$data['main_content'] = 'process/bulk_deliver';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$data['employees'] = $this->modelEmployee->getEmployees(3);
		$data['suppliers'] = $this->modelEmployee->getEmployees(2);
		$data['supplier'] = $supplier;
		$this->load->view(THEMES, $data);
    }
	
	
	
	public function bulkDeliverdProduct() {
		$action = $this->input->post('action');
		
        if ($action) {
			$date = date("Y-m-d H:i:s");
			$keys = array_keys($this->fields);
			
			$empid = $this->input->post('emp');
			$remark = $this->input->post('remark');
			$status = $this->input->post('status');
			$rowid  = $this->input->post('rowid');
			$nextst = $keys[array_search($status,$keys)+1];
			$field = $this->fields[$keys[array_search($status,$keys)]];
			$next_field = $this->fields[$keys[array_search($nextst,$keys)]];
			$data=[];
			$data[$field.'_id']= $this->userid;
			$data['status']     = $nextst;
			//$data[$next_field.'_id']  = $empid;
			$data[$field.'_remark']= $remark;
			$data[$field.'_date']  = $date;
			;
			foreach($rowid as $k => $id){
				
				$this->modelCommon->updateTableData('place_orders',$data,['id'=>$id]);
				$rows = $this->modelCommon->getTableData('place_orders',['id'=>$id]);
				$log['userid']  = $this->userid;
				$log['orderid'] = $rows[0]->orderid;
				$log['product'] = $rows[0]->product_id;
				$text = ($nextst==200)?"Bulk, driver assign for pickup. ":"Bulk, delivered ";
				$log['notes']   = $text.$remark;
				$log['created_on']= $date;
				$this->modelCommon->insertTableData($log,'logs');
			}
			redirect('process/lists/');
			
		}
	}
	public function physicalVisit(){
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/physical_visit/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
		
		$data['auth']= $this->authentication->check_auth(array('ADMIN','MD','RM'));
		
		$data['nav'] = array('1'=>'inventory','2'=>'report','3'=>'physical');	
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM'));
		if( $admin ){
			$data['main_content'] = 'tasks/task_add';
		}
		
		$condition =['employee.status'=>1,'ctype'=>3];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$data['drivers']= $this->modelEmployee->employeeLists($condition,$limit);
		$data['orders']= $this->modelTasks->orderCS();
		
		$condition =['employee.status'=>1,'ctype'=>2];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['employee']= $rows['rows'];
		$data['userid']= $this->userid;
		
		$this->load->view(THEMES, $data);
	}
}
