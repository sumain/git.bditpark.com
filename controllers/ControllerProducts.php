<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerProducts extends ControllerBase {

	
    public function __construct() {
        parent::__construct();
         $this->load->model('modelProducts');
         $this->load->model('modelSections');
         $this->load->model('modelUnits');
         $this->load->model('modelLocations');
         $this->load->model('modelRacks');
		
    }

    public function index() {
       $this->lists();
    }

    
	public function lists($pa=1) {
        extract($this->authentication->user_groups_auth);
       
		$filter = [];		
		
		
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/products/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
		
		$catid  = $this->input->get('catid');
		$rack = $this->input->get('rack');
		$name   = $this->input->get('name');
		$locaid = $this->input->get('locaid');
		
		$filter['categoryid'] = $catid;
		$filter['rackid'] = $rack;
		$filter['name'] = $name;
		$filter['locaid'] = $locaid;
		
		$filter = array_map('trim',$filter);
		
		$page['page'] = $pa;
		$page['data_per_page'] = 20;
		$page['offset'] = ((int)($page['page']-1)*$page['data_per_page']);	
		
		$result            = $this->modelProducts->productsLists($filter,$page);
		
		$data['result']    = $result['rows'];
		$page['total_row'] = $result['ttl_rows'];
		$page['page_url']  = base_url()."products/lists/";
		
		$page['search_url']= '?rack='.$rack.'&catid='.$catid.'&name='.$name.'&locaid='.$locaid;
		$data['pages']=$this->modelCommon->getPagination($page);
		
		$data['page']  = $pa;  
		$data['offset']= $page['offset']; 		
		$data['catid']= $catid; 		
		$data['rack']= $rack; 		
		$data['name']= $name; 		
		$data['isAdmin']= $isAdmin; 		
		$data['locaid']= $locaid; 		
		
        
        $data['nav'] = array('1'=>'inventory','2'=>'products','3'=>'');		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD'));
		if( $admin){
			$data['main_content'] = 'products/list';
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
		$data['category']= $this->modelSections->getAll();
		$data['locations'] = $this->modelLocations->getAll();
		$data['racks']= $this->modelRacks->getAll();
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
        $data['scripts'][] = SCRIPT . base_url('assets/js/products/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        
        $data['nav'] = array('1'=>'inventory','2'=>'products','3'=>'');
		
		$data['rows']= $this->modelSections->getAll();
		//$data['units']= $this->modelUnits->getAll();
		
		
		//printr($data['employees']);
		$data['isAdmin']= $isAdmin;
		
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD'));
		if($admin){
			$data['main_content'] = 'products/add';
		}
		$data['locations'] = $this->modelLocations->getAll();
		$data['racks']= $this->modelRacks->getAll();
        $this->load->view(THEMES, $data);
    }

    public function create() {
        
        $action = $this->input->post('submit');
		
        if ($action) {
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$new_id = $this->modelProducts->createNew($form_data);
			
			$log['userid']  = getName('user_id');
			$log['product'] = 0;
			$log['notes']   = 'New product('.$form_data['name'].') add into product table';
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
			
            if ($new_id) {
               redirect('products/lists/');
              
            }
        }
    }

    public function edit($id) {
		extract($this->authentication->user_groups_auth);
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/products/script.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/script.js') . END_SCRIPT;
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $row = $this->modelProducts->getDetails($id);
            $data['details'] = $row;			
        }
		
		$data['isAdmin']= $isAdmin;
		
		$data['nav'] = array('1'=>'inventory','2'=>'products','3'=>'');
		$data['rows']= $this->modelSections->getAll();
		$data['units']= $this->modelUnits->getAll();
		$data['subs']= $this->modelSections->getAllSubSuction($row[0]->categoryid);
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','CHA'));
		if( $admin){
			$data['main_content'] = 'products/edit';
		}
		$data['locations'] = $this->modelLocations->getAll();
		$data['racks']= $this->modelRacks->getAll();
        $this->load->view(THEMES, $data);
    }

    public function update() {
        $action = $this->input->post('update');
        if ($action) {
            $form_data = $this->input->post('data');
			$form_data = array_map('trim',$form_data);
			$form_data = $this->security->xss_clean($form_data);
			
            $id = $this->input->post('update_id');
			
            $this->modelProducts->update($form_data, $id);
			
			$log['userid']  = getName('user_id');
			$log['product'] = 0;
			$log['notes']   = 'update product('.$form_data['name'].')';
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
			
            redirect('products/lists/');
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
		$catid = $this->input->get('category');
		$subcat= $this->input->get('sub_category');

		$this->db->select("iproducts.id,iproducts.name,buy,iproducts.margin,iproducts.rate,description,units.name as unit,sections.name as category,subsections.name as subcat", FALSE);
        if($catid)
			$this->db->where('categoryid', $catid);
	    
		if($subcat)
			$this->db->where('subcat', $subcat);
        
		$this->db->like('iproducts.name', $term,'both');
		$this->db->join("units","units.id = iproducts.unitid",'left');
		$this->db->join("sections","sections.id = iproducts.categoryid",'left');
		$this->db->join("subsections","subsections.id = iproducts.subcat",'left');
        $result = $this->db->get('iproducts')->result();
        //echo $this->db->last_query();

		///$this->db->select("id,name", FALSE);
        ///$this->db->where('status', 1);
        ///$locations = $this->db->get('locations')->result();
		///
		///$this->db->select("id,name", FALSE);
        ///$this->db->where('status', 1);
        ///$racks = $this->db->get('racks')->result();
       

         $json = array();
		// $json['csrfName'] = $this->security->get_csrf_token_name();
        // $json['csrfHash'] = $this->security->get_csrf_hash();
          if(!empty($result)){
                foreach ($result as $i => $v) {
					//$this->db->select("(sum(stockin) - sum(stockout)) as total");
					//$this->db->where("projectid",$v->id);
					//$total = $this->db->get('inventory')->result();
		
                    $json[$i]['id'] = $v->id;
                    $json[$i]['value'] = $v->name.' ( '.$v->description.' )';
                    $json[$i]['name'] = $v->name;
                    $json[$i]['description'] = $v->description;
                    $json[$i]['unit'] = $v->unit;
                    $json[$i]['rate'] = $v->rate;
                    $json[$i]['buy'] = $v->buy;
                    $json[$i]['margin'] = $v->margin;
                    $json[$i]['category'] = $v->category;
                   // $json[$i]['locations'] = $locations;
                   // $json[$i]['racks'] = $racks;
                  if ($i == 25){
                      break;
                  }
              }
          }
		  
       echo json_encode($json);
	}

	public function saleAutocomplete(){
		$term  = $this->input->get('term');
		$catid = $this->input->get('category');
		$subcat= $this->input->get('sub_category');

		$this->db->select("iproducts.id,iproducts.name,description,units.name as unit,sections.name as category,subsections.name as subcat", FALSE);
        if($catid)
			$this->db->where('categoryid', $catid);
	    
		if($subcat)
			$this->db->where('subcat', $subcat);
        
		$this->db->like('iproducts.name', $term,'both');
		$this->db->join("iproducts","iproducts.id = inventory.productid",'left');
		$this->db->join("units","units.id = iproducts.unitid",'left');
		$this->db->join("sections","sections.id = iproducts.categoryid",'left');
		$this->db->join("subsections","subsections.id = iproducts.subcat",'left');
        $result = $this->db->get('iproducts')->result();
        $result = $this->db->get('inventory')->result();

        //echo $this->db->last_query();

		//$this->db->select("id,name", FALSE);
		//$this->db->select("id,name", FALSE);
        //$this->db->where('status', 1);
        //$locations = $this->db->get('locations')->result();
//
		//$this->db->select("id,name", FALSE);
        //$this->db->where('status', 1);
        //$racks = $this->db->get('racks')->result();
       

         $json = array();
          if(!empty($result)){
                foreach ($result as $i => $v) {
					//$this->db->select("(sum(stockin) - sum(stockout)) as total");
					//$this->db->where("projectid",$v->id);
					//$total = $this->db->get('inventory')->result();
		
                    $json[$i]['id'] = $v->id;
                    $json[$i]['value'] = $v->name.' ( '.$v->description.' )';
                    $json[$i]['name'] = $v->name;
                    $json[$i]['description'] = $v->description;
                    $json[$i]['unit'] = $v->unit;
                    //$json[$i]['unit'] = $v->unit;
                    $json[$i]['category'] = $v->category;
                    //$json[$i]['locations'] = $locations;
                    //$json[$i]['racks'] = $racks;
                  if ($i == 25){
                      break;
                  }
              }
          }
		  
       echo json_encode($json);
	}
	
	public function delete($id){
		
		$data['main_content'] = 'not_authorized';		
		$admin =$this->authentication->check_auth(array('ADMIN','RM','MD'));
		if( $admin){
			
			$pro=$this->modelCommon->getTableData('products',array('id' =>$id));
			$this->modelCommon->deleteTableData('products',array('id' =>$id));
		    
			$pro=$pro[0];
			$log['userid']  = getName('user_id');
			$log['product'] = 0;
			$log['notes']   = 'Product( '.$pro->name.' ) has deleted';
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
			
			redirect('products/lists/');
		}
		
		
	}
	public function getProductBySku(){
		$sku      = $this->input->post('sku');
		$counter  = $this->input->post('counter');
		$sale     = $this->input->post('sale');
		
		$this->db->select("iproducts.id,iproducts.name,buy,iproducts.margin,iproducts.rate,description,units.name as unit,sections.name as category,subsections.name as subcat", FALSE);
        //$this->db->like('iproducts.name', $term,'both');
		$this->db->where('sku', $sku);
		$this->db->join("units","units.id = iproducts.unitid",'left');
		$this->db->join("sections","sections.id = iproducts.categoryid",'left');
		$this->db->join("subsections","subsections.id = iproducts.subcat",'left');
        $result = $this->db->get('iproducts')->result();
        
        //echo $this->db->last_query();

		$cont_row='';
		if(!empty($result)){
			$row = $result[0];
			$counter++;
			if($sale==1){
				$cont_row .='<tr><td>'.$counter.'<input type="hidden" name="productid[]" value="'.$row->id.'"></td>';           	
				$cont_row .='<td>'.$row->name.'</td>';
				$cont_row .= '<td>'.$row->description.'</td>';
				$cont_row .='<td align="right"><input type="text" autocomplete="off"  id="rate'.$counter.'" name="unitprice[]" value="0" class="form-control text-right qty float_numbers"></td>';
				$cont_row .='<td align="right"><input type="text" autocomplete="off" id="qty'.$counter.'" name="qty[]" value="1" class="form-control text-right qty float_numbers"></td>';            
				$cont_row .='<td align="right"><input type="text" autocomplete="off" id="mar'.$counter.'" name="margin[]" value="1" class="form-control text-right float_numbers"></td>';            
				//$cont_row .='<td align="center">'.$row->unit.'</td>';
				$cont_row .='<td align="right"><input type="text" id="rowttl'.$counter.'" name="price[]" value="0" readonly class="form-control text-right float_numbers"></td>';            
				$cont_row .='</tr>'; 
			}elseif($sale==2){
				$cont_row .= '<tr><td>'.$counter.'<input type="hidden" name="productid[]" value="'.$row->id.'"></td>';           	
				$cont_row .= '<td>'.$row->name.'</td>';
				$cont_row .= '<td>'.$row->description.'</td>';
				$cont_row .= '<td align="right"><input type="text" autocomplete="off"  id="rate'.$counter.'" name="unitprice[]" value="'.$row->rate.'" class="form-control text-right qty float_numbers"></td>';
				$cont_row .= '<td align="right"><input type="text" autocomplete="off" id="qty'.$counter.'" name="qty[]" value="1" class="form-control text-right qty float_numbers"></td>';            
				//$cont_row .= '<td align="center">'.$row->unit.'</td>';
				$cont_row .= '<td align="right"><input type="text" id="rowttl'.$counter.'" name="price[]" value="'.$row->rate.'" readonly class="form-control text-right float_numbers"></td>';            
				$cont_row .= '</tr>'; 
			}
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
        $data['rows'] = $cont_row;
        $data['counter'] = $counter;
		  
       echo json_encode($data);
	}

}
