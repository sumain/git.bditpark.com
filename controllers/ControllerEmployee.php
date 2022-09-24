<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerEmployee extends ControllerBase {

	public $path = '';
	public $path1 = '';
    public function __construct() {
        parent::__construct();
        $this->load->model('modelEmployee');
        $this->load->model('modelDesignations');
        $this->load->model('modelDepartments');
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
		$company = $this->input->get('company');
		$name    = $this->input->get('name');
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/jquery-ui/jquery-ui.min.js') . END_SCRIPT; 
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/employee/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$mobile      = $this->input->get('mobile');
		
		$filter['mobile'] = $mobile;
		$filter['comp'] = '!=1';
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 20;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelEmployee->employeeLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."employee/lists/";
		
		$page['search_url']= '?mobile='.$mobile.'&company='.$company;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 		
		$data['mobile']= $mobile; 		
		$data['company']= $company; 		
		$data['name']= $name; 		
		$data['isAdmin']= $isAdmin; 		
		
        
        $data['nav'] = array('1'=>'setting','2'=>'list','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(['ADMIN','MD','SUE','PRO','PUR','STOR','RM','STVISIT']);
		$data['auth'] = $this->authentication->check_auth(array('ADMIN','MD','SUE'));
		if( $admin){
			$data['main_content'] = 'employee/list';
		}
		//$data['auth'] =$admin;
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$this->load->view(THEMES, $data);
    }

    public function add() {       
        extract($this->authentication->user_groups_auth);
       
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/jquery-ui/jquery-ui.min.js') . END_SCRIPT; 
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/employee/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
        $data['nav'] = array('1'=>'setting','2'=>'list','3'=>'');
		$data['roles']= $this->modelEmployee->getEmployeeRole();
		$data['isAdmin']= $isAdmin;
		
		$data['departments'] =[];
		$data['designations']=[];
		
		$filter=[];
		$limit['data_per_page'] = 100;
		$limit['offset'] = 0;
		$departments = $this->modelDepartments->departmentsLists($filter,$limit);
		$data['departments'] = $departments['rows'];
		$designations = $this->modelDesignations->designationsLists($filter,$limit);
		$data['designations'] = $designations['rows'];
		
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SUE'));
		if($admin){
			$data['main_content'] = 'employee/add';
		}
		
        $this->load->view(THEMES, $data);
    }

    public function create() {
        
        $action = $this->input->post('submit');
		
        if ($action) {
            $form_data = $this->input->post('data');
			$roleid    = $this->input->post('roleid');            
			$form_data = array_map('trim',$form_data);
			//$form_data = $this->security->xss_clean($form_data);
			
			$form_data['password'] = md5($form_data['password']);			
            $new_id = $this->modelEmployee->createNew($form_data);
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,1);
			//echo $this->modelEmployee->path;
			if(!empty($roleid)){
				foreach($roleid as $k){
				    $many = array();
					$many['roleid']     = $k;
					$many['employeeid'] = $new_id;
					$this->modelCommon->insertTableData($many,'roles_employee');
				}
			}
			
			
            if ($new_id) {
               redirect('employee/lists/');
              
            }
        }
    }

    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/employee/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $row = $this->modelEmployee->getDetails($id);
            $data['details'] = $row;
			$data['details'][0]->password='';
        }
		$data['roles']     = $this->modelEmployee->getEmployeeRole();
		$data['selected'] =[];
		$roles = $this->modelCommon->getTableData('roles_employee',array('employeeid'=>$id));
		
		if(!empty($roles)){
			foreach($roles as $k){
				$role[] = $k->roleid;
			}
			$data['selected'] =$role;
		}
		
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>1]);
        $data['path'] = $this->path1;
		
		$data['isAdmin']= $isAdmin;
		
		$data['departments'] =[];
		$data['designations']=[];
		
		$filter = [];
		$limit['data_per_page'] = 100;
		$limit['offset'] = 0;
		$departments = $this->modelDepartments->departmentsLists($filter,$limit);
		$data['departments'] = $departments['rows'];
		$designations = $this->modelDesignations->designationsLists($filter,$limit);
		$data['designations'] = $designations['rows'];
	
		
		$data['nav'] = array('1'=>'setting','2'=>'list','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','SUE'));
		if( $admin){
			$data['main_content'] = 'employee/edit';
		}
		
        $this->load->view(THEMES, $data);
    }

    public function update() {
        $action = $this->input->post('update');
        if ($action) {
            $form_data = $this->input->post('data');
			$roleid     = $this->input->post('roleid'); 
			$form_data = array_map('trim',$form_data);
			
			if($form_data['password'])
				$form_data['password'] = md5($form_data['password']);
			else
				unset($form_data['password']);
			
			$form_data = $this->security->xss_clean($form_data);
			
            $id = $this->input->post('update_id');
			
            $this->modelEmployee->update($form_data, $id);
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($id,$this->path,1);
			
			if(!empty($roleid)){
				$this->modelCommon->deleteTableData('roles_employee',array('employeeid'=>$id));
				foreach($roleid as $k){
					$many = array();
					$many['roleid']     = $k;
					$many['employeeid'] = $id;
					$this->modelCommon->insertTableData($many,'roles_employee');
				}
			}
			
            redirect('employee/lists/');
        }
    }

    public function changeStatus() {
        if($this->authentication->check_auth(['ADMIN','MD','SUE'])){
			$id = (int) $this->input->post('id');
			$data['status'] = $this->input->post('status');
			$this->modelEmployee->update($data, $id);
		}		
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		echo json_encode($data);
    }
	public function delete($id){
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','MD','HR'));
		if( $admin){
			
			$this->modelCommon->deleteTableData('employee',array('id' =>$id));
			$this->modelCommon->deleteTableData('roles_employee',array('employeeid' =>$id));
			
			$files= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>1]);
			if(!empty($files)){
				foreach($files as $val){
					$file = $this->path.$val->image;
					@unlink($file);
					$this->modelCommon->deleteTableData('documents',['id'=>$val->id]);
				}
			}
				
			redirect('employee/lists/');
		}
		
		
	}
	
	public function checkMobile(){
		$flag['mobile'] ='';
		if ($this->input->is_ajax_request()) {            
            $mobile = $this->input->get('mobile');
            $id = $this->input->get('id');
            $condition['mobile'] = 	$mobile;	
           $result= $this->modelCommon->getTableData('employee',$condition);
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

	public function removeFile(){
		$id = $this->input->post('id');
		
		$flag['sms'] = 'You are not authorized';		
		//$admin =$this->authentication->check_auth(array('ADMIN','PM','COMP'));
		//if( $admin){
			$files= $this->modelCommon->getTableData('documents',['id'=>$id]);
			if(!empty($files)){
				$file = $files[0]->image;
				@unlink('uploads/documents/'.$file);
				$this->modelCommon->deleteTableData('documents',['id'=>$id]);
				$flag['sms'] = 'Success';
				$flag['code'] = '200';
			}else{
				$flag['sms'] = 'file not found';
				$flag['code'] = '404';
			}
		//}
		$flag['csrfName'] = $this->security->get_csrf_token_name();
		$flag['csrfHash'] = $this->security->get_csrf_hash();
		
		echo json_encode($flag);
	}
	
}
