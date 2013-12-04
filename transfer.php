<?php

	/** 
	 * 	Transfer class for datadesk_workflow
	 * 	@author h1tjm03
	 * 
	 */
	
	/**
	 * 	AP Server Command Variables
	 *	These commands are used to logon to the AP server and transfer the files.
	 *
	 */
	public $script_commands;
	public $login_command;
	public $zip_command;
	public $scp_copy_command;
	public $delete_command;
	public $transfer_command;
	public $count_command;
	public $
	public $destination_directory;
	public $script_commands;


	public function __construct() {

		$this->destination_directory = "/home-ldap/$this->user_name/test_transfer/"; //"/www/fred/data/.../"
		$this->script_commands = array('sh_file_delete_all_files' => "rm -fr ".$source_directory."* 2>&1",
						   'sh_file_unzip_file' => "unzip -o ".$tmpfdir.$zip_file." -d ".$source_directory." 2>&1",
						   'sh_file_delete_zip_file' => "rm -fr ".$tmpfdir.$zip_file." 2>&1",
						   'sh_file_count_transferred_files' => "ls -1 ".$source_directory." | wc -l 2>&1",
						   'sh_file_set_group' => "chown -R ".$user_name.":datadesk ".$source_directory."* 2>&1",
						   'sh_file_set_permissions' => "/www/httpd/allow_permissions ".$source_directory." 2>&1");
	}

	public function transfer_series() {


	}

	public function zip_files() {
	}

	public function compare_transferred() {
	}


	public function file_volume_check () {
	}
?>
