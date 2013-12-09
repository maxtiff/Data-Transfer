<?php

	/** 
	 * 	Transfer class for datadesk_workflow
	 * 	@author h1tjm03
	 * 
	 */

class Transfer {
	
	/**
	 *	Local Attributes
	 *	These attributes are used to determine file volume.
	 */
	
	/**
	 *	User name for local directory and AP access.
	 *
	 *	@var string
	 *	@access public
	 */
	public $user_name;

	/**
	 *	Directory of files to be loaded. 	
	 *
	 *	@var string
	 *	@access public 
	 */
	public $dir;

	/**
	 *	Aggregate volume of files in the directory in KBs. Returned from get_directory_size() function.	
	 *
	 *	@var NULL
	 *	@access public 
	 */
	public $file_volume;

	/**
	 * 	AP Server Command Variables
	 *	These commands are used to logon to the AP server and transfer the files.
	 */

	/**
	 *
	 *
	 *
	 */
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
	 *	KB threshold for file volume. If the file volume exceeds this number then all of the files in the directory are compressed into a zip file.
	 *
	 */
	const THRESHOLD = 1000; 


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

	public function file_volume_check () {
	/**
	 *	This function gets the volume of the files in the directory; if the volume surpasses the defined threshold, the function will zip
	 *	the files.
	 *
	 *	@access public
	 */
		$this->get_directory_size($this->dir);

		if ($this->file_volume >= THRESHOLD)
		{
			echo "The volume of the files to be transfered is too large.\n Zipping files.";
			$this->zip_files();
		}
		elseif (0 < $this->file_volume < THRESHOLD)
		{
			echo "Transfer\n";
			continue;
		}
		else
		{
			echo "Something terrible has happened";
			//insert a kill function extended from Compare class
			exit;
		}

	}


	public function get_directory_size($directory) {
    /**
     *	This function measures the file volume of the directory prior to the winzip operation and returns $file_volume in KBs. 
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


	public function zip_files() {
	/**
	 *	This function zips the files in the directory if the aggregate volume of the files surpasses the defined threshold of 1000 KBs.
	 *
	 *	@access public
	 */
		$zip = new ZipArchive;

		$archive = $zip->open("$this->dir"."test.zip", ZipArchive::CREATE);
		
		if ($archive === True) 
		{
			
			if(!$dh=opendir($this->dir))
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

		        if(is_file("$this->dir"."$file"))
		        {
		            echo "Adding $file to archive...\n";

		            //For the addFile function to work correctly (i.e. to not zip the file with its absolute directory tree), a relative path for each file to be added to the zip file must be provided via the 2nd parameter of the function.
		            $zip->addFile("$this->dir"."$file", $file);
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

	

	
}
?>
