<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';

class ControllerStoreIn extends ControllerBase {

	public $path = '';
	public $path1 = '';
	public $userid = '';
	
    public function __construct() {
        parent::__construct();
		$this->userid = getName('user_id');
        $this->load->model('modelStoreIn');
        $this->load->model('modelPurchases');
        $this->load->model('modelSuppliers');
        $this->load->model('modelUnits');
        $this->load->model('modelSections');
        $this->path = $this->root.'uploads/';
		$this->path = $this->root.'uploads/documents/';
		$this->path1 = 'uploads/documents/';
        @mkdir('uploads');
        @mkdir('uploads/documents/');
		
    }

    public function index() {
        $this->lists();
    }

    public function lists($pa = 1) {


        $fromdate = $this->input->get('fromdate');
        $todate = $this->input->get('todate');
        $supplier = $this->input->get('supplier');
        $order = $this->input->get('order');

        $filter['fromdate'] = $fromdate;
        $filter['todate'] = $todate;
        $filter['purchase.supplierid'] = $supplier;
        $filter['store_in.receipt'] = $order;
        $filter = array_map('trim', $filter);

        $page['page'] = $pa;
        $page['data_per_page'] = 50;
        $page['offset'] = ((int) ($page['page'] - 1) * $page['data_per_page']);


        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/store_in/script.js') . END_SCRIPT;

        $result = $this->modelStoreIn->lists($filter, $page);
		
        $data['result'] = $result['rows'];
        $page['total_row'] = $result['ttl_rows'];
        $page['page_url'] = base_url() . "storeIn/lists/";
		
        $page['search_url'] = '?supplier=' . $supplier . '&fromdate=' . $fromdate . '&todate=' . $todate . '&order=' . $order;
        $data['pages'] = $this->modelCommon->getPagination($page);

        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['supplier'] = $supplier;
        $data['suppliers'] = $this->modelSuppliers->getAll(1);
        $data['page'] = $pa;
        $data['data'] = $page['data_per_page'];
        $data['order'] = $order;


        $data['nav'] = array('1' => 'inventory', '2' => 'storeIn', '3' => '');

        $data['main_content'] = 'not_authorized';
        $admin = $this->authentication->check_auth(array('ADMIN','CHA','RM','MD'));
        if ( $admin) {
            $data['main_content'] = 'store_in/list';
        }
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','RM','MD'));
        $data['userid'] = $this->userid;
		$this->load->view(THEMES, $data);
    }

    public function add() {

        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/jquery-ui/jquery-ui.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/store_in/script.js') . END_SCRIPT;

        $data['suppliers'] = $this->modelSuppliers->getAll(1);
        $data['nav'] = array('1' => 'inventory', '2' => 'storeIn', '3' => '');

        $data['main_content'] = 'not_authorized';
        $admin = $this->authentication->check_auth(array('ADMIN','PM','RM','MD'));
        if ($admin) {
            $data['main_content'] = 'store_in/add';
        }

        $this->load->view(THEMES, $data);
    }

    public function create() {

        $action = $this->input->post('action');
        $userid = $this->userid;
        $date = date("Y-m-d H:i:s");
       
        if ($action) {
          
            $product   = $this->input->post('product'); 
            $productid = $this->input->post('productid'); 
            $received  = $this->input->post('received'); 
            $date1     = $this->input->post('date'); 
            $orderid   = $this->input->post('orderid'); 
           /*
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,4);
			
            $supplier = $data['supplierid'];
            $project  = $data['projectid'];
            $or_date  = $data['date'];
            
            $particular = "Purchase products, Order No. " . $data['receipt'];
            */
			$data =[];
			$data['userid']= $userid;
			$data['purchaseid']=$orderid;
			$data['receipt']=$product;
			$data['date']=$date1;
			$data['created_on']=$date;
			$data['updated_on']=$date;
			$storeid = $this->modelCommon->insertTableData($data,'store_in');

            foreach ($received as $k => $val) {
                $pid = $productid[$k];
           
                if ($val) {
					$data = $invt = $p = array();
					
					$data =[];
					$data['storeid']= $storeid;
					$data['purchase']=$orderid;
					$data['productid']=$pid;
					$data['qty']=$val;
					
					$this->modelCommon->insertTableData($data,'store_in_products');
					
                    $invt['userid'] = $userid;
                    $invt['productid'] = $pid;
                    $p['received']=$invt['stockin'] = $val;
                    $invt['stockout'] = 0;
                    $invt['date'] = $date1;
                    $invt['ext'] = 2;
                    $invt['created_on'] = $date;
                    $invt['updated_on'] = $date;
                    $this->modelCommon->insertTableData($invt,'inventory');
                    $condi=['productid'=>$pid,'orderid'=>$orderid];
					$this->modelCommon->updateTableData( 'purchase_products',$p,$condi);
                    
					$log['userid']  = $userid;
					$log['orderid'] = 0;
					$log['product'] = $pid;
					$log['notes']   = 'Product add into Inventory Quantity:"('.$val.')", Purchase Order No.'.$product;
					$log['created_on']= dates();
					$this->modelCommon->insertTableData($log,'logs');
                }
            }
			
			
			
           redirect('storeIn/details/'.$storeid);
            
        }
    }
	
	
	
