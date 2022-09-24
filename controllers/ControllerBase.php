<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ControllerBase extends CI_Controller {

    public $root = '';
    public $path = '';
    public $temp = '';
	public $className ='';
    public $methodName ='';
	
	function __construct() {
        parent::__construct();		
		
		$this->root = $_SERVER['DOCUMENT_ROOT'].'/rahail/';	
		
		$this->className = str_replace('Controller','',$this->router->fetch_class());
		$this->methodName = str_replace('Controller','',$this->router->fetch_method());
		
		$host = $_SERVER['HTTP_HOST'];
		$host = str_replace('www.','',$host);
		if($host != 'apexdmit.com' && $host !='localhost'){
			//echo'Licence expired';//exit;
		}
		
    }

    public function sendmail($data){
		
		$to      = $data['to'];
		$subject = $data['subject'];
		$cc      = @$data['cc'];
		$bcc     = @$data['bcc'];
		$body    = $data['body'];
		$reply   = ($data['reply'])?$data['reply']:'sales@desifreshmart.com';
		
		$header  = "MIME-Version: 1.0\r\n"; // Defining the MIME version
		$header  = "Content-type: text/html; charset=utf-8"; // Defining the MIME version
		$header .= "From: Auto System <sales@desifreshmart.com> \r\n";
		$header .= "Reply-To: ".$reply."\r\n"; 		
		
		if($cc)
			$header .= "Cc: .$cc \r\n";
		if($bcc)
			$header .= "Bcc: .$bcc \r\n";
		
		$charset = 'UTF-8';
		$subject = "=?$charset?B?" . base64_encode($subject) . "?=\n";
	
		$body1 = $body;
		
		return mail($to, $subject, $body1, $header);
	}
	
	
	public function downloadAttachment($id) {
		
        // $this->common_library->_push_file($this->attachment_path . DIRECTORY_SEPARATOR . $file, $file);
        $filename = '';
        //$rows     = $this->modelCommon->getTableData('tutorials',array('id'=>$id));
		$file     ='';
		
		$filename = $this->root . 'application/videos/' . $file;
		
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');


        $file_extension = strtolower(substr(strrchr($filename, "."), 1)); //echo $file_extension;
		
        if ($filename == "") {
            echo "<html><title>Download Script</title><body>ERROR: pppdownload file NOT SPECIFIED. USE force-download.php?file=filepath</body></html>";
            exit;
        } elseif (!file_exists($filename)) {
            echo "<html><title>eLouai's Download Script</title><body>ERROR: File not found. USE force-download.php?file=filepath===</body></html>";
            exit;
        };
        switch ($file_extension) {
            case "pdf": $ctype = "application/pdf";
                break;
            case "exe": $ctype = "application/octet-stream";
                break;
            case "zip": $ctype = "application/zip";
                break;
            case "rar": $ctype = "application/rar";
                break;
            case "doc": $ctype = "application/msword";
                break;
            case "xls": $ctype = "application/vnd.ms-excel";
                break;
            case "ppt": $ctype = "application/vnd.ms-powerpoint";
                break;
            case "gif": $ctype = "image/gif";
                break;
            case "png": $ctype = "image/png";
                break;
            case "jpeg":$ctype = "image/jpg";
                break;
            case "jpg": $ctype = "image/jpg";
                break;
            default: $ctype = "application/force-download";
        }
		//echo filesize($filename);
		//exit();
		$content = file_get_contents($filename);
        header("Pragma: public"); // required
        header("Expires: 0");
		header('Accept-Ranges: bytes');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers 
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=" . basename($filename) . ";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filename));
        //echo $content;
        readfile("$filename");
        exit();
    }
	
	public function getPassword($length = 6) {
        $chars = '*#@abcdefghijklmnopqrstuvwxyz*#@ABCDEFGHIJKLMNOPQRSTUVWXYZ*#@0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }
}
