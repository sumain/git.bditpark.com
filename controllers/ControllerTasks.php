<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerTasks extends ControllerBase {

	protected $approval;
	protected $fields;
	protected $userid;
	protected $appstatus = ['50'=>'Status','350'=>'Supplier/Driver','400'=>'Admin/Sale','450'=>'Driver','500'=>'Completed','505'=>'Create New Task','510'=>'Re-Assign','515'=>'Cancel'];
    public function __construct() {
        parent::__construct();
		include APPPATH . 'third_party/phpqrcode/qrlib.php';
		$this->path = $this->root.'uploads/slip/';
		
        $this->load->model('modelOrders');
        $this->load->model('modelEmployee');
        $this->load->model('modelTasks');
		$this->approval= ['50'=>'RM','350'=>'SV','400'=>'RM','450'=>'DRV','500'=>'Completed'];
		$this->fields= ['50'=>'raiseby','350'=>'supplier','400'=>'rm','450'=>'driver','500'=>'complete'];
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
		$emp   = $this->input->get('emp');
		$status= $this->input->get('status');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        $type   = $this->input->get('type');
        $order   = $this->input->get('order');
		
        $data['fromdate']  = $fromdate;
        $data['todate']    = $todate;
        $data['type']    = $type;
        $data['order']    = $order;
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/orders/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['tasks.status']= $status;
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		$filter['order_id']= $order;
		if($isSV){
			$filter['tasks.supplier_id']= $this->userid;
		}elseif($isDRV){
			$filter2= "((tasks.driver_id = $this->userid))";
		}elseif($isAdmin || $isMD){
			if($type ==2){
				$filter['tasks.supplier_id']= $emp;
			}elseif($type ==3){
				$filter['tasks.driver_id']= $emp;
			}
		}
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelTasks->orderList($filter,$page,$filter2);
		//printr($filter2 );
		//printr($filter );
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."tasks/lists/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
				
		$data['isAdmin']= $isAdmin; 		
		$data['status']= $status; 		
		$data['emp']   = $emp; 		
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM'));
        $data['nav'] = array('1'=>'Task','2'=>'task','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin){
			$data['main_content'] = 'tasks/list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$data['appstatus'] = $this->appstatus;
		$data['employees'] = $this->modelEmployee->getEmployees([2,3]);
		
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
			$data['main_content'] = 'tasks/mobile_list';
		}
		//$data['main_content'] = 'tasks/mobile_list';
		$this->load->view(THEMES, $data);
    }
	
    public function forward() {
        
        $action = $this->input->post('action');
        $taskid = $this->input->post('taskid');
        $orderid = $this->input->post('orderid');
        $product = $this->input->post('product');
        $qty     = $this->input->post('qty');
		//printr($this->input->post());
		//exit;
        if ($action) {
            $received  = $this->input->post('received');
            $remark  = $this->input->post('remark');
            $status  = $this->input->post('status');
			$keys    = array_keys($this->fields);
			
			$field   = $this->fields[$status];
			$date    = date("Y-m-d H:i:s");
			if($action ==1){				
				$data[$field.'_remark']= $remark;
				$data[$field.'_id']    = $this->userid;				
			}elseif($action == 2){
				$seq = $this->input->post('seq');
				$box = $this->input->post('box');
				$data = $this->input->post('data');
				$field = $this->fields[$keys[array_search($status,$keys)-1]];
				$data[$field.'_id']= $this->userid;
				$data['status']     = $status;
				$data[$field.'_remark']= $remark;
				$data[$field.'_date']  = $date;
				if($status == 450){
					$data['box']  = $box;
					$data['seq']  = $seq;
				}
		        
				if($taskid){
					$data['updated_on'] = $date;
					$this->modelCommon->updateTableData('tasks',$data,['id'=>$taskid]);
					$this->modelCommon->updateTableData('orders',['status'=>$status],['id'=>$orderid]);
				}else{
					$data['created_on'] = $data['updated_on'] = $date;
					$taskid = $this->modelCommon->insertTableData($data,'tasks');
					$this->modelCommon->updateTableData('orders',['status'=>$status],['id'=>$orderid]);
				}
				if(!empty($received)){
					
					foreach($received as $k => $val){
						$data=[];
						$con['proid'] = $k;
						$con['taskid'] = $taskid;
						$data['rec_qty'] = $val;
						
						$this->modelCommon->updateTableData('tasks_products',$data,$con);
					}
				}
				if(!empty($product)){
					
					foreach($product as $k => $val){
						$data=[];
						$data['proid'] = $val;
						$data['taskid'] = $taskid;
						$data['qty'] = $qty[$val];
						$this->modelCommon->insertTableData($data,'tasks_products');;
					}
				}
				
				//$appstatus
				$log['userid']  = $this->userid;
				$log['orderid'] = 0;
				$log['product'] = 0;
				$log['notes']   = 'Assign task "'.$this->appstatus[$status] .'" '.$remark;
				$log['created_on']= dates();
				$this->modelCommon->insertTableData($log,'logs');
				
			}else{				
				$data['status']     = 500;
				///$data['rm_id']     = 0;
				$status = $this->input->post('save');
				
				$field = $this->fields[$keys[array_search($status,$keys)]];
				
				$data[$field.'_remark']= 'Cancel because:-'.$remark;
				$data[$field.'_id']    = $this->userid;
				if($status ==450){
					$data[$field.'_date']  = $date;
					$data['status']  = 400;
				}
				
				$this->modelCommon->updateTableData('tasks',$data,['id'=>$taskid]);
				//
				//$appstatus
				$log['userid']  = $this->userid;
				$log['orderid'] = 0;
				$log['product'] = 0;
				$log['notes']   = 'Cancel because "'.$this->appstatus[$status] .'" '.$remark;
				$log['created_on']= dates();
				$this->modelCommon->insertTableData($log,'logs');
			}
			 //$this->modelCommon->updateTableData('place_orders',$data,['id'=>$orderid]);	
			
            redirect('tasks/taskDetail/'.$taskid);
			
        }
    }

	public function taskDetail($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/tasks/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {            
            $row = $this->modelTasks->getTaskDetail($id);			
            $data['details'] = $row;			
        }
		//printr($row);
		$status = $row[0]->status;
		
		$keys = array_keys($this->approval);	 
		
		//$previous =$keys[array_search($status,$keys)-1];
		if($status <300)
			$next     =$keys[array_search($status,$keys)+1];
		
		
		
		$data['auth']= $isAdmin;
		$data['userid']= $this->userid;
		$data['nav'] = array('1'=>'Task','2'=>'task','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin){
			$data['main_content'] = 'tasks/detail';
		}
		
        $this->load->view(THEMES, $data);
    }
	public function add() {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/tasks/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
		$data['details'] = array();
		
		$data['save']   = '50';
		$data['forward']='350';	
		$data['cancel'] = '400';
		
		
		$data['auth']= $this->authentication->check_auth(array('ADMIN','MD','RM'));
		$data['drivers']= '';
		$data['nav'] = array('1'=>'Task','2'=>'taskadd','3'=>'');
		
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
    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/tasks/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
        $id = intval($id);
        $data['details'] = array();
        if ($id) {            
            $row = $this->modelTasks->getTaskDetail($id);			
            $data['details'] = $row;			
        }
		
		$status = $row[0]->status;
		
		$keys = array_keys($this->approval);
        
		$data['save'] = $status;
		$data['forward'] =$keys[array_search($status,$keys)+1];	
		if($isDRV){
			$data['cancel'] = 400;
		}else{
			$data['cancel'] = 500;
		}
		
		$data['auth']= $this->authentication->check_auth(array('ADMIN','MD','RM'));
		$data['drivers']= '';
		$data['nav'] = array('1'=>'Task','2'=>'taskadd','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin ){
			$data['main_content'] = 'tasks/process_edit';
		}
		
		$condition =['employee.status'=>1,'ctype'=>3];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$data['drivers']= $this->modelEmployee->employeeLists($condition,$limit);
		
		$condition =['employee.status'=>1,'ctype'=>2];
		$limit = ['data_per_page'=>100,'offset'=>0];
		$rows= $this->modelEmployee->employeeLists($condition,$limit);
		$data['employee']= $rows['rows'];
		
		$authentication = 1;//$this->authentication->check_auth($this->approval[$status]);
		
		if($authentication == false ){
			if($data['auth'] == false){
				redirect('process/processDetail/'.$row[0]->id);
			}
		}
		
								
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
	
	function getOrders(){
		$id = $this->input->post('id');
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		if($id){
			$result = $this->modelTasks->orderCS($id);
			$data['rows'] = $result;
			//echo $this->db->last_query();
		}
        echo json_encode($data);
	}
	function getEmployee(){
		$id = $this->input->post('id');
		
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		if($id){
			$condition =['employee.status'=>1,'ctype'=>$id];
			$limit = ['data_per_page'=>100,'offset'=>0];
			$drivers= $this->modelEmployee->employeeLists($condition,$limit);
			$data['drivers']= $drivers['rows'];
		}
        echo json_encode($data);	
	}
	
	public function supplierGroupList($pa=1) {
        extract($this->authentication->user_groups_auth);
        $data['app'] = $this->appstatus;
		$filter2 = '';		
		$filter = [];		
		$emp   = $this->input->get('emp');
		$status= $this->input->get('status');
		$fromdate = $this->input->get('fromdate');
        $todate   = $this->input->get('todate');
        $type   = $this->input->get('type');
		
        $data['fromdate']  = $fromdate;
        $data['todate']    = $todate;
        $data['type']    = $type;
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/tasks/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['tasks.status']= $status;
		$filter['fromdate']= $fromdate;
		$filter['todate']= $todate;
		
		if($isSV){
			$filter['tasks.supplier_id']= $this->userid;
			$filter['tasks.status']= 350;
		}elseif($isDRV){
			$filter2= "((tasks.driver_id = $this->userid) and (tasks.status = 350))";
		}elseif($isAdmin || $isMD){
			echo "dd";
			if($type ==2){
				$filter['tasks.supplier_id']= $emp;
				$filter['tasks.status']= 350;
			}elseif($type ==3){
				$filter['tasks.driver_id']= $emp;
			}else{
				$filter['tasks.status']= 350;
			}
		}
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelTasks->supplierGroupList($filter,$page,$filter2);
		//printr($filter2 );
		//printr($result );
		//exit;
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."tasks/supplierGroupList/";
		
		$page['search_url']= '?status='.$status;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
				
		$data['isAdmin']= $isAdmin; 		
		$data['status']= $status; 		
		$data['emp']   = $emp; 		
		
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM'));
        $data['nav'] = array('1'=>'Task','2'=>'sgroup','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin){
			$data['main_content'] = 'tasks/supplier_group_list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$data['approval'] = $this->approval;
		$data['fields'] = $this->fields;
		$data['employees'] = $this->modelEmployee->getEmployees([2,3]);
		$this->load->view(THEMES, $data);
    }
	
	public function taskPrint() {
		extract($this->authentication->user_groups_auth);
		$id = $this->input->get('id');
		$t  = $this->input->get('t');
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/js/tasks/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {  
            $row = $this->modelTasks->getTaskDetail($id);			
            $data['details'] = $row;			
        }
		$profile = $this->modelCommon->getTableData('profile',['status'=>1]);
		//printr($row);
		
		$order_id =$row[0]->order_id;
		$name     =$row[0]->name;
		$qbar     =$row[0]->qbar;
		$address  =$row[0]->address;
		
        if($qbar == false){
			$qbar  = $address;
			//$qbar  = $order_id;
			
			$filename1 = $order_id.'_'.time().'.png';
			$filename = $this->path.$filename1;
			QRcode::png($qbar, $filename, 'L','4', 4); 
			
			$filename = $this->path.'2_'.$filename1;
			QRcode::png($qbar, $filename, 'L','2', 2); 	
			
			$this->modelCommon->updateTableData('tasks',['qbar'=>$filename1],['id'=>$id]);
		}
			
		$data['profile']= $profile[0];
		$data['auth']= $isAdmin;
		$data['userid']= $this->userid;
		$data['nav'] = array('1'=>'Task','2'=>'task','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','SV','DRV','RM'));
		if( $admin){
			if($t==1){
				$data['main_content'] = 'tasks/print_slip';
			}else{
				$data['main_content'] = 'tasks/print_label';
			}
		}
		
        $this->load->view(THEMES, $data);
    }
	
	public function saveGroup() {
		$product = $this->input->post('product');
		$flag    = $this->input->post('flag');
		$action  = $this->input->post('action');
		if($action){
			$date = date('Y-m-d');
			$flag =($flag==1)?"0":"1";
			$data['productid'] = $product;
			$data['flag']      = $flag;
			$data['date']      = $date;
			$profile = $this->modelCommon->getTableData('product_group',['productid'=>$product,'date'=>$date]);
			if(empty($profile)){				
				$this->modelCommon->insertTableData($data,'product_group');
			}else{
				$this->modelCommon->updateTableData('product_group',$data,['productid'=>$product,'date'=>$date]);
			}
			
		}	
		
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		echo json_encode($data);
    }
	
	function deleteTask($id){
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SUE'));
		if($id && $admin){
			$this->modelCommon->deleteTableData('tasks',['id'=>$id]);
			$this->modelCommon->deleteTableData('tasks_products',['taskid'=>$id]);
			
		//$appstatus
			$log['userid']  = $this->userid;
			$log['orderid'] = 0;
			$log['product'] = 0;
			$log['notes']   = 'Delete task because "Created New Task"';
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
			redirect('tasks/');
		}
	}
	
}
