<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include_once 'ControllerBase.php';

class ControllerDashboard extends ControllerBase {

    function __construct() {
        parent::__construct();
		 $this->load->model('modelDashboard');
		 $this->load->model('modelOrders');
		 $this->load->model('modelTasks');
    }

    public function index() {
        $this->dashboard();  
    }

    
    public function dashboard() {
        extract($this->authentication->user_groups_auth);
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap/js/bootstrap.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/dashboard/script.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/tooltip.js') . END_SCRIPT;
		
        $data['nav'] = array('1'=>'dashboard','2'=>'home','3'=>'');
        $data['title'] = 'Rahail ';
		$data['price_info'] = $this->authentication->check_auth(['ADMIN','MD','SUE']);;
		$data['main_content'] = 'dashboard2';
		if($isAdmin ||$isMD || $isSUE ||$isSV || $isDRV){
			$data['main_content'] = 'dashboard';
		}
        
        $this->load->view(THEMES, $data);
    }
	
	public function getDashboardNotification(){
		extract($this->authentication->user_groups_auth);
		
		$userid = getName('user_name');
		$filter = $this->getFilter();
		
		$data['orders'] = 0;
		$data['orders_ttl'] = 0;
		$data['status'] = 0;
		$order = $pack = $driver = $assign = $delever='';
		$or = $pa = $dr = $ass = $de=0;
		
		$rows =$this->modelOrders->processList("",['data_per_page'=>30],$filter['order']);
		$task =$this->modelTasks->orderList("",['data_per_page'=>30],$filter['task']);
		//echo $filter['task'];
		//printr($task);
		
		if($isAdmin || $isMD || $isRM){
			if(!empty($task)){
				$data['taskst'] = @$task['rows'][0]->status;
				$data['task']    = $task['rows'];
				$data['task_ttl'] = $task['ttl_rows'];
			}
			if(!empty($rows)){
				foreach($rows['rows'] as $k => $val){
					//printr($val);
					if($val->status == 50 || $val->status == 320){
						$or++;
						$order .='<div>';
						$order .='<strong>Order NO.:'.$val->order_no.'</strong><br>';
						$order .='<a href="'.base_url('process/edit/'.$val->id).'"><strong>Name:</strong>'.$val->name.'<br><strong>Description</strong> '.$val->description.'</a></td>';
						$order .='<p><strong>Qty:</strong>'.$val->qty.'<br>';
						if($val->unit !='')
							$order .=$val->unit.':'.$val->uval.'<br>';
						if($val->unit1 !='')
							$order .= $val->unit1.':'.$val->uval1;
						
						$order .='</p></div';
						
					}elseif($val->status == 100){
						$or++;
						$order .='<div>';
						$order .='<strong>Order NO.:'.$val->order_no.'</strong><br>';
						$order .='<a href="'.base_url('process/edit/'.$val->id).'"><strong>Name:</strong>'.$val->name.'<br><strong>Description</strong> '.$val->description.'</a></td>';
						$order .='<p><strong>Qty:</strong>'.$val->qty.'<br>';
						if($val->unit !='')
							$order .=$val->unit.':'.$val->uval.'<br>';
						if($val->unit1 !='')
							$order .= $val->unit1.':'.$val->uval1;
						
						$order .='</p></div';
						
					}elseif($val->status == 150 || $val->status == 220){
						$level = ($val->status == 150)?"Pickup":"Deliver";
						$ass++;
						$assign .='<div>';
						$assign .='<strong>Order NO.:'.$val->order_no.'</strong>('.$level.')<br>';
						$assign .='<a href="'.base_url('process/edit/'.$val->id).'"><strong>Name:</strong>'.$val->name.'<br><strong>Description</strong> '.$val->description.'</a></td>';
						$assign .='<p><strong>Qty:</strong>'.$val->qty.'<br>';
						if($val->unit !='')
							$assign .=$val->unit.':'.$val->uval.'<br>';
						if($val->unit1 !='')
							$assign .= $val->unit1.':'.$val->uval1;
						
						$assign .='</p>';
						
						$assign .='<p ><strong>Weight:</strong>'.$val->weight.'<br>';
						$assign .='<strong>Price:</strong>'.$val->price.'</p>';
						$assign .='</div';
						
						
					}elseif($val->status == 200){
						$pa++;
						$pack .='<div>';
						$pack .='<strong>Order NO.:'.$val->order_no.'</strong><br>';
						$pack .='<a href="'.base_url('process/edit/'.$val->id).'"><strong>Name:</strong>'.$val->name.'<br><strong>Description</strong> '.$val->description.'</a></td>';
						
						$pack .='<p><strong>Qty:</strong>'.$val->qty.'<br>';
						if($val->unit !='')
							$pack .=$val->unit.':'.$val->uval.'<br>';
						if($val->unit1 !='')
							$pack .= $val->unit1.':'.$val->uval1;
						
						$pack .='</p>';
						
						$pack .='<p ><strong>Weight:</strong>'.$val->weight.'<br>';
						$pack .='<strong>Price:</strong>'.$val->price.'</p>';
						$pack .='</div';
						
					}elseif($val->status == 250){
						$de++;
						$delever .='<div>';
						$delever .='<strong>Order NO.:'.$val->order_no.'</strong><br>';
						$delever .='<a href="'.base_url('process/edit/'.$val->id).'"><strong>Name:</strong>'.$val->name.'<br><strong>Description</strong> '.$val->description.'</a></td>';
						$delever .='<p><strong>Qty:</strong>'.$val->qty.'<br> '.$val->unit.':'.$val->uval.'</p>';
						if($val->unit1 !='')
							$delever .= '<p>'.$val->unit1.':'.$val->uval1.'<br></p>';						
						$delever .='<p ><strong>Weight:</strong>'.$val->weight.'</p>';					
						$delever .='</div';
						
					}
				}
			}
			
			$data['orders'] = $order;
			$data['orders_ttl'] = $or;
			
			$data['pack'] = $pack;
			$data['pack_ttl'] = $pa;
			
			$data['driver'] = $driver;
			$data['driver_ttl'] = $dr;			
			
			$data['assigns'] = $assign;
			$data['assign_ttl'] = $ass;
			
			$data['delever'] = $delever;
			$data['delever_ttl'] = $de;
			$data['admin'] = 1;
			//printr($data['assigns']);
			//printr($data['delever']);
		}else{
			$data['admin'] = '';
			$data['ostatus'] = @$rows['rows'][0]->status;
			$data['orders'] = $rows['rows'];
			$data['orders_ttl'] = $rows['ttl_rows'];
			
			$data['taskst'] = @$task['rows'][0]->status;
			$data['task'] = $task['rows'];
			$data['task_ttl'] = $task['ttl_rows'];
		}
			
		
		echo json_encode($data);
	}
	
