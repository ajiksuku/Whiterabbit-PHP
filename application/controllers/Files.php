<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files extends CI_Controller {

	    function __construct() {
        parent::__construct();

       
    }

    /*
    url - http://localhost/whiterabbit/index.php/Files/listing
    params - page,search
    method - POST
    */
	
	public function listing($currentpage='',$search='') {
        $files = array();
        $exclude_files = array(""); 
        $currentpage = $this->input->post('page');//get page from input
        $search = $this->input->post('search');//get search from input
        $handle = opendir(Directory); //target folder defined in constants
        $extension_allowed = array();
        //getting total files in the directory start
        if($search !='')
        {
            $extension_allowed=  explode(',', $search);
        }  
        while (false !== ($file = readdir($handle))) { 
            if ($file != "." && $file != ".." ) { 
                $file_extension=  pathinfo($file, PATHINFO_EXTENSION); 
                if(!empty($extension_allowed))//extension filtering
                {
                    if(in_array($file_extension, $extension_allowed))
                    {
                        $f['name'] = $file; 
                        $f['ext'] = $file_extension; 
                        $f['url'] = base_url().Directory.$file; 
                        $files[] = $f;

                    }  
                } 
                else
                {
                    $f['name'] = $file; 
                    $f['ext'] = $file_extension; 
                    $f['url'] = base_url().Directory.$file; 
                    $files[] = $f;
                }
            }
        } 
        closedir($handle); 
        $total_files = count($files); 
        //getting total files in the directory end
        $rowsperpage = rowsperpage; //rows per page to display
        $totalpages = ceil($total_files / $rowsperpage);
        if (isset($currentpage) && is_numeric($currentpage)) {
            $currentpage = (int) $currentpage;
        } else {
            $currentpage = 1;
            }
        if ($currentpage > $totalpages) {
            $currentpage = $totalpages;
        } 
        if ($currentpage < 1) { 
            $currentpage = 1;
        } 
        $offset = ($currentpage - 1) * $rowsperpage;
        $rowsperpage;
        if($rowsperpage>$total_files)
        $rowsperpage = $total_files;
        for($z=0; $z<$rowsperpage; $z++) { 
            $vf = $z+$offset;  
            if($vf<$total_files)
            {
                $files_display[] = $files[$vf];
            }
        } 
        if(!empty($files_display))
        {
            header('Content-Type: application/json'); // output 
            $json['success'] = 200;
            $json['files'] = $files_display;
            $json['msg'] = 'Files Listed';
            echo json_encode($json);
        }
        else{
            header('Content-Type: application/json'); // output 
            $json['success'] = 400;
            $json['files'] = $files_display;
            $json['msg'] = 'No Files Available';
            echo json_encode($json);
        }
        
    }

   /*
    url - http://localhost/whiterabbit/index.php/Files/delete
    params - file
    method - GET
    */

    public function delete() {
        $file = $this->input->get('file');//get file name from input
        if(file_exists(Directory.$file))
        {
            if(unlink(Directory.$file))//remove file
            {
                $this->load->model('FilesModel');
                $this->FilesModel->update_history($file,'0');//update delete history
                header('Content-Type: application/json'); // output 
                $json['success'] = 200;
                $json['msg'] = 'Files Deleted';
                echo json_encode($json);
            }
            else
            {
                header('Content-Type: application/json'); // output 
                $json['success'] = 400;
                $json['msg'] = 'Error in deleteting the file';
                echo json_encode($json);
            }
            
        }
        else
        {
            header('Content-Type: application/json'); // output 
            $json['success'] = 400;
            $json['msg'] = 'No file Found';
            echo json_encode($json);
        }
		    	
	}

    /*
    url - http://localhost/whiterabbit/index.php/Files/upload
    params - file
    method - POST
    */

    public function upload()
    {
        /**image upload**/
        if(isset($_FILES) && !empty($_FILES))
        {		
            $Folder = Directory; //target folder defined in constants
            if (!file_exists(getcwd().$Folder)) {
                mkdir(getcwd().$Folder, 0777, true);
            }
            if($Folder!='')
            {
                foreach($_FILES as $key =>$_FILE)
                {
                if($_FILE['name']!='')
                {
                    $path = $_FILE['name'];
                    $ext = pathinfo($path, PATHINFO_EXTENSION);//get extension
                    $file_size = $_FILE['size'];
                    if($file_size > SizeLimit)
                    {
                        if(in_array(strtolower($ext),unserialize(files)))//check whether allowed file
                        {
                            $file=date('Ymdhis') .'.'.$ext;		
                            $success = move_uploaded_file($_FILE["tmp_name"], getcwd().$Folder.$file);	
                            if($success)
                            {
                                $file_url = base_url().Directory.$file;
                                $this->load->model('FilesModel');
                                $this->FilesModel->update_history($file,'1');//update upload history
                                header('Content-Type: application/json'); // output 
                                $json['success'] = 200;
                                $json['file'] = $file_url;
                                $json['msg'] = 'Files Uploaded';
                                echo json_encode($json);
                            } 
                            else
                            {
                                header('Content-Type: application/json'); // output 
                                $json['success'] = 400;
                                $json['msg'] = 'Error in file upload ';
                                echo json_encode($json);
                            }
                        }	
                        else{
                            header('Content-Type: application/json'); // output 
                            $json['success'] = 400;
                            $json['msg'] = 'Not an allowed Extension';
                            echo json_encode($json);
                        }
                    } 
                    else
                    {
                        header('Content-Type: application/json'); // output 
                        $json['success'] = 400;
                        $json['msg'] = 'File Size is Large. Only leass than 2MB allowed';
                        echo json_encode($json);
                    }
                }  
                }
            }
        }
        /****/        
    }

        /*
    url - http://localhost/whiterabbit/index.php/Files/upload_history
    params - page
    method - POST
    */

    public function upload_history()
    {
        $this->load->model('FilesModel');
        $page = $this->input->post('page');//get page from input
        $data = $this->FilesModel->select_history($page);
        header('Content-Type: application/json'); // output 
        $json['success'] = 200;
        $json['content'] = $data;
        $json['msg'] = 'Content Loaded';
        echo json_encode($json);

    }

}
