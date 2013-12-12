<?php
const TEMP_DIRECTORY = "P:/DATADESK/temp";
const LOG_DIRECTORY = "P:/DATADESK/logs/ssh/";

$tmpfdir = "/home-ldap/h1tjm03/test_transfer/";
$source_directory = "C:/Users/h1tjm03/Documents/test_directory/";
$zip_file = "test.zip";

$sh_script_commands = array('sh_file_delete_all_files' => "rm -fr ".$source_directory."* 2>&1",
						   'sh_file_unzip_file' => "unzip -o ".$tmpfdir.$zip_file." -d ".$source_directory." 2>&1",
						   'sh_file_delete_zip_file' => "rm -fr ".$tmpfdir.$zip_file." 2>&1",
						   'sh_file_count_transferred_files' => "ls -1 ".$source_directory." | wc -l 2>&1",
						   'sh_file_set_group' => "chown -R ".$user_name.":datadesk ".$source_directory."* 2>&1",
						   'sh_file_set_permissions' => "/www/httpd/allow_permissions ".$source_directory." 2>&1");

$sh_script_file_paths = array('sh_file_delete_all_files' => TEMP_DIRECTORY."shell_script_delete_all_files"."sh",
							  'sh_file_unzip_file' => TEMP_DIRECTORY."shell_script_unzip_file_".".sh",
							  'sh_file_delete_zip_file' => TEMP_DIRECTORY."shell_script_delete_zip_file_".".sh",
							  'sh_file_count_transferred_files' => TEMP_DIRECTORY."shell_script_count_transferred_files_".".sh",
							  'sh_file_set_group' => TEMP_DIRECTORY."shell_script_set_group_".".sh",
							  'sh_file_set_permissions' => TEMP_DIRECTORY."shell_script_set_permissions_".".sh");

$command = "plink -ssh ".$server." -i C:/Users/".$user_name."/Documents/ap.ppk -m ";
$copy = "pscp -i C:/Users/".$user_name."/Documents/ap.ppk ";
$command_local_zip = "wzzip ".TEMP_DIRECTORY.$zip_file." ".$directory.$file." 2>&1";
$command_ssh_delete_all_files = $command.$sh_script_file_paths['sh_file_delete_all_files'];
$command_ssh_transfer = $copy.TEMP_DIRECTORY.$zip_file." ".$server.":".$tmpfdir." 2>&1";
$command_ssh_unzip = $command.$sh_script_file_paths['sh_file_unzip_file'];
$command_ssh_delete_zip = $command.$sh_script_file_paths['sh_file_delete_zip_file'];
$command_ssh_count_files = $command.$sh_script_file_paths['sh_file_count_transferred_files'];
$command_ssh_set_group = $command.$sh_script_file_paths['sh_file_set_group'];
$command_ssh_set_permissions = $command.$sh_script_file_paths['sh_file_set_permissions'];


function make_shell_scripts($array_of_shell_scripts, $commands)
{
    foreach($array_of_shell_scripts as $key => $path) {
		$fh = fopen($path, "w");
		fwrite($fh, $commands[$key]);
		fclose($fh);
	}
	
	return TRUE;
}

?>
