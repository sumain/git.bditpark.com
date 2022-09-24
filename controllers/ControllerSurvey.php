<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerSurvey extends ControllerBase {

	 public $userid ='';
    public function __construct() {
        parent::__construct();
        $this->load->model('modelSurvey');
		$this->userid = getName('user_id');
		
    }

    public function index() {
       $this->lists();
    }

    
	public function lists($pa=1) {
        extract($this->authentication->user_groups_auth);
        $userid = getName('user_id');
		$filter = [];		
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/survey/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$fromdate= $this->input->get('fromdate');
		$todate  = $this->input->get('todate');
		
		$filter['fromdate'] = $fromdate;
		$filter['todate'] = $todate;
		if($isSTVISIT){
			$filter['userid'] = $userid;
		}
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 20;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelSurvey->surveyLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."survey/lists/";
		
		$page['search_url']= '?';
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 		
		$data['isAdmin']= $isAdmin; 
        
        $data['nav'] = array('1'=>'inventory','2'=>'survey','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(['ADMIN','STVISIT','MD']);
		if( $admin){
			$data['main_content'] = 'survey/list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		
		$this->load->view(THEMES, $data);
    }

    

    public function add() {       
        extract($this->authentication->user_groups_auth);
       
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/survey/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
        $data['nav'] = array('1'=>'inventory','2'=>'survey','3'=>'');
		
		$data['isAdmin']= $isAdmin;
		$filter['fromdate'] = '';
		$filter['todate']   = '';
		$rows = $this->modelSurvey->surveyProducts($filter);
		$data['rows'] = $rows['rows'];
		//printr($data['rows']);
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','STVISIT','MD'));
		if($admin){
			$data['main_content'] = 'survey/add';
		}
		$this->load->view(THEMES, $data);
    }

    public function create() {
        
        $action = $this->input->post('action');
		
        if ($action) {
            $products = $this->input->post('product');
            $quantity = $this->input->post('qty');
            $previous = $this->input->post('previous');
			
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$form_data['userid'] = getName('user_id');
			
			$new_id = $this->modelSurvey->createNew($form_data);
			foreach ($quantity as $k => $val) {
                $pid = $products[$k];
                if ($val) {
					$data =  array();
                    $data['orderid'] = $new_id;
                    $data['productid'] = $pid;
                    $data['present_qty'] = $val;
                    $data['previous_qty'] = $previous[$k];
                    $this->modelCommon->insertTableData($data, 'survey_products');
                }
            }
			
				$log['userid']  = $this->userid;
				$log['orderid'] = 0;
				$log['product'] = 0;
				$log['notes']   = 'Create New Warehouse visited ';
				$log['created_on']= dates();
				$this->modelCommon->insertTableData($log,'logs');
				
            if ($new_id) {
               redirect('survey/lists/');
              
            }
        }
    }

    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/survey/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $row = $this->modelSurvey->getDetails($id);
            $data['details'] = $row;			
        }
		
		$data['isAdmin']= $isAdmin;
		
		$data['nav'] = array('1'=>'inventory','2'=>'survey','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','STVISIT','MD'));
		if( $admin){
			$data['main_content'] = 'survey/edit';
		}
		$this->load->view(THEMES, $data);
    }

    public function update() {
        $action = $this->input->post('action');
        if ($action) {
            $products = $this->input->post('product');
            $quantity = $this->input->post('qty');
            $previous = $this->input->post('previous');
			
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			
            $id = $this->input->post('update_id');
			
            $this->modelSurvey->update($form_data, $id);
			if(!empty($quantity)){
				$this->modelCommon->deleteTableData('survey_products',['orderid'=>$id]);
				foreach ($quantity as $k => $val) {
					$pid = $products[$k];
					if ($val) {
						$data =  array();
						$data['orderid'] = $id;
						$data['productid'] = $pid;
						$data['present_qty'] = $val;
						$data['previous_qty'] = $previous[$k];
						$this->modelCommon->insertTableData($data, 'survey_products');
					}
				}
			}
			
				$log['userid']  = $this->userid;
				$log['orderid'] = 0;
				$log['product'] = 0;
				$log['notes']   = 'Update Warehouse visited ';
				$log['created_on']= dates();
				$this->modelCommon->insertTableData($log,'logs');
			
            redirect('survey/lists/');
        }
    }
	
	 public function details($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/survey/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $row = $this->modelSurvey->getDetails($id);
            $data['details'] = $row;			
        }
		
		$data['isAdmin']= $isAdmin;
		
		$data['nav'] = array('1'=>'inventory','2'=>'survey','3'=>'');
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','STVISIT','MD'));
		if( $admin){
			$data['main_content'] = 'survey/details';
		}
		$this->load->view(THEMES, $data);
    }
	
	public function getProductBySku(){
		$sku      = $this->input->post('sku');
		$counter  = $this->input->post('counter');
		$qty     = $this->input->post('qty');
		$qty     = ($qty)?$qty:1;
		$this->db->select("sku,iproducts.name,iproducts.rate, units.name AS unit,locations.name AS location,racks.name AS rack, productid,locationid,iproducts.rackid,SUM(stockin-stockout) AS remain", FALSE);
        $this->db->join("iproducts", "iproducts.id = inventory.productid", "left");
        $this->db->join("units", "units.id = iproducts.unitid", "left");
        $this->db->join("racks", "racks.id = iproducts.rackid", "left");
        $this->db->join("locations", "locations.id = iproducts.locaid", "left");
		$this->db->where('iproducts.sku', $sku);
        $this->db->group_by('iproducts.rackid');
        $this->db->group_by('iproducts.locaid');
        $this->db->group_by('productid');
        $this->db->order_by('iproducts.name', 'asc');
		
        $result = $this->db->get('inventory')->result();

		$cont_row='';
		if(!empty($result)){
			$val = $result[0];
			$counter++;
			
			$cont_row .='<tr style="color:">';                         
				$cont_row .='<td>'.$counter;			
				$cont_row .='<input type="hidden" name="product[]" value="'.$val->productid.'">';		
				$cont_row .='<input type="hidden" name="previous[]" value="'.$val->remain.'">';
				$cont_row .='</td>';			
				$cont_row .='<td>'.$val->sku.'</td>';
				$cont_row .='<td>'.$val->name.'</td>';
				$cont_row .='<td>'.$val->location.'</td>';
				$cont_row .='<td class="text-center"><input type="text" name="qty[]" value="'.$qty.'" class="form-control" placeholder="Actual Qty"></td>';				
			$cont_row .='</tr>';
		}else{
			
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
        $data['rows'] = $cont_row;
        $data['counter'] = $counter;
		  
       echo json_encode($data);
	}
	
	 public function delete($id){
		
		$admin =$this->authentication->check_auth(['ADMIN','MD','STVISIT']);
		if( $admin){
			$this->modelCommon->deleteTableData('survey',['id' =>$id]);
			$this->modelCommon->deleteTableData('survey_products',['orderid' =>$id]);
			
			
				$log['userid']  = $this->userid;
				$log['orderid'] = 0;
				$log['product'] = 0;
				$log['notes']   = 'Delete Warehouse visited ';
				$log['created_on']= dates();
				$this->modelCommon->insertTableData($log,'logs');
				
			redirect('survey/index/');
		}else{
			$this->load->view('not_authorized');
		}
		
	}

}
