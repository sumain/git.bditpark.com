<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerUnits extends ControllerBase {

    
	public function __construct() {
        parent::__construct();
        $this->load->model('modelUnits');		
    }

    public function index() {
        $this->lists();
    }

    public function lists($pa=1) {
        $filter=array();
        extract($this->authentication->user_groups_auth);
        
        $data['scripts'][] = SCRIPT . base_url('assets/js/units/list.js') . END_SCRIPT;
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
		
		$result            = $this->modelUnits->unitLists($filter,$page);
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."units/lists/";
		
		$page['search_url']= '?';
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']     = $pa;  
		$data['data']     = $page['data_per_page']; 		
        
        $data['nav'] = array('1'=>'setting','2'=>'units','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM','MD'));
		if( $admin){
			$data['main_content'] = 'hr/units/list';
		}else{
			$data['main_content'] = 'not_authorized';
		}
		$this->load->view(THEMES, $data);
    }

    public function add() {
       		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','SUP','PM','MD'));
		if($admin){
			$this->load->view('hr/units/model_add');
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
			
            $new_id = $this->modelUnits->createNew($form_data);
            if ($new_id) {
                redirect('units/lists/');
            }
        }
    }

    public function edit($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelUnits->getDetails($id);
        }
      
	  $admin =$this->authentication->check_auth(array('ADMIN','CHA','SUP','PM','MD'));
		if( $admin){
			 $this->load->view('hr/units/model_edit', $data);
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
			
            $this->modelUnits->update($form_data, $id);

            redirect('units/lists/');
        }
    }

    public function delete($id){
		
		
		$admin =$this->authentication->check_auth(array('ADMIN','CHA','PM','MD'));
		if( $admin){
			$this->modelCommon->deleteTableData('units',array('id' =>$id));
			redirect('units/lists/');
		}else{
			$data['main_content'] = 'not_authorized';
		}
		
		
	}
	public function deleteMultiple() {
		$admin =$this->authentication->check_auth(array('ADMIN','CHA','PM','MD'));
		if( $admin){
			$newsid = $this->input->post('newsid');
			if(!empty($newsid)){
				foreach($newsid as $id){
					$this->modelCommon->deleteTableData('units',array('id' =>$id));
				}
			}
			redirect('units/lists/');
		}else{
			$data['main_content'] = 'not_authorized';
		}
	}

}
