<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerCustomers extends ControllerBase {

	
    public function __construct() {
        parent::__construct();
         $this->load->model('modelCustomers');
		
    }

    public function index() {
       $this->lists();
    }

    
	public function lists($pa=1) {
        extract($this->authentication->user_groups_auth);
       
		$filter = [];		
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/customers/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$catid  = $this->input->get('catid');
		$subcat = $this->input->get('subcat');
		$name   = $this->input->get('name');
		
		$filter['categoryid'] = $catid;
		$filter['subcat'] = $subcat;
		$filter['name'] = $name;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 20;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelCustomers->customersLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."customers/lists/";
		
		$page['search_url']= '?subcat='.$subcat.'&catid='.$catid.'&name='.$name;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 		
		$data['catid']= $catid; 		
		$data['subcats']= $subcat; 		
		$data['name']= $name; 		
		$data['isAdmin']= $isAdmin; 		
		
        
        $data['nav'] = array('1'=>'inventory','2'=>'customers','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','ACC','CHA','SUP','PM','MD'));
		if( $admin){
			$data['main_content'] = 'customers/list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$data['category']= $this->modelSections->getAll();
		$data['subcategory']= [];
		
		if($catid){
			$data['subcategory'] = $this->modelSections->getAllSubSuction($catid);
		}
		$this->load->view(THEMES, $data);
    }

    

    public function add() {       
        extract($this->authentication->user_groups_auth);
       
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/customers/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
        $data['nav'] = array('1'=>'setting','2'=>'customers','3'=>'');
		
		$data['rows']= $this->modelSections->getAll();
		$data['units']= $this->modelUnits->getAll();
		
		//printr($data['employees']);
		$data['isAdmin']= $isAdmin;
		
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','PM','SUP','CHA','MD'));
		if($admin){
			$data['main_content'] = 'customers/add';
		}
		
        $this->load->view(THEMES, $data);
    }

    public function create() {
        
        $action = $this->input->post('submit');
		
        if ($action) {
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$new_id = $this->modelCustomers->createNew($form_data);
			
            if ($new_id) {
               redirect('customers/lists/');
              
            }
        }
    }

    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/customers/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $row = $this->modelCustomers->getDetails($id);
            $data['details'] = $row;			
        }
		
		$data['isAdmin']= $isAdmin;
		
		$data['nav'] = array('1'=>'setting','2'=>'customers','3'=>'');
		$data['rows']= $this->modelSections->getAll();
		$data['units']= $this->modelUnits->getAll();
		$data['subs']= $this->modelSections->getAllSubSuction($row[0]->categoryid);
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','PM','SUP','CHA'));
		if( $admin){
			$data['main_content'] = 'customers/edit';
		}
		
        $this->load->view(THEMES, $data);
    }

    public function update() {
        $action = $this->input->post('update');
        if ($action) {
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			
            $id = $this->input->post('update_id');
			
            $this->modelCustomers->update($form_data, $id);
			
			
            redirect('customers/lists/');
        }
    }




    public function getSubCategory(){

        $id = $this->input->post('id');
        $data['rows'] = $this->modelSections->getAllSubSuction($id);

        $data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();

        echo json_encode($data);
    }

	public function getAutocomplete(){
		$term  = $this->input->get('term');

		$this->db->select("id,name,mobile", FALSE);
		$this->db->like('customers.name', $term,'both');
		$result = $this->db->get('customers')->result();
        //echo $this->db->last_query();
         $json = array();
          if(!empty($result)){
                foreach ($result as $i => $v) {
				
                    $json[$i]['id'] = $v->id;
                    $json[$i]['value'] = $v->name;
                    $json[$i]['name'] = $v->name;
                    $json[$i]['mobile'] = $v->mobile;
                  if ($i == 25){
                      break;
                  }
              }
          }
		  
       echo json_encode($json);
	}

	
	public function delete($id){
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','CHA','PM','MD'));
		if( $admin){
			
			$this->modelCommon->deleteTableData('customers',array('id' =>$id));
		
			redirect('customers/lists/');
		}
		
		
	}

}
