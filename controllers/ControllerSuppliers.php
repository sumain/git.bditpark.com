<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerSuppliers extends ControllerBase {

	public $path = '';
	public $path1 = '';
    public function __construct() {
        parent::__construct();
        $this->load->model('modelSuppliers');
		$this->path = $this->root.'uploads/';
		$this->path = $this->root.'uploads/documents/';
		$this->path1 = 'uploads/documents/';
        @mkdir('uploads');
        @mkdir('uploads/documents/');
    }

    public function index() {
        $this->lists();
    }

     public function lists($pa=1) {
        extract($this->authentication->user_groups_auth);
       
		$filter = [];
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/suppliers/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$name    = $this->input->get('name');
		$work    = $this->input->get('work');
		$type    = $this->input->get('type');
		$mobile  = $this->input->get('mobile');
		
		$filter['mobile'] = $mobile;
		$filter['name'] = $name;
		$filter['work'] = $work;
		$filter['mobile'] = $mobile;
		$filter['type'] = $type;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 20;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelSuppliers->suppliersLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."suppliers/lists/";
		
		$page['search_url']= '?mobile='.$mobile.'&name='.$name.'&work='.$work.'&type='.$type;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 
		$data['mobile']= $mobile; 		
		$data['name']= $name; 		
		$data['isAdmin']= $isAdmin; 		
		
        
        $data['nav'] = array('1'=>'inventory','2'=>'suppliers','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD','CHA'));
		if( $admin){
			$data['main_content'] = 'suppliers/list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$this->load->view(THEMES, $data);
    }

    public function add() {       
        extract($this->authentication->user_groups_auth);
       
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/suppliers/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
        $data['nav'] = array('1'=>'inventory','2'=>'suppliers','3'=>'');
		$data['isAdmin']= $isAdmin;
		
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD','CHA'));
		if($admin){
			$data['main_content'] = 'suppliers/add';
		}
		
        $this->load->view(THEMES, $data);
    }

    public function create() {
        
        $action = $this->input->post('submit');
		
        if ($action) {
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$new_id = $this->modelSuppliers->createNew($form_data);
			
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,2);
			
			$log['userid']  = getName('user_id');
			$log['orderid'] = 0;
			$log['product'] = 0;
			$log['notes']   = 'New Supplier "('.$form_data['name'].')" add in supplier table';
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
            if ($new_id) {
               redirect('suppliers/lists/');
              
            }
        }
    }

    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/suppliers/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $row = $this->modelSuppliers->getDetails($id);
            $data['details'] = $row;
			
        }
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>2]);
        $data['path'] = $this->path1;
		$data['isAdmin']= $isAdmin;
		
		
		
		$data['nav'] = array('1'=>'inventory','2'=>'suppliers','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD','CHA'));
		if( $admin){
			$data['main_content'] = 'suppliers/edit';
		}
		
        $this->load->view(THEMES, $data);
    }

    public function update() {
        $action = $this->input->post('update');
        if ($action) {
            $form_data = $this->input->post('data');
			//$roleid     = $this->input->post('roleid'); 
			$form_data = array_map('trim',$form_data);
			
			$form_data = $this->security->xss_clean($form_data);
			
            $id = $this->input->post('update_id');
			
            $this->modelSuppliers->update($form_data, $id);
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($id,$this->path,2);
			$log['userid']  = getName('user_id');
			$log['orderid'] = 0;
			$log['product'] = 0;
			$log['notes']   = 'update Supplier table "'.$form_data['name'].'"';
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
            redirect('suppliers/lists/');
        }
    }

    public function changeStatus() {
        $id = (int) $this->input->post('id');
        $data['status'] = $this->input->post('status');
		$this->modelSuppliers->update($data, $id);		
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		echo json_encode($data);
    }
	public function delete($id){
		
		$this->modelSuppliers->delete($id);
		
		$files= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>2]);
		if(!empty($files)){
			foreach($files as $val){
				$file = $this->path.$val->image;
				@unlink($file);
				$this->modelCommon->deleteTableData('documents',['id'=>$val->id]);
			}
		}
			
		redirect('suppliers/lists/');
	}
	
	public function checkMobile(){
		$flag['mobile'] ='';
		if ($this->input->is_ajax_request()) {            
            $mobile = $this->input->get('mobile');
            $id = $this->input->get('id');
            $condition['mobile'] = 	$mobile;	
           $result= $this->modelCommon->getTableData('suppliers',$condition);
		   if(!empty($result)){
				$flag['mobile'] =1;
			}
			$flag['csrfName'] = $this->security->get_csrf_token_name();
			$flag['csrfHash'] = $this->security->get_csrf_hash();
		   echo json_encode($flag);
            
        }else{
			$flag['error'] = 'Unauthorized request';
			echo json_encode($flag);
		}
	}
	
}
