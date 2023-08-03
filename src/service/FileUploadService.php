<?php

namespace App\Service\Files;

class FileUploadService{
public $comp, $newname;
public function upload(string $name, string $dir='public/uploads', bool $create_dir_if_not_exists=true, array $extensions=['png', 'jpeg', 'jpg'], int $size=25000000){
    $d=  explode('src', __DIR__)[0]; $d= str_replace('\\', '/', $d) . $dir; 
    $dir = $d;  
    if(empty($name)){
		   echo 'empty name parameter';
		   exit;
	   }
	   $filename = basename($_FILES[$name]['name']);
	   $this->isPresent($filename, 'php');
	   if ($this->comp == true){
		   echo '<br>file name is unacceptable'; exit;
	   }

		   
	   if(!file_exists($dir) && $create_dir_if_not_exists == false){
		 echo $dir . 'is not a recognized folder <br> please create this directory or pass <u>true</u> as a third parameter to this function';   
	   }
	   else if(!file_exists($dir) && $create_dir_if_not_exists == true){
           
		   mkdir($d);
	   }
	  // }
	   
	  $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
	  if (!in_array($file_ext, $extensions)){
		  echo 'unrecognized file extension - ' . $file_ext; exit;    
	  }
	  
	  if ($size !== 0){
		  if ($_FILES[$name]['size'] > $size){
			  echo 'max file size allowed : ' . $size . '<br> file size uploaded : ' . $_FILES[$name]['size'];
			  exit;
		  }
		  
	  }
	  else if($size == 0){
		$size =  $_FILES[$name]['size'] * 1.5; 
	  }
	
	  $date = time();
	  $this->newname = $dir . '/' . $date . $filename;
	  if (move_uploaded_file($_FILES[$name]['tmp_name'], $this->newname)){
		  $result = array();
		  $result['filename'] = $this->newname;
		  $result['outcome'] = 'file successfully moved ';
		  return $result;
	  }
   }
   
   function isPresent($str, $sub){
	if (stristr($str, $sub)){
		$this->comp = true;
		return $this->comp;
	}
	else{
		$this->comp = false;
		return $this->comp;
	}
}

}