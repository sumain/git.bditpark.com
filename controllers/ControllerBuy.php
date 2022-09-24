<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';

class ControllerBuy extends ControllerBase {

	public $path = '';
	public $path1 = '';
	public $userid = '';
	
    public function __construct() {
        parent::__construct();
		$this->userid = getName('user_id');
        $this->load->model('modelBuy');
        $this->load->model('modelUnits');
        $this->load->model('modelReportCategory');
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
        $category = $this->input->get('category');
        $order = $this->input->get('order');

        $filter['fromdate'] = $fromdate;
        $filter['todate'] = $todate;
        $filter['categoryid'] = $category;
        $filter['receipt'] = $order;
        $filter = array_map('trim', $filter);

        $page['page'] = $pa;
        $page['data_per_page'] = 50;
        $page['offset'] = ((int) ($page['page'] - 1) * $page['data_per_page']);


        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buy/script.js') . END_SCRIPT;

        $result = $this->modelBuy->expLists($filter, $page);
        $data['result'] = $result['rows'];
        $page['total_row'] = $result['ttl_rows'];
        $page['page_url'] = base_url() . "modelBuy/lists/";

        $page['search_url'] = '?category=' . $category . '&fromdate=' . $fromdate . '&todate=' . $todate . '&order=' . $order;
        $data['pages'] = $this->modelCommon->getPagination($page);

        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['category'] = $category;
        $data['categories'] = $this->modelReportCategory->getAll();
        $data['page'] = $pa;
        $data['data'] = $page['data_per_page'];
        $data['order'] = $order;


        $data['nav'] = array('1' => 'otherExp', '2' => 'buy', '3' => '');

        $data['main_content'] = 'not_authorized';
        $admin = $this->authentication->check_auth(array('ADMIN','OTRPUR','MD'));
        if ( $admin) {
            $data['main_content'] = 'buy/list';
        }
        $data['auth'] = $this->authentication->check_auth(['ADMIN','MD','OTRPUR']);
        $data['userid'] = $this->userid;
		$this->load->view(THEMES, $data);
    }

    public function add() {

        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/jquery-ui/jquery-ui.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buy/script.js') . END_SCRIPT;

        $data['categorys'] = $this->modelReportCategory->getAll();
     
        $data['nav'] = array('1' => 'otherExp', '2' => 'buy', '3' => '');

        $data['main_content'] = 'not_authorized';
        $admin = $this->authentication->check_auth(['ADMIN','OTRPUR','MD']);
        if ($admin) {
            $data['main_content'] = 'buy/buy_add';
        }

        $this->load->view(THEMES, $data);
    }

    public function create() {

        $action = $this->input->post('action');
        $userid = $this->userid;
        $date = date("Y-m-d H:i:s");
        
        if ($action) {
           
            //..printr($this->input->post());
			//exit;
            $catid = $this->input->post('catid');
            $qty   = $this->input->post('qty'); 
            $items = $this->input->post('items');
            $rate  = $this->input->post('rate'); 

            $paid = trim($this->input->post('paid'));
            $due = trim($this->input->post('due'));
            $total = $this->input->post('total');
            $disc = $this->input->post('discount');

            $data = array();
            $data = $this->input->post('buy');
            $data['userid']  = $userid;            
            $data['receipt'] = $this->modelBuy->getOrderNo();
            $data['total']= ($total-$disc);
            $data['discount'] = $disc;
            $data['paid'] = $paid;
            $data['due'] = $due;
			

 
            $new_id = $this->modelBuy->createNew($data);
			/*
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,4);
			
            $supplier = $data['supplierid'];
            $project  = $data['projectid'];
            $or_date  = $data['date'];
            
            $particular = "Purchase products, Order No. " . $data['receipt'];
            */

            foreach ($qty as $k => $val) {
                $pid = $items[$k];
                $data = $invt = array();
                $p = array();
                if ($val) {
                    $data['orderid']= $new_id;
                    $data['product']= $pid;
                    $data['qty']    = $val;
                    $data['catid']  = $catid[$k];
                    $data['rate']   = $rate[$k];
                    $data['total']  = round(($val * $rate[$k]),2);                    
                    $did = $this->modelCommon->insertTableData($data, 'buy_products');

                }
            }
//$appstatus
				$log['userid']  = $userid;
				$log['orderid'] = 0;
				$log['product'] = 0;
				$log['notes']   = 'Create Other purchase ';
				$log['created_on']= dates();
				$this->modelCommon->insertTableData($log,'logs');
         
            if ($new_id) {
                redirect('buy');
            }
        }
    }
	
	
    
    public function details($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelBuy->getDetails($id);
			
			
        }
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buy/script.js') . END_SCRIPT;

        $data['nav'] = array('1' => 'otherExp', '2' => 'buy', '3' => '');
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
       // $this->load->view('sales/detail_pr', $data);
		//printr($data);
		$data['path'] = $this->path;
		$data['main_content'] = 'buy/detail_pr';
		$this->load->view(THEMES, $data);
    }
	
	public function getProductBySku(){
		$sku      = $this->input->post('sku');
		$counter  = $this->input->post('counter');
		$sale     = 1;//$this->input->post('sale');
		
		$this->db->select("iproducts.id,iproducts.name,buy,iproducts.margin,iproducts.rate,description,units.name as unit,sections.name as category,subsections.name as subcat", FALSE);
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
				$cont_row .='<td align="right"><input type="text" autocomplete="off"  id="rate'.$counter.'" name="unitprice[]" value="'.$row->buy.'" class="form-control text-right qty float_numbers"></td>';
				$cont_row .='<td align="right"><input type="text" autocomplete="off" id="qty'.$counter.'" name="qty[]" value="1" class="form-control text-right qty float_numbers"></td>'; 
				$cont_row .='<td align="right"><input type="text" id="rowttl'.$counter.'" name="price[]" value="'.$row->buy.'" readonly class="form-control text-right float_numbers"></td>';            
				$cont_row .='</tr>'; 
			}
		}
		$data['csrfName'] = $this->security->get_csrf_token_name();
        $data['csrfHash'] = $this->security->get_csrf_hash();
        $data['rows'] = $cont_row;
        $data['counter'] = $counter;
		  
       echo json_encode($data);
	}
	
	
}
