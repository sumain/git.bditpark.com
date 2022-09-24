<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';

class ControllerSales extends ControllerBase {

	public $path = '';
	public $path1 = '';
	public $userid = '';
	
    public function __construct() {
        parent::__construct();
		$this->userid = getName('user_id');
        $this->load->model('modelSales');
        $this->load->model('modelCustomers');
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
        $customer = $this->input->get('customer');
        $order = $this->input->get('order');

        $filter['fromdate'] = $fromdate;
        $filter['todate'] = $todate;
        $filter['customerid'] = $customer;
        $filter['receipt'] = $order;
        $filter = array_map('trim', $filter);

        $page['page'] = $pa;
        $page['data_per_page'] = 50;
        $page['offset'] = ((int) ($page['page'] - 1) * $page['data_per_page']);


        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/sales/script.js?v=1.1') . END_SCRIPT;

        $result = $this->modelSales->salesLists($filter, $page);
        $data['result'] = $result['rows'];
        $page['total_row'] = $result['ttl_rows'];
        $page['page_url'] = base_url() . "sales/lists/";

        $page['search_url'] = '?customer=' . $customer . '&fromdate=' . $fromdate . '&todate=' . $todate . '&order=' . $order;
        $data['pages'] = $this->modelCommon->getPagination($page);

        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['customer'] = $customer;
        $data['customers'] = $this->modelCustomers->getAll();
        $data['page'] = $pa;
        $data['data'] = $page['data_per_page'];
        $data['order'] = $order;


        $data['nav'] = array('1' => 'inventory', '2' => 'sales', '3' => '');

        $data['main_content'] = 'not_authorized';
        $admin = $this->authentication->check_auth(array('ADMIN','RM','MD'));
        if ( $admin) {
            $data['main_content'] = 'sales/buy_list';
        }
        $data['auth'] = $this->authentication->check_auth(array('ADMIN','CHA','MD'));
        $data['userid'] = $this->userid;
		$this->load->view(THEMES, $data);
    }

    public function add() {

        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/jquery-ui/jquery-ui.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/sales/script.js?v=1.1') . END_SCRIPT;

        $data['categorys'] = $this->modelSections->getAll();
     
        $data['nav'] = array('1' => 'inventory', '2' => 'sales', '3' => '');

        $data['main_content'] = 'not_authorized';
        $admin = $this->authentication->check_auth(array('ADMIN','CHA','RM','MD'));
        if ($admin) {
            $data['main_content'] = 'sales/buy_add';
        }

        $this->load->view(THEMES, $data);
    }

    public function create() {

        $action = $this->input->post('action');
        $userid = $this->userid;
        $date = date("Y-m-d H:i:s");
        
        if ($action) {
            
            $custid   = $this->input->post('custid'); //
            $customer   = $this->input->post('customer'); //
            $mobile   = $this->input->post('mobile'); //
            if( (!empty($mobile) || !empty($customer)) && $custid ==''){
                $cut['mobile']= $mobile;
                $cut['name']  = $customer;
                $custid = $this->modelCommon->insertTableData($cut, 'customers');
            }

            $utprice   = $this->input->post('unitprice'); //
            $qty       = $this->input->post('qty'); //
            $productid = $this->input->post('productid'); //
            $location  = $this->input->post('location'); //
            $rack      = $this->input->post('rack'); //

            $paid = trim($this->input->post('paid'));
            $due = trim($this->input->post('due'));
            $total = $this->input->post('total');
            $disc = $this->input->post('discount');

            $data = array();
            $data = $this->input->post('buy');
            $data['userid']  = $userid;            
            $receipt = $this->modelSales->getOrderNo();
            $data['receipt'] = $receipt;
            $data['total']= ($total-$disc);
            $data['discount'] = $disc;
            $data['paid'] = $paid;
            $data['due'] = $due;
            $data['customerid'] = $custid;
			

 
            $new_id = $this->modelSales->createNew($data);
			/*
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,4);
			
            $supplier = $data['supplierid'];
            $project  = $data['projectid'];
            $or_date  = $data['date'];
            
            $particular = "Purchase products, Order No. " . $data['receipt'];
            */

            foreach ($qty as $k => $val) {
                $pid = $productid[$k];
                $data = $invt = array();
                $p = array();
                if ($val) {
                    $data['orderid'] = $new_id;
                    $data['productid'] = $pid;
                    $data['qty'] = $val;
                    $data['rate'] = $utprice[$k];
                    $data['total'] = round(($val * $utprice[$k]),2);                    
                    $did = $this->modelCommon->insertTableData($data, 'sales_products');

                    $invt['userid'] = $userid;
                    $invt['productid'] = $pid;
                    //$invt['locationid'] = 0;
                    //$invt['rackid'] = 0;
                    $invt['stockin'] = 0;
                    $invt['stockout'] = $val;
                    $invt['date'] = $date;
                    $invt['ext'] = 3;
                    $invt['created_on'] = $date;
                    $invt['updated_on'] = $date;
                    $this->modelCommon->insertTableData($invt, 'inventory');
                }
            }

			$log['userid']  = $userid;
			$log['orderid'] = 0;
			$log['product'] = 0;
			$log['notes']   = 'New sale has created Invoice No '.$receipt;
			$log['created_on']= dates();
			$this->modelCommon->insertTableData($log,'logs');
			
            if ($new_id) {
                redirect('sales/');
            }
        }
    }
	public function edit($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $rows = $this->modelSales->getDetails($id);
            $data['details'] =  $rows;
			$sup = ($rows[0]->sup)?$rows[0]->sup:"0";
        }
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/jquery-ui/jquery-ui.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/sales/script.js?v=1.1') . END_SCRIPT;

