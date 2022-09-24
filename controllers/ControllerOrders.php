<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerOrders extends ControllerBase {

	protected $approval;
	protected $fields;
	public  $userid;
    public function __construct() {
        parent::__construct();
        $this->load->model('modelOrders');
        $this->load->model('modelEmployee');
        $this->userid = getName('user_id');
		//$this->approval= ['0'=>'Not Process','100'=>'Packing','150'=>'Assaging for Pickup','200'=>'Pickuping','220'=>'Assaging for Deliver','250'=>'Deliver','300'=>'Delivered','320'=>'Cancel'];
		$this->approval = ['0'=>'Not Process','50'=>'Status','350'=>'Supplier/Driver','400'=>'Admin/Sale','450'=>'Driver','500'=>'Completed','505'=>'Create New Task','510'=>'Re-Assign','515'=>'Cancel'];
		include APPPATH . 'third_party/SimpleXLSXGen.php';	
    }

    public function index() {
        $this->lists();
    }

     public function lists($pa=1) {
        extract($this->authentication->user_groups_auth);
       
		$filter = [];		
		$company = $this->input->get('company');
		$name    = $this->input->get('name');
		$client  = $this->input->get('client');
		$product = $this->input->get('product');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$mobile      = $this->input->get('mobile');
		
		$filter['mobile']  = $mobile;
		$filter['clientid']= $client;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->ordersLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."orders/lists/";
		
		$page['search_url']= '?mobile='.$mobile.'&client='.$client;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 		
		$data['mobile']= $mobile; 		
		$data['name']= $name; 		
		$data['isAdmin']= $isAdmin; 		
		$data['client']= $client; 		
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM'));
        $data['nav'] = array('1'=>'vendors','2'=>'orders','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM'));
		if( $admin){
			$data['main_content'] = 'orders/list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
        $data['status']   = $this->approval;
		
		
		$this->load->view(THEMES, $data);
    }


    public function create() {
        
        $action = $this->input->post('action');
		
        if ($action) {
            $orderid = $this->input->post('orderid');
            $remark  = $this->input->post('remark');
            $status  = $this->input->post('status');
            $supplier= $this->input->post('supplier_id');
            $driver  = $this->input->post('driver');
            $product = $this->input->post('productid');
            $discript= $this->input->post('description');
            $name    = $this->input->post('name');
            $qty     = $this->input->post('qty');
			$date    = date("Y-m-d H:i:s");
			
			$new_id ='0';
			if($action ==1){
				foreach($product as $k => $val){
					$data['product_id'] = $val;
					$data['name']       = $name[$val];
					$data['qty']        = $val[$val];
					$data['orderid']    = $orderid;
					$data['status']     = $status;
					$data['raiseby_remark']= $remark;
					$data['supplier_id']= $supplier;
					$data['created_on']= $date;
					$data['updated_on']= $date;
					$data['raiseby_id']    = $this->userid;
					$new_id = $this->modelCommon->insertTableData($data,'place_orders');					
				}
			}
			if($action ==2){
				//printr($this->input->post());
				
				foreach($product as $k => $val){
					$data = $log = [];
					$data['product_id'] = $val;
					$data['raiseby_id']    = $this->userid;
					$data['name']       = $name[$val];
					$data['qty']        = $qty[$val];
					$data['orderid']    = $orderid;
					$data['status']     = $status;
					$data['raiseby_remark']= $remark;
					$data['supplier_id']= $supplier;
					$data['driver_id']  = $driver;
					$data['created_on']= $date;
					$data['updated_on']= $date;
					$data['raiseby_date']= $date;
					$new_id = $this->modelCommon->insertTableData($data,'place_orders');
					$pdata =[];
					$pdata['description'] =$discript[$val];
					$this->modelCommon->updateTableData('products',$pdata,['id'=>$val]);
					$this->modelCommon->updateTableData('orders',['state'=>$status],['id'=>$orderid]);
					$log['userid']  = $this->userid;
					$log['orderid'] = $orderid;
					$log['product'] = $val;
					$log['notes']   = 'Supplier assign '.$remark;
					$log['created_on']= $date;
					$this->modelCommon->insertTableData($log,'logs');
				}
				//exit;
			}
            if ($new_id) {
				if(count($product) ==1)              
					redirect('process/processDetail/'.$new_id);
				else
					redirect('process/lists');
            }
        }
    }

	public function detail($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            
            $data['details'] = $this->modelOrders->getDetails($id);
			
        }
		$condition =['employee.status'=>1,'ctype'=>2];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['employee']= $rows['rows'];
		
		$condition =['employee.status'=>1,'ctype'=>3];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['drivers']= $rows['rows'];
		
		$data['isAdmin']= $isAdmin;
		$data['nav'] = array('1'=>'vendors','2'=>'orders','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD'));
		if( $admin){
			$data['main_content'] = 'orders/place_order';
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
            $row = $this->modelOrders->getDetails($id);
            $data['details'] = $row;
			
        }
		$condition =['employee.status'=>1];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['employees']= $rows['rows'];
		
		$data['isAdmin']= $isAdmin;
		$data['nav'] = array('1'=>'vendors','2'=>'orders','3'=>'');
		$data['clients']= $this->modelClients->getAll();
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','PM','SAL','CHA'));
		if( $admin){
			$data['main_content'] = 'orders/edit';
		}
		
        $this->load->view(THEMES, $data);
    }
	
	public function add() {
       	$data['scripts'][] = SCRIPT . base_url('assets/js/sections/list.js') . END_SCRIPT;
		$admin =$this->authentication->check_auth(array('ADMIN','MD','RM'));
		if($admin){
			$this->load->view('orders/model_add');
		}else{
			$this->load->view('not_authorized');
		}
		
    }
	
	public function manualOrder() {
       	$data['scripts'][] = SCRIPT . base_url('assets/js/sections/list.js') . END_SCRIPT;
		$admin =$this->authentication->check_auth(array('ADMIN','MD','RM'));
		$todate   = date('Y-m-d');	
		$fromdate =  date('Y-m-d', strtotime('-10 day', strtotime($todate)));	
		
		$condition="DATE(orders.created_on) between '".$fromdate."' and '".$todate."'";
		$this->db->where("$condition");		
		$this->db->select("orders.*", FALSE);
        $this->db->order_by("id",'desc');		
		$data['rows'] = $this->db->get('orders')->result();
		
		$condition =['employee.status'=>1,'ctype'=>2];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['suppliers']= $rows['rows'];
		
		
		if($admin){
			$this->load->view('orders/model_manual_order',$data);
		}else{
			$this->load->view('not_authorized');
		}
		
    }
	public function manualOrderPlace(){
		
		
		$submit = $this->input->post('submit');
		if($submit){
			$date    = date("Y-m-d H:i:s");
			$supplier =$this->input->post('supplier');
			$order  = $this->input->post('order');
			
			$data =$this->input->post('data');
			$data['created_on']= $date;
			$data['updated_on']= $date;
			$data['raiseby_date']= $date;
			$new_id = $this->modelCommon->insertTableData($data,'place_orders');
			
			$log['userid']  = $this->userid;
			$log['orderid'] = $data['orderid'];
			$log['product'] = $data['product_id'];
			$log['notes']   = 'Supplier assign '.$data['raiseby_remark']. ' '.$order.' '.$supplier;
			$log['created_on']= $date;
			$this->modelCommon->insertTableData($log,'logs');
			redirect('process/processDetail/'.$new_id);		
		}
		
	}
	
	
	public function getProducts(){
		$orderid = $this->input->get('id');
		
		
		$this->db->where("orderid",$orderid);
		
		$this->db->select("id,product_id as pid,product", FALSE);
        $this->db->group_by("product_id");		
		$data['rows'] = $this->db->get('products')->result();
		echo json_encode($data);
	}
	
	
	public function download(){
		// get API Information
		$query ="select * from api where status = 1";
		$api=$this->db->query($query)->row();
		
		// get CS Card category Information
		//$query ="select * from sections where status = 1";
		//$row=$this->db->query($query)->result();
		$submit = $this->input->post('submit');
		$row = $this->input->post('data');
		
		if(!empty($row)){
			foreach($row as $val){
				
				if(1){
					$i=$val;
					$j=$val;
					for(; $i<=$j; $i++) {
						$query = $this->checkProduct($i,$api);
						
						$sql ="select id from orders where order_id = $i";
						$flag=1;
						$check=$this->db->query($sql)->row();
						if(!empty($check)){
							$flag=0;
						}
						$p_detail = $query->products;
						$p_groups = $query->product_groups[0]->products;
						$data = array();
						$data['order_id']           =$query->order_id;
						$data['is_parent_order']    =$query->is_parent_order;
						$data['parent_order_id']    =$query->parent_order_id;
						$data['company_id']         =$query->company_id;
						$data['issuer_id']          =$query->issuer_id;
						$data['user_id']            =$query->user_id;
						$data['total']              =$query->total;
						$data['subtotal']           =$query->subtotal;
						$data['discount']           =$query->discount;
						$data['subtotal_discount']  =$query->subtotal_discount;
						$data['payment_surcharge']  =$query->payment_surcharge;
						$data['shipping_ids']       =$query->shipping_ids;
						$data['shipping_cost']      =$query->shipping_cost;
						$data['timestamp']          =date("Y-m-d H:i:s",$query->timestamp);
						$data['status']             =$query->status;
						$data['notes']              =$query->notes;
						$data['details']            =$query->details;
						$data['promotion_ids']      =$query->promotion_ids;
						$data['firstname']          =$query->firstname;
						$data['lastname']           =$query->lastname;
						$data['company']            =$query->company; 
						$data['b_firstname']        =$query->b_firstname;
						$data['b_lastname']         =$query->b_lastname;
						$data['b_address']          =$query->b_address;
						$data['b_address_2']        =$query->b_address_2;
						$data['b_city']             =$query->b_city;
						$data['b_county']           =$query->b_county;
						$data['b_state']            =$query->b_state;
						$data['b_country']          =$query->b_country;
						$data['b_zipcode']          =$query->b_zipcode;
						$data['b_phone']            =$query->b_phone;
						$data['s_firstname']        =$query->s_firstname;
						$data['s_lastname']         =$query->s_lastname;
						$data['s_address']          =$query->s_address;
						$data['s_address_2']        =$query->s_address_2;
						$data['s_city']             =$query->s_city;
						$data['s_county']           =$query->s_county;
						$data['s_state']            =$query->s_state;
						$data['s_country']          =$query->s_country;
						$data['s_zipcode']          =$query->s_zipcode;
						$data['s_phone']            =$query->s_phone;
						$data['s_address_type']     =$query->s_address_type; 
						$data['phone']              =$query->phone;
						$data['fax']                =$query->fax;
						$data['url']                =$query->url;
						$data['email']              =$query->email;
						
						if($flag){
							$data['created_on'] = $data['updated_on'] =date("Y-m-d H:i:s");
							$orderid = $this->modelCommon->insertTableData($data,'orders');
						}else{
							$data['updated_on'] =date("Y-m-d H:i:s");
							$orderid = $check->id;							
							$this->modelCommon->updateTableData('orders',$data,['id'=>$orderid]);
							$this->modelCommon->deleteTableData('products',['orderid'=>$orderid]);
							
						}
						foreach($p_detail as $key => $val){
							$product = array();
							$product['orderid']          =$orderid;//  $val->order_id;
							$product['product_id']       =$val->product_id;
							$product['product_code']     =$val->product_code;
							$product['price']            =$val->price;
							$product['amount']           =$val->amount;
							$product['product']          =utf8_encode($val->product);
							$product['product_status']   =$val->product_status;
							$product['deleted_product']  =$val->deleted_product;
							$product['discount']         =$val->discount;
							$product['company_id']       =$val->company_id;
							$product['base_price']       =$val->base_price;
							$product['original_price']   =$val->original_price;
							$product['cart_id']          =$val->cart_id;
							$product['tax_value']        =$val->tax_value;
							$product['subtotal']         =$val->subtotal;
							$product['display_subtotal'] =$val->display_subtotal;
							$product['shipped_amount']   =$val->shipped_amount;
							$product['shipment_amount']  =$val->shipment_amount;
							$product['is_accessible']    =$val->is_accessible;
							//$product['shared_product']   =$val->shared_product; 
							$product['created_on'] = $product['updated_on'] =date("Y-m-d H:i:s");
							
							if(!empty($val->extra->product_options)){
								$unit = $val->extra->product_options_value;
								
								$product['unit'] = $unit[0]->option_name;
								$product['uval'] = $unit[0]->variant_name;
								$product['unit1'] = @$unit[1]->option_name;
								$product['uval1'] = @$unit[1]->variant_name;
							}
							$proAPI = str_replace('orders','products',$api->name);
							$api->name = $proAPI;
							$short = $this->checkProduct($val->product_id,$api);
							$product['descrip'] = trim(@$short->short_description);
							$product['description'] = trim(@$short->short_description);
							$product['image']= utf8_encode(@$short->main_pair->detailed->image_path);
							//$obj->insertQuery($product,'products',1);
							$this->modelCommon->insertTableData($product,'products');
						//	$obj->printr($product);
						}
					}
				}
			}
		}
		redirect('orders');
	}
	
	function checkProduct($orderno,$api){

		$url  = $api->name.$orderno;
		$user = $api->user;
		$pass = $api->password;
        
		$flag = 1;
		$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_HTTPAUTH => CURLAUTH_ANY,
			CURLOPT_USERPWD  => "$user:$pass",
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_USERAGENT => 'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36'
		));
		$resp = json_decode(curl_exec($curl));
		curl_close($curl);
		
		return $resp;
	}
	
	function deleted($id){
		
		//echo $id = $this->input->get('id');
		
		$admin =$this->authentication->check_auth(array('ADMIN','MD'));
		if($admin){			
			$this->db->where("orderid",$id);
			$this->db->select("id", FALSE);		
			$row = $this->db->get('tasks')->result();
			$taskid = $row[0]->id;
		   
			$this->modelCommon->deleteTableData('orders',['id'=>$id]);
			$this->modelCommon->deleteTableData('products',['orderid'=>$id]);
			$this->modelCommon->deleteTableData('place_orders',['orderid'=>$id]);
			$this->modelCommon->deleteTableData('tasks',['orderid'=>$id]);
			$this->modelCommon->deleteTableData('tasks_products',['taskid'=>$taskid]);
			redirect('orders');
		}else{
			
			$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
			$data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
			$data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
			
			$data['main_content'] = 'not_authorized';			
			$this->load->view(THEMES, $data);
		}
	}
	
	
	public function logs($pa=1) {
        extract($this->authentication->user_groups_auth);
       
		$filter = [];		
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
		$key   = $this->input->get('key');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$mobile      = $this->input->get('mobile');
		
		$filter['key']  = $key;
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelOrders->logsLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."orders/logs/";
		
		$page['search_url']= '?key='.$key.'&todate='.$todate.'&fromdate='.$fromdate;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 		
		$data['fromdate']= $fromdate; 		
		$data['todate']= $todate; 		
		$data['key']= $key; 		
		$data['isAdmin']= $isAdmin; 
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','MD'));
        $data['nav'] = array('1'=>'vendors','2'=>'log','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD'));
		if( $admin){
			$data['main_content'] = 'process/logs';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
        $data['status']   = $this->approval;
		
		
		$this->load->view(THEMES, $data);
    }
	
	function deleteLog($id){
		$this->modelCommon->deleteTableData('logs',['id'=>$id]);
		redirect('orders/logs');
	}
	function bulkDelete(){
		
		$rowid= $this->input->post('rowid');
		$days = $this->input->post('days');
		$date = date('Y-m-d', strtotime('-'.$days.' day'));
		if((int)$days){
			$this->db->where("'$date' > DATE(created_on)");
			$this->db->delete('logs');
			//echo $this->db->last_query();
			//exit;
		}else{
		
			foreach($rowid as $k => $id){
				$this->modelCommon->deleteTableData('logs',['id'=>$id]);
			}
		}
		redirect('orders/logs');
	}
	
	function exportOrder(){
		
		$result  = $this->modelOrders->exportOrder();
		
		$name = time().'.xlsx';
		$rows[]= ['Order No','Name','Address','Product','Quantity'];
		if(!empty($result)){
			foreach($result as $k =>$val){
				
				$name    =  $val->s_firstname.' '.$val->s_lastname;
				$address =  $val->s_address.', '.$val->s_city.', '.$val->s_state.', '.$val->s_zipcode;
				$item = array();
				$item[]= $val->order_id;
				$item[]= $name;
				$item[]= $address;
				$item[]= $val->product;
				$item[]= $val->qty;
				
				$rows[]= $item;
			}
		}		
		
		$xlsx = SimpleXLSXGen::fromArray( $rows );
		$xlsx->downloadAs($name);
	}
	
	
}
