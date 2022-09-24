<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once 'ControllerBase.php';
class ControllerLogin extends ControllerBase {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelEmployee');
    }

   
    public function index($param= array()) {
        $is_logged_in = $this->session->userdata('is_logged_in');
        if ($is_logged_in == true) {
            redirect('dashboard');
        }
		if(empty($param))
			$param['msg']='';
		else
			$param=$param;

        $this->load->view('login_form', $param);
    }

    public function validateCredentials() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('dashboard');
        }
		$req_data = array();
		$req_data['username'] = $this->security->xss_clean(trim($this->input->post('user_name')));
	    $req_data['password'] = $this->security->xss_clean(trim($this->input->post('password')));
		
        $data = $this->modelEmployee->validateLoginCredential($req_data);
        
        if ($data != "") { // if the user's credentials validated...
            $user_data = "";
            $row = $data[0];
            if ($row->status == 3) {
                $msg['msg'] = "Your account has been locked. Please send email to system admin to unlock your account.";
                $this->index($msg);
            } else {
                $functions = $this->modelEmployee->getEmployeeRoleIDS($row->id);
                $group_ids = $roll_var= $controls="";
                if ($functions != '') {
                    foreach ($functions as $function) {
                        $group_ids .= $group_ids == "" ? $function->roleid : "," . $function->roleid;
                        $roll_var  .= $roll_var == "" ? $function->role : "," . $function->role;                        
                    }
                }
				
                $user_data = array(
                    'user_id' => $row->id,
                    'email' => $row->email,
                    'full_name' => $row->name,
                    'name' => $row->last_name,
                    'email' => $row->email,
                    'mobile' => $row->mobile,
                    'image' => $row->image,
                    'is_logged_in' => true,
                    'SoGroupID' => $group_ids,
                    'rollVAR' => $roll_var
                );
                $this->session->set_userdata($user_data);
             //   $this->model_common->insertTableData(array('ip' => REMOTE_ADDRESS, 'user' => $row->id));
                
                redirect('dashboard');
            }
        } else { // incorrect username or password
            $msg['msg'] = "Login failed. Please provide valid credentials.";
            $this->index($msg);
        }
    }

    public function logout($trac='') {
        $this->session->sess_destroy();
		$this->index();
    }

    public function resetPassword() {

        $username = $this->input->post('email');
		$action = $this->input->post('action');
		
		if($action){
			$condition = array('email' => trim($username) );
			$info = $this->modelCommon->getTableData('employee',$condition);
			
			if (empty($info)) {
			   
				 $msg['msg'] = "Invalid email or block your account";
				$this->index($msg);
				
			} else {
				$password = $this->getPassword();
				$info = $info[0];
				$data['password'] = md5(trim($password));
				$condition = array('email' => $username,'id'=>$info->id);
				$ass = $this->modelCommon->updateTableData('employee',$data,$condition);

				$info->password = $password;
				$info->username = $username;
				$id = $this->passwordEmail($info);
				$msg['msg'] = "Please check your email";
				$this->index($msg);
			}
		}
    }

    public function passwordEmail($data) {

		$http   =  isset($_SERVER['HTTPS']) ? "https://" : "http://";
		$server =  $http . $_SERVER['HTTP_HOST'].'/login';

        $message = 'Dear ' . $data->full_name . ', <br/>';
        $message .='You new password has been created . Please check...<br><br>';
        $message .= $server . '<br><br>';
        $message .='Username:' . $data->email . '<br>';
        $message .='Password:' . $data->password . '<br><br><br>';
        $message .= 'Regards<br>
                     Timfeo Team.<br/>
                     <br/>';

        $email_data['subject'] = 'Password has been changed';        
        $email_data['name'] = $data->full_name;
        $email_data['to'] = $data->email;
        $email_data['reply'] = 'info@timfeo.com';
        $email_data['bcc'] = '';
       
        $email_data['body'] = $message;
      //  return $this->sendmail($email_data);
    }
	
	function viewProfile($index=0){
		 $data['12']['union']="৮নং ভাগ্যকুল ইউনিয়ন পরিষদ";
		 $data['12']['district']="উপজেলা - শ্রীনগর, জেলা - মুন্সীগঞ্জ";
		 $data['12']['name']="কাজী মনোয়ার হোসেন (শাহাদাত)";
		 $data['12']['sig']="";
		 $data['12']['image']="images/union/12_chairman.png";
		 
		 $data['1']['union']="৮নং চিনিশপুর ইউনিয়ন পরিষদ";
		 $data['1']['district']="নরসিংদী সদর,নরসিংদী";
		 $data['1']['name']="মেহেদী হাসান তুহিন";
		 $data['1']['sig']="images/union/1_sign.png";
		 $data['1']['image']="images/union/1_chairman.png";
		 
		 $data['1']['union']="সোনারং টংগিবাডী ইউনিয়ন পরিষদ";
		 $data['1']['district']="উপজেলা - টংগিবাডী, জেলা - মুন্সীগঞ্জ";
		 $data['1']['name']="মোঃ বেলায়েত হোসেন লিটন ";
		 $data['1']['sig']="";
		 $data['1']['image']="images/union/liton.jpg";
		 
		 $data['row'] = $data[$index];
		 $this->load->view('profile', $data);
	}
}
