<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerLocations extends ControllerBase {

    
	public function __construct() {
        parent::__construct();
        $this->load->model('modelLocations');		
    }

    public function index() {
        $this->lists();
    }

    public function lists($pa=1) {
        $filter=array();
        extract($this->authentication->user_groups_auth);
        if($isCOM){
			$filter['company'] = getName('company');
		}
        $data['scripts'][] = SCRIPT . base_url('assets/js/locations/list.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		$company   = $this->input->get('company');
		if($isAdmin){				
			$filter['company']  = $company;
		}
		$filter['fromdate']  = '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);
		
		$result            = $this->modelLocations->locationsLists($filter,$page);
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."locations/lists/";
		
		$page['search_url']= '?';
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']     = $pa;  
		$data['data']     = $page['data_per_page']; 		
        
        $data['nav'] = array('1'=>'setting','2'=>'locations','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','HR','CHA'));
		if( $admin){
			$data['main_content'] = 'hr/locations/list';
		}else{
			$data['main_content'] = 'not_authorized';
		}
		$this->load->view(THEMES, $data);
    }

    public function add() {
       		
		$admin =$this->authentication->check_auth(array('ADMIN','COMP','HR'));
		if($admin){
			$this->load->view('hr/locations/model_add');
		}else{
			$this->load->view('not_authorized');
		}
		
    }

    public function create() {
        
        $action = $this->input->post('submit');
        if ($action) {
            $form_data = $this->input->post('data');
            $form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
		
		    
            $new_id = $this->modelLocations->createNew($form_data);
            if ($new_id) {
                redirect('locations/lists/');
            }
        }
    }

    public function edit($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelLocations->getDetails($id);
        }
      
	  $admin =$this->authentication->check_auth(array('ADMIN','CHA','HR'));
		if( $admin){
			 $this->load->view('hr/locations/model_edit', $data);
		}else{
			$this->load->view('not_authorized');
		}
       
    }

    public function update() {
        $action = $this->input->post('update');
        if ($action) {
			$id = $this->security->xss_clean($this->input->post('update_id'));
            $form_data = $this->input->post('data');            
			$form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			
            $this->modelLocations->update($form_data, $id);

            redirect('locations/lists/');
        }
    }

    public function delete($id){
		
		
		$admin =$this->authentication->check_auth(array('ADMIN','CHA','HR'));
		if( $admin){
			$this->modelCommon->deleteTableData('locations',array('id' =>$id));
			redirect('locations/lists/');
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
					$this->modelCommon->deleteTableData('locations',array('id' =>$id));
				}
			}
			redirect('locations/lists/');
		}else{
			$data['main_content'] = 'not_authorized';
		}
	}

}