	private function getFilter(){
		extract($this->authentication->user_groups_auth);
		
		$userid = getName('user_id');
		
		$filter = [];
		if($isRM){
			$filter['order'] = "(((place_orders.status=50) || (place_orders.status=150 || place_orders.status=220 || place_orders.status=320) ) && ((raiseby_id=$userid) || (rm_id=$userid)))";
			$filter['task'] = "( ((tasks.status = 50) && (tasks.raiseby_id = $userid)) || ((tasks.status = 400) ))";
		}
		if($isSV){
			$filter['order'] = "( (place_orders.status = 100) && (place_orders.supplier_id = $userid) )";
			$filter['task'] = "( (tasks.status = 350) && (tasks.supplier_id = $userid) )";
		}
		if($isDRV ){
			$filter['order'] = "(((place_orders.status=200) || (place_orders.status=250)) && ((driver_id=$userid) || (driver2_id=$userid)))";
			$filter['task'] = "((tasks.status = 450) && (tasks.driver_id = $userid) )";
		}
		if($isAdmin || $isMD ){
			
			$filter['order'] = "((place_orders.status between 50 and 320) and (place_orders.status != 300))";
			$filter['task'] = "( (tasks.status = 50) || (tasks.status = 400))";
		}
		
		return $filter;
	}
	
	public function changePassForm(){
	
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/dashboard/script.js') . END_SCRIPT;
        $data['main_content'] = 'setting/pass/add';
		
		$data['nav'] = array('1'=>'dashboard','2'=>'pass');
        $data['title'] = 'Leraar System Change Password';
		$id = getName('user_id');
        $row = $this->modelCommon->getTableData('employee',array('id'=>$id));
        $data['row'] = $row[0];
        $this->load->view(THEMES, $data);
	}
	
	public function changePass(){
	
		$submitac = $this->input->post('submitac');
		$password = trim($this->input->post('password'));
		$name     = trim($this->input->post('name'));
		$last_name= trim($this->input->post('last_name'));
		$mobile   = trim($this->input->post('mobile'));
		$email    = trim($this->input->post('email'));
		
		$userid = getName('user_id');
		
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/dashboard/script.js') . END_SCRIPT;
        $data['main_content'] = 'setting/pass/view';
		
		$data['nav'] = array('1'=>'dashboard','2'=>'pass','3'=>'');
        $data['title'] = 'Change Password';
		
		$ed['name']  = $name;
		$ed['last_name'] = $last_name;
		$ed['mobile']     = $mobile;
		$ed['email']      = $email;
		if($password){
			$ed['password'] = md5($password);
		}
		
		$this->modelCommon->updateTableData('employee',$ed,array('id'=>$userid));
        
        $this->load->view(THEMES, $data);
	}
	
	
}
