<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerProfile extends ControllerBase {

    
	public function __construct() {
        parent::__construct();
       // $this->load->model('modelRacks');		
    }

    public function index() {
        $this->lists();
    }

    public function lists($pa=1) {
        $filter=array();
        extract($this->authentication->user_groups_auth);
        
        //$data['scripts'][] = SCRIPT . base_url('assets/js/profile/list.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		$company   = $this->input->get('company');
		
		$filter['fromdate']  = '';
		$filter = array_map('trim',$filter);
		
		
		$result            = $this->modelCommon->getTableData('profile',$filter);
		
		$data['result']    = $result;
		$page['total_row'] = 0;
		$page['page_url']  = base_url()."profile/lists/";
		
		
        $data['nav'] = array('1'=>'setting','2'=>'profile','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','HR','CHA'));
		if( $admin){
			$data['main_content'] = 'hr/profile/list';
		}else{
			$data['main_content'] = 'not_authorized';
		}
		$this->load->view(THEMES, $data);
    }

    public function add() {
       	
		$data['scripts'][] = SCRIPT . base_url('assets/js/profile/script.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') . END_SCRIPT;
		$admin =$this->authentication->check_auth(array('ADMIN','COMP','HR'));
		 $data['nav'] = array('1'=>'setting','2'=>'profile','3'=>'');
		if($admin){
			$data['main_content'] = 'hr/profile/add';
		}else{
			$data['main_content'] = 'not_authorized';
			
		}
		$this->load->view(THEMES, $data);
    }

    public function create() {
        
        $action = $this->input->post('submit');
        if ($action) {
            $form_data = $this->input->post('data');
            $form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			$path = $this->root.'images/';
			$image = time().'_img';
			$image = $this->modelCommon->uploadPicture($image,$path);
		
		    $form_data['image'] = $image;
            $new_id = $this->modelCommon->insertTableData($form_data,'profile');
            if ($new_id) {
                redirect('profile/lists/');
            }
        }
    }

    public function edit($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelCommon->getTableData('profile',['id'=>$id]);
        }
	  $data['scripts'][] = SCRIPT . base_url('assets/js/profile/script.js') . END_SCRIPT;
      $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') . END_SCRIPT;
	  $admin =$this->authentication->check_auth(['ADMIN','CHA','COM']);
		 $data['nav'] = array('1'=>'setting','2'=>'profile','3'=>'');
		if($admin){
			$data['main_content'] = 'hr/profile/edit';
		}else{
			$data['main_content'] = 'not_authorized';
			
		}
       $this->load->view(THEMES, $data);
    }

    public function update() {
        $action = $this->input->post('update');
        if ($action) {
			$id = $this->security->xss_clean($this->input->post('update_id'));
            $form_data = $this->input->post('data');            
			$form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			
			$path = $this->root.'images/';
			$image = time().'_img';
			$image = $this->modelCommon->uploadPicture($image,$path);
			if($image){
				$form_data['image'] = $image;
			}
		
            $this->modelCommon->updateTableData('profile',$form_data, ['id'=>$id]);
            //echo $this->db->last_query();
			//exit;
            redirect('profile/lists/');
        }
    }

    public function delete($id){
		
		
		$admin =$this->authentication->check_auth(array('ADMIN','CHA','HR'));
		if( $admin){
			$this->modelCommon->deleteTableData('racks',array('id' =>$id));
			redirect('profile/lists/');
		}else{
			$data['main_content'] = 'not_authorized';
		}
		
		
	}
	public function deleteMultiple() {
		$admin =$this->authentication->check_auth(array('ADMIN','CHA','HR'));
		if( $admin){
			$newsid = $this->input->post('newsid');
			if(!empty($newsid)){
				foreach($newsid as $id){
					$this->modelCommon->deleteTableData('racks',array('id' =>$id));
				}
			}
			redirect('profile/lists/');
		}else{
			$data['main_content'] = 'not_authorized';
		}
	}

}