    public function details($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelStoreIn->getDetails($id);
			
			
        }
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/store_in/script.js') . END_SCRIPT;

        $data['nav'] = array('1' => 'inventory', '2' => 'storein', '3' => '');
		//$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
       
		$data['path'] = $this->path;
		$data['main_content'] = 'store_in/detail';
		$this->load->view(THEMES, $data);
    }
	
	
	public function uploadDoc($id=0) {
        $id = intval($id);
        $data['details'] = array();
        $data['id'] = $id;
        if ($id) {
            $data['details'] = $this->modelPurchases->getDetails($id);
        }
		$action = $this->input->post('submit');
		$new_id = $this->input->post('id');
		if($action){
			
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,4);
			redirect('purchase/');
		}
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buys/script.js') . END_SCRIPT;

        $data['nav'] = array('1' => 'inventory', '2' => 'purchase', '3' => '');
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
        $this->load->view('purchase/purchase_upload', $data);
    }

    

	
	public function download($id){
		$this->downloadAttachment($id);
	}
	
	public function getEmployeeBalance(){
		$userid =$this->userid;
		
        $this->db->select("sum(credit) - sum(debit) as total", FALSE);
        $this->db->where("empid",$userid);
		$result = $this->db->get('emp_ledger')->result();
		//echo $this->db->last_query();
		$balance = number_format((int)$result[0]->total,2);
		$result['balance'] = $balance.'&nbsp; BDT';
       echo json_encode($result);
	}
	
	
	public function deletePurchase($id){
		
		$purchases = $this->modelPurchases->getDetails($id);		
		$row = $purchases[0];
		
		if($row->status == 2){
			$this->modelCommon->updateTableData('purchase',['status'=>'-1'],['id'=>$id]);
		}
		redirect('purchase/');
	}

    
	public function getProductBySku(){
		$sku      = $this->input->post('sku');
		$counter= $orderid=0;
		$this->db->select("purchase.*,qty as ordqty,received,sku,productid,iproducts.name as product,description,sections.name as category", FALSE);
        //$this->db->like('iproducts.name', $term,'both');
		$this->db->where('receipt', $sku);
		$this->db->join("purchase_products","purchase.id = purchase_products.orderid",'left');
		$this->db->join("iproducts","iproducts.id = purchase_products.productid",'left');
		$this->db->join("sections","sections.id = iproducts.categoryid",'left');
		$this->db->join("subsections","subsections.id = iproducts.subcat",'left');
        $result = $this->db->get('purchase')->result();
        
        //echo $this->db->last_query();

		$cont_row='<tr><td colspan="7">No products in this order.</td></tr>';
		if(!empty($result)){
			$cont_row='';
			$orderid=$result[0]->id;
			foreach($result as $k => $row){
				$counter++;
				
				$qty = ($row->ordqty - $row->received);
				$disabled = ($qty == false)?'disabled="1"':"";
				$cont_row .='<tr><td>'.$counter.'<input '.$disabled.' type="hidden" name="productid[]" value="'.$row->productid.'"></td>';           	
				$cont_row .='<td>'.$row->sku.'</td>';
				$cont_row .='<td>'.$row->product.'</td>';
				$cont_row .= '<td>'.$row->description.'</td>';
				$cont_row .= '<td class="text-center">'.$row->ordqty.'</td>';
				$cont_row .= '<td class="text-center">'.$row->received.'</td>';
				$cont_row .='<td align="right"><input '.$disabled.' type="text" autocomplete="off"  name="received[]" value="'.$qty.'" class="form-control text-right qty float_numbers"></td>';
				$cont_row .='</tr>'; 
				
			}
		}
		
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
        $data['rows'] = $cont_row;
        //$data['order'] = $result[0];
        $data['orderid'] = $orderid;
        $data['counter'] = $counter;
		  
       echo json_encode($data);
	}

}
