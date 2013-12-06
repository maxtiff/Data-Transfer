<?php

	/** 
	 * 	Transfer class for datadesk_workflow
	 * 	@author h1tjm03
	 * 
	 */
	
	class Transfer {
	/**
	 * 	AP Server Command Variables
	 *	These commands are used to logon to the AP server and transfer the files.
	 *
	 */
	public $user_name;
	public $dir;
	public $script_commands;
	public $login_command;
	public $zip_command;
	public $scp_copy_command;
	public $delete_command;
	public $transfer_command;
	public $count_command;
	public $set_group_command;
	public $set_permissions_command;
	public $destination_directory;

	/**
	 *	Constants
	 *
	 *
	 */
	//const $file_volume_threshold = 


	public function __construct(/*$dir*/) {

		$this->user_name = strtolower(exec("ECHO %USERNAME%", $output_temp, $return_temp));
		$this->dir = "C:/Users/$this->user_name/Documents/test_directory/";
		$this->destination_dir = "/home-ldap/$this->user_name/test_transfer/"; //"/www/fred/data/.../"
		$this->file_volume= NULL;
		// $this->script_commands = array('sh_file_delete_all_files' => "rm -fr ".$source_directory."* 2>&1",
		// 				   'sh_file_unzip_file' => "unzip -o ".$tmpfdir.$zip_file." -d ".$source_directory." 2>&1",
		// 				   'sh_file_delete_zip_file' => "rm -fr ".$tmpfdir.$zip_file." 2>&1",
		// 				   'sh_file_count_transferred_files' => "ls -1 ".$source_directory." | wc -l 2>&1",
		// 				   'sh_file_set_group' => "chown -R ".$user_name.":datadesk ".$source_directory."* 2>&1",
		// 				   'sh_file_set_permissions' => "/www/httpd/allow_permissions ".$source_directory." 2>&1");
	}


	public function transfer_series() {

	}


	public function validate() {

	}


	public function zip_files() {
	
		$this->get_directory_size($this->dir);

		$zip = new ZipArchive;

		$fixed_dir = preg_replace("[/]", "\\", $this->dir);

		echo $this->dir."\n";
		echo $fixed_dir."\n";

		$archive = $zip->open($this->dir."test.zip", ZipArchive::CREATE);
		
		if ($archive === True) 
		{
			
			if(!$dh=opendir($fixed_dir))
	    	{
	        echo false;
	        return false;
	    	}

		    while($file = readdir($dh))
		    {
				if($file == "." || $file == "..")
		        {
		            echo "Skipping $file\n";
		            continue;
		        }

		        if(is_file($fixed_dir.$file))
		        {
		            echo "Adding $file to archive...\n";
		            $zip->addFile($fixed_dir.$file);
		        }
			}

			$zip->close();

		}
		else
		{
			echo "error\n";
		}

		
	}


	public function compare_transferred() {
	}

	public function file_volume_check () {
		
	}

	public function get_directory_size($directory) {
    /**
     *	This function measures the file volume of the directory prior to the winzip operation. 
     *	
     *	@return float
     *	@access public
     */
	    $dir_size=0;
	     
	    if(!$dh=opendir($directory))
	    {
	        return false;
	    }
	     
	    while($file = readdir($dh))
	    {
	        if($file == "." || $file == "..")
	        {
	            continue;
	        }
	         
	        if(is_file($directory."/".$file))
	        {
	            $dir_size += filesize($directory."/".$file);
	        }
	         
	        if(is_dir($directory."/".$file))
	        {
	            $dir_size += get_directory_size($directory."/".$file);
	        }
	    }
	     
	    closedir($dh);
	     
	    $this->file_volume = ($dir_size / 1000);
	    echo $this->file_volume." KBs\n";
	    return $this->file_volume;
	}
}
?>
