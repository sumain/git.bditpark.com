<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerSections extends ControllerBase {

    
	public function __construct() {
        parent::__construct();
        $this->load->model('modelSections');		
    }

    public function index() {
        $this->lists();
    }

    public function lists($pa=1) {
        
       
        $data['scripts'][] = SCRIPT . base_url('assets/js/sections/list.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']  = '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);
		
		$result            = $this->modelSections->sectionsLists($filter,$page);
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."sections/lists/";
		
		$page['search_url']= '?';
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']     = $pa;  
		$data['data']     = $page['data_per_page']; 		
        
        $data['nav'] = array('1'=>'setting','2'=>'section','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			$data['main_content'] = 'sections/list';
		}else{
			$data['main_content'] = 'not_authorized';
		}
		$this->load->view(THEMES, $data);
    }

    public function add() {
       	$data['scripts'][] = SCRIPT . base_url('assets/js/sections/list.js') . END_SCRIPT;
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if($admin){
			$this->load->view('sections/model_add');
		}else{
			$this->load->view('not_authorized');
		}
		
    }

    public function create() {
        
        $action = $this->input->post('submit');
        if ($action) {
			$form_data = $this->input->post('data');
            $category = $this->input->post('category');
			if(isset($category)){
				$form_data['category'] = implode(',',$category);
			}
			
            $new_id = $this->modelSections->createNew($form_data);
            if ($new_id) {
                redirect('sections/lists/');
            }
        }
    }

    public function edit($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelSections->getDetails($id);
        }
      
	  $admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			 $this->load->view('sections/model_edit', $data);
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
			
            $this->modelSections->update($form_data, $id);

            redirect('sections/lists/');
        }
    }

    public function delete($id){
		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			$this->modelCommon->deleteTableData('sections',array('id' =>$id));
		}else{
			$this->load->view('not_authorized');
		}
		
		redirect('sections/lists/');
	}
	public function deleteMultiple() {

		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			$newsid = $this->input->post('newsid');
			if(!empty($newsid)){
				foreach($newsid as $id){
					$this->modelCommon->deleteTableData('sections',array('id' =>$id));
				}
			}
			redirect('sections/lists/');
		}else{
			$this->load->view('not_authorized');
		}
		
		
	}
	
	public function subSection($pa=1) {
        
       
        $data['scripts'][] = SCRIPT . base_url('assets/js/sections/list.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$category   = $this->input->get('category');
		
		$filter['sectionid']  = $category;
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 25;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);
		
		$result            = $this->modelSections->subSectionLists($filter,$page);
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."sections/subSection/";
		
		$page['search_url']= '?category='.$category;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']     = $pa;  
		$data['data']     = $page['data_per_page']; 
		$data['categorys'] = $this->modelSections->getAll();		
		$data['category'] = $category;		
        
        $data['nav'] = array('1'=>'setting','2'=>'subsection','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			$data['main_content'] = 'sections/sub_list';
		}else{
			$data['main_content'] = 'not_authorized';
		}
		$this->load->view(THEMES, $data);
    }

    public function addSubSection() {
       		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if($admin){
			$data['categorys'] = $this->modelSections->getAll();
			$this->load->view('sections/sub_model_add',$data);
		}else{
			$this->load->view('not_authorized');
		}
		
    }

    public function createSubSection() {
        
        $action = $this->input->post('submit');
        if ($action) {
            $form_data = $this->input->post('data');
            $form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
		    
            $new_id = $this->modelSections->createNewSubSuction($form_data);
            if ($new_id) {
                redirect('sections/subSection/');
              
            }
        }
    }

    public function editSubSection($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelSections->getDetailSubSuction($id);
        }
      
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			 $data['categorys'] = $this->modelSections->getAll();
			 $this->load->view('sections/sub_model_edit', $data);
		}else{
			$this->load->view('not_authorized');
		}
       
    }

    public function updateSubSection() {
        $action = $this->input->post('update');
        if ($action) {
			$id = $this->security->xss_clean($this->input->post('update_id'));
            $form_data = $this->input->post('data');            
			$form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			
            $this->modelSections->updateSubSection($form_data, $id);

            redirect('sections/subSection/');
        }
    }

    public function deleteSubSection($id){
		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			$this->modelCommon->deleteTableData('subsections',array('id' =>$id));
			redirect('sections/subSection/');
		}else{
			$this->load->view('not_authorized');
		}
		
	}
	
	public function subDeleteMultiple() {

		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','PM'));
		if( $admin){
			$newsid = $this->input->post('newsid');
			if(!empty($newsid)){
				foreach($newsid as $id){
					$this->modelCommon->deleteTableData('subsections',array('id' =>$id));
				}
			}
			redirect('sections/subSection/');
		}else{
			$this->load->view('not_authorized');
		}
		
	}

}
