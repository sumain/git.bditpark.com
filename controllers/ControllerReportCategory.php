<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerReportCategory extends ControllerBase {

    
	public function __construct() {
        parent::__construct();
        $this->load->model('modelReportCategory');		
    }

    public function index() {
        $this->lists();
    }

    public function lists($pa=1) {
        
       
        $data['scripts'][] = SCRIPT . base_url('assets/js/report_cat/list.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$filter['fromdate']  = '';
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 50;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);
		
		$result            = $this->modelReportCategory->sectionsLists($filter,$page);
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."reportCategory/lists/";
		
		$page['search_url']= '?';
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page'] = $pa;  
		$data['data'] = $page['data_per_page']; 		
        
        $data['nav'] = array('1'=>'setting','2'=>'report_cat','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(['ADMIN','COMP','RM']);
		if( $admin){
			$data['main_content'] = 'report_cat/list';
		}else{
			$data['main_content'] = 'not_authorized';
		}
		$this->load->view(THEMES, $data);
    }

    public function add() {
       	$data['scripts'][] = SCRIPT . base_url('assets/js/report_cat/list.js') . END_SCRIPT;
		$admin =$this->authentication->check_auth(['ADMIN','COMP','RM']);
		if($admin){
			$this->load->view('report_cat/model_add');
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
			
            $new_id = $this->modelReportCategory->createNew($form_data);
            if ($new_id) {
                redirect('reportCategory/lists/');
            }
        }
    }

    public function edit($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelReportCategory->getDetails($id);
        }
      
	  $admin =$this->authentication->check_auth(['ADMIN','COMP','RM']);
		if( $admin){
			 $this->load->view('report_cat/model_edit', $data);
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
			
            $this->modelReportCategory->update($form_data, $id);

            redirect('reportCategory/lists/');
        }
    }

    public function delete($id){
		
		$admin =$this->authentication->check_auth(['ADMIN','COMP','RM']);
		if( $admin){
			$this->modelCommon->deleteTableData('report_cat',array('id' =>$id));
		}else{
			$this->load->view('not_authorized');
		}
		
		redirect('reportCategory/lists/');
	}
	public function deleteMultiple() {

		$admin =$this->authentication->check_auth(['ADMIN','COMP','RM']);
		if( $admin){
			$newsid = $this->input->post('newsid');
			if(!empty($newsid)){
				foreach($newsid as $id){
					$this->modelCommon->deleteTableData('report_cat',array('id' =>$id));
				}
			}
			redirect('reportCategory/lists/');
		}else{
			$this->load->view('not_authorized');
		}
		
		
	}
	

}
