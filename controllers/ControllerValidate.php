<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'ControllerBase.php';
class ControllerValidate extends ControllerBase {

    public function __construct() {
        parent::__construct();
        
    }

    public function index() {
        $this->delete();
    }

    public function delete() {
        
        $rowid = $this->input->post('rowid');
        $model = $this->input->post('model');
        $url   = $this->input->post('url');
		if(!empty($rowid)){
			foreach($rowid as $id){
				$row =$this->db->where('id',$id)->get($model)->result();
				$row =$row[0];
				$path = $this->path.'uploads/'.$model.'/';
				@unlink($path.$row->image);
				@unlink($path.'thumb/'.'m'.$row->image);
				@unlink($path.'thumb/'.$row->image);
				
				$this->db->where('id', $id);
				$this->db->delete($model);
				
			}
		}
		if($url)
			redirect($url);
		else
			redirect($model);
    }
	
	public function changeStatus() {
        if ($this->input->is_ajax_request()) { 
			$id = (int) $this->input->post('id');
			$data['status'] = $this->input->post('status');
			$model = $this->input->post('model');
			
			$this->modelCommon->updateTableData($model,$data,array('id'=>$id));		
			$data['csrfName'] = $this->security->get_csrf_token_name();
			$data['csrfHash'] = $this->security->get_csrf_hash();
			echo json_encode($data);
		}else{
			$data['error'] = 'Unauthorized request';
			echo json_encode($data);
		}
    }
	
	public function checkMobile(){
		$flag['mobile'] ='';
		if ($this->input->is_ajax_request()) {            
            $id     = $this->input->get('id');
            $model  = $this->input->get('model');
            $mobile = $this->input->get('mobile');
            $condition['mobile'] = 	$mobile;


			if (!empty($condition)) {
				foreach ($condition as $colName => $value) {
					if($value)
					   $this->db->where($colName, $value);
				}
			}
			if($id != ''){
				$this->db->where_not_in("id", array($id));
			}
			
		
			$this->db->select('*', FALSE);
			$result = $this->db->get($model)->result();
			//echo $this->db->last_query();
			
			if(!empty($result)){
				$flag['mobile'] =1;
			}
			$flag['csrfName'] = $this->security->get_csrf_token_name();
			$flag['csrfHash'] = $this->security->get_csrf_hash();
		   echo json_encode($flag);
            
        }else{
			$flag['error'] = 'Unauthorized request';
			echo json_encode($flag);
		}
	}
	
	public function checkEmail(){
		$flag['email'] ='';
		if ($this->input->is_ajax_request()) {            
            
			$id = $this->input->get('id');
            $email = $this->input->get('email');
            $model = $this->input->get('model');
            $condition['email'] = 	$email;	
			
			if (!empty($condition)) {
				foreach ($condition as $colName => $value) {
					if($value)
					   $this->db->where($colName, $value);
				}
			}
			if($id != ''){
				$this->db->where_not_in("id", array($id));
			}
			
		
			$this->db->select('*', FALSE);
			$result = $this->db->get($model)->result();
			if(!empty($result)){
				$flag['email'] =1;
			}
			$flag['csrfName'] = $this->security->get_csrf_token_name();
			$flag['csrfHash'] = $this->security->get_csrf_hash();
		    echo json_encode($flag);
        }else{
			$flag['error'] = 'Unauthorized request';
			echo json_encode($flag);
		}
	}

}