        $data['categorys'] = $this->modelSections->getAll();
        $data['suppliers'] = $this->modelSuppliers->getAll(1, $sup);
		/*
        $data['path']= $this->path1;
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
        */
        $data['nav'] = array('1' => 'inventory', '2' => 'sales', '3' => '');
       
	   $data['main_content'] = 'not_authorized';
	    $admin = $this->authentication->check_auth(array('ADMIN','CHA','MD'));
        if ($admin) {
           $data['main_content'] = 'sales/buy_edit';
        }
		$data['auth'] = $admin;
		
		$this->load->view(THEMES, $data);
    }
	
	public function update() {

        $action = $this->input->post('action');
        $userid = $this->userid;
        $date = date("Y-m-d H:i:s");
        $admin = $this->authentication->check_auth(array('ADMIN','CHA','MD'));
        if ($action) {

            $utprice    = $this->input->post('unitprice'); //
            $qty        = $this->input->post('qty'); //
            $total_price= $this->input->post('price'); //            
            $productid  = $this->input->post('productid'); //
            

            $paid = trim($this->input->post('paid'));
            $due = trim($this->input->post('due'));
            $total = $this->input->post('total');
            $disc = $this->input->post('discount');
            $rowid = $this->input->post('rowid');
		
            $data = array();
            $new_id = $this->input->post('update_id');
            $data = $this->input->post('buy');
            $data['total']= $total;
            $data['discount'] = $disc;
            $data['paid'] = $paid;
            $data['due'] = $due;
            $data['transportid'] = $this->input->post('transports');
            $expense = $this->input->post('expense');
            $data['expense'] = $expense;
			
            if( $admin){
				$data['status'] = 1;
				$data['approved'] = $userid ;
			}
				$this->modelSales->update($data,$new_id);
				
				$_FILES["userfile"] = $_FILES["docs"];
				if(empty($_FILES["userfile"]['name'])){
					$this->modelCommon->multiFilesUpload($new_id,$this->path,4);
				}
				
				$supplier = $data['supplierid'];
				$project  = $data['projectid'];
				$or_date  = $data['date'];
				
				
				foreach ($qty as $k => $val) {
					$rid = $rowid[$k];
					$pid = $productid[$k];
					$data = array();
					$p = array();
					if ($val) {
						$data['orderid'] = $new_id;
						$data['productid'] = $pid;
						$data['qty'] = $val;
						$data['rate'] = $utprice[$k];
						$data['total'] = round(($val * $utprice[$k]),2);
						
						$did = $this->modelCommon->updateTableData('sales_products',$data,['id'=>$rid] );
					}					
				}
				
            if( $admin){
				 $rows = $this->modelSales->getDetails($new_id);
				 $row = $rows[0];
				 $particular = "Purchase products, Order No. " . $row->receipt;
				// for accountability			 
				$data = array();
				$data['supplierid'] = $row->supplierid;
				$data['particular'] = $particular;
				$data['debit'] = ($row->total - $row->discount);
				$data['credit'] = 0;
				$data['date'] = $row->date;
				$data['created_on'] = $date;
				$data['empid']      = $userid;
				$this->modelCommon->insertTableData($data, 'supplier_ledger');

				if ($row->paid >= 1) {
					$data = array();
					$data['empid']      = $userid;
					$data['supplierid'] = $row->supplierid;;
					$data['debit'] = 0;
					$data['credit'] = $row->paid;
					$data['particular'] = $particular;
					$data['date'] = $row->date;
					$data['created_on'] = $date;

					$this->modelCommon->insertTableData($data, 'supplier_ledger');
										
					$data          = [];
					$data['empid'] = $userid;
					$data['rowid'] = $new_id;
					$data['debit'] = ($row->paid + (int)$expense);
					$data['particular'] = $particular ;
					$data['credit'] = 0;
					$data['date']   = $row->date;
					$data['type'] = 2;				
					$data['ext'] = 4;
					$data['created_on'] = $date;
					$data['updated_on'] = $date;					
					$this->modelCommon->insertTableData($data,'accounting');
				}
				
				// for adjustment
				if( $row->adjustment ==1){
					$data          = [];
					$data['userid'] = $userid;
					$data['empid'] = $row->userid;
					$data['debit'] = ($row->paid + (int)$expense);
					$data['particular'] = $particular ;
					$data['credit'] = 0;
					$data['date']   = $date;
					$data['created_on'] = $date;				
					$this->modelCommon->insertTableData($data,'emp_ledger');
				}
            }
            // for accountability	

            if ($new_id) {
                redirect('sales/');
            }
        }
    }

    public function dashboardDetails($id) {
		$id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelSales->getDetails($id);
			
			
        }
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buys/script.js?v=1.1') . END_SCRIPT;

        $data['nav'] = array('1' => 'inventory', '2' => 'sales', '3' => '');
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
        $this->load->view('sales/sales_details', $data);
	}
    public function details($id) {
        $id = intval($id);
        $data['details'] = array();
        if ($id) {
            $data['details'] = $this->modelSales->getDetails($id);
			
			
        }
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buys/script.js?v=1.1') . END_SCRIPT;

        $data['nav'] = array('1' => 'inventory', '2' => 'sales', '3' => '');
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
       // $this->load->view('sales/detail_pr', $data);
		//printr($data);
		$data['path'] = $this->path;
		$data['main_content'] = 'sales/detail_pr';
		$this->load->view(THEMES, $data);
    }
	
	
	public function uploadDoc($id=0) {
        $id = intval($id);
        $data['details'] = array();
        $data['id'] = $id;
        if ($id) {
            $data['details'] = $this->modelSales->getDetails($id);
        }
		$action = $this->input->post('submit');
		$new_id = $this->input->post('id');
		if($action){
			
			$_FILES["userfile"] = $_FILES["docs"];
			$this->modelCommon->multiFilesUpload($new_id,$this->path,4);
			redirect('sales/');
		}
		$data['scripts'][] = SCRIPT . base_url('assets/global/plugins/bootstrap-fileinput/bootstrap-filestyle.min.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') . END_SCRIPT;
        $data['scripts'][] = SCRIPT . base_url('assets/js/buys/script.js?v=1.1') . END_SCRIPT;

        $data['nav'] = array('1' => 'inventory', '2' => 'sales', '3' => '');
		$data['files']= $this->modelCommon->getTableData('documents',['refid'=>$id,'ref'=>4]);
        $this->load->view('sales/sales_upload', $data);
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
		
		$saless = $this->modelSales->getDetails($id);		
		$row = $saless[0];
		
		if($row->status == 2){
			$this->modelCommon->updateTableData('sales',['status'=>'-1'],['id'=>$id]);
		}
		redirect('sales/');
	}

}
