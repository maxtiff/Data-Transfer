<?php

	/** 
	 * 	Transfer class for datadesk_workflow
	 * 	@author h1tjm03
	 * 
	 */

class Transfer {
	
	/********************************************************************************
	 *	Local Attributes 															*
	 *	These attributes are used to determine file volume.							*
	 ********************************************************************************/
	
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
	 *	Location of ap parameter file used for secure access to ap server.
	 *
	 *	@var string
	 *	@access public
	 */
	public $ap_file;

	/**
	 *	Aggregate volume of files in the directory in KBs. Returned from get_directory_size() function.	
	 *
	 *	@var NULL
	 *	@access public 
	 */
	public $file_volume;

	/**
	 *	Flag that is turned on if files in directory are compressed into a zip file.	
	 *
	 *	@var bool
	 *	@access public 
	 */
	public $zipped;

	/********************************************************************************
	 * 	AP Server Command Variables 												*
	 *	These commands are used to logon to the AP server and transfer the files.	*
	 ********************************************************************************/

	/**
	 *	Server location for SSH login command.
	 *
	 *	@var string
	 *	@access public
	 */
	public $server;
	public $script_commands;
	public $login_command;
	public $unzip_command;
	public $scp_copy_command;
	public $delete_command;
	public $transfer_command;
	public $count_command;
	public $set_group_command;
	public $set_permissions_command;
	public $destination_directory;

	/********************************************************************************
	 *	Constants 																	*
	 *	KB threshold for file volume. If the file volume exceeds this number then 	*
	 *	all of the files in the directory are compressed into a zip file.			*
	 ********************************************************************************/
	const THRESHOLD = 100;

	/**
	 * 	Constructor function to initialize class and assign variables.
	 *
	 *	@access public
	 */
	public function __construct(/*$dir*/) 
	{
	
		$this->user_name = strtolower(exec("ECHO %USERNAME%", $output_temp, $return_temp));
		$this->dir = "C:/Users/$this->user_name/Documents/test_directory/";
		$this->ap_file = "C:/Users/$this->user_name/Documents/ap.ppk";
		$this->zip_file = "test.zip";
		$this->zipped = false;
		$this->destination_dir = "/home-ldap/$this->user_name/test_transfer/"; //"/www/fred/data/.../"
		$this->file_volume= NULL;
		$this->server = $this->user_name."@ap.stlouisfed.org";
		$this->ssh_command = "plink -ssh ".$this->server." -i ".$this->ap_file." -m ";
		$this->ssh_copy = "pscp -i C:/Users/$this->user_name/Documents/ap.ppk ";
		$this->ssh_transfer_zip = $this->ssh_copy.$this->dir.$this->zip_file." ".$this->server.":".$this->destination_dir." 2>&1";
		$this->ssh_unzip = "unzip ".$this->destination_dir.$this->zip_file." -d ".$this->destination_dir." 2>&1";
		//$this->ssh_transfer_all = $this->ssh_copy.$this->dir.*." ".$this->server.":".$this->destination_dir." 2>&1";
		// $this->script_commands = array('sh_file_delete_all_files' => "rm -fr ".$source_directory."* 2>&1",
		// 				   'sh_file_unzip_file' => "unzip -o ".$this->destination_dir.$this->zip_file." -d ".$this->destination_dir." 2>&1",
		// 				   'sh_file_delete_zip_file' => "rm -fr ".$tmpfdir.$zip_file." 2>&1",
		// 				   'sh_file_count_transferred_files' => "ls -1 ".$source_directory." | wc -l 2>&1",
		// 				   'sh_file_set_group' => "chown -R ".$user_name.":datadesk ".$source_directory."* 2>&1",
		// 				   'sh_file_set_permissions' => "/www/httpd/allow_permissions ".$source_directory." 2>&1");
	}

	public function run() 
	{
	}

	public function validate() 
	{
	}


	public function transfer_series() 
	{
		
		$this->file_volume_check();
		
		if ($this->zipped == True) 
		{
			system($this->ssh_transfer_zip);
			system($this->ssh_command.$this->ssh_unzip);
		}
		else
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
		            echo "Transferring $file to AP...\n";
		            system($this->ssh_copy.$this->dir.$file." ".$this->server.":".$this->destination_dir." 2>&1");
		        }
			}

		}
		
	}

	/**
	 *	This function takes the volume of the files in the directory from get_directory_size() function; if the volume surpasses the defined threshold, the function will zip
	 *	the files.
	 *
	 *	@access public
	 */
	public function file_volume_check () 
	{
	
		$this->get_directory_size($this->dir);

		if ($this->file_volume >= Transfer::THRESHOLD)
		{
			echo "The volume of the files to be transferred exceeds 1000 KBs.\n Zipping files.\n";
			$this->zip_files();
		}
		elseif ($this->file_volume < Transfer::THRESHOLD || $this->file_volume > 0)
		{
			echo "The volume of the files to be transferred is less than the 1000 KBs threshold.\n";
		}
		else
		{
			echo "Something terrible has happened";
			//insert a kill function extended from Compare class
			exit;
		}

	}


	/**
	 *	This function measures the file volume of the directory prior to the winzip operation and returns $file_volume in KBs. 
	 *	
	 *	@return float
	 *	@access public
	 */
	public function get_directory_size($directory) 
	{
    
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
	    
	    //Convert volume from Bs to KBs 
	    $this->file_volume = ($dir_size / 1000);
	    echo $this->file_volume." KBs\n";
	    return $this->file_volume;
	}


	
	/**
	 *	This function zips the files in the directory if the aggregate volume of the files surpasses the defined threshold of 1000 KBs.
	 *
	 *	@access public
	 */
	public function zip_files() 
	{

		$zip = new ZipArchive;

		$archive = $zip->open("$this->dir"."$this->zip_file", ZipArchive::CREATE);
		
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
		            //For the addFile function to work correctly (i.e. to not zip the file with its absolute directory tree), a relative path for each compressed file must be provided via the 2nd parameter of the function.
		            $zip->addFile("$this->dir"."$file", $file);
		        }
			}
			$zip->close();
			$this->zipped = True;
		}
		else
		{
			echo "error\n";
		}
	}


	public function compare_transferred() 
	{

	}

}

?>
