<?php

/** 
 * 	Compare class for datadesk_workflow
 * 	@author h1tjm03
 * 
 */

//require dirname(__FILE__) . '\logger.php';

class Compare {
	
	/********************************************************************************
	 *	Local Network Variables 													*
	 *	These variables set the parameters that are used for local processing prior *
	 *	the to the transfer of data to the AP server.								*
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
	 *	Variable used to remove '..', '.' in order to get correct count of files. Finds the nominal difference between the array of filenames from the 
	 *	directory and an array that has '..' and '.' as values.  	
	 *
	 *	@var array
	 *	@access public 
	 */
	public $files;
	
	/**
	 *	Assigned with counted value from $files. 	
	 *
	 *	@var integer
	 *	@access public 
	 */
	public $file_count;

	/**
	 *	Divides the integer in $files by two to get the amount of series that will be uploaded. 	
	 *
	 *	@var integer
	 *	@access public 
	 */
	public $series_count;
	
	/**
	 *	FRED API key that is used in URL to download JSON object. 	
	 *
	 *	@var string
	 *	@access public 
	 */
	public $api_key;

	/**
	 *	Integer used in URL to download JSON object. 	
	 *
	 *	@var integer
	 *	@access public 
	 */
	public $release_id;

	/**
	 * Array used to determine which frequencies are updated with the release.
	 *
	 *	@var array
	 *	@access public
	 */
	public $frequency;

	/**
	 *	URL used to download JSON file through FRED API. 	
	 *
	 *	@var string
	 *	@access public 
	 */
	public $request;

	/**
	 *	Initializes Curl to download JSON object. 	
	 *
	 *	@var resource
	 *	@access public 
	 */
	public $ch;

	/**
	 *	Used to validate if Curl download has initialized. 	
	 *
	 *	@var boolean
	 *	@access public 
	 */
	public $download;

	/**
	 *	Records amount of expected series to upload depending on frequency of series observations. Variable is set
	 *	by the number of occurences in the JSON object. 	
	 *
	 *	@var NULL
	 *	@access public 
	 */
	public $expected;


	/********************************************************************************
	 *	Constants 																	*
	 *	Web proxy and browser useragent for curl download functions.				*
	 ********************************************************************************/
	const PROXY = "http://h1proxy.frb.org:8080/";
	const USERAGENT = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0";


	
	public function __construct(/*$dir, $release_id*, *$frequency*/) {
	/**
	 * 	Constructor function to initialize class and assign variables.
	 *
	 *	@access public
	 */

		$this->user_name = strtolower(exec("ECHO %USERNAME%", $output_temp, $return_temp));
		$this->dir = "C:/Users/$this->user_name/Documents/test_directory/"; //$dir
		$this->files = array_diff(scandir($this->dir), array('..', '.'));
		$this->file_count = count($this->files);
		$this->series_count = ($this->file_count)/2;
		$this->api_key = "76bb1186e704598b725af0a27159fdfc";
		$this->release_id = 97; //$release_id;
		$this->frequency = "M"; //$frequency;
		$this->request = "http://api.stlouisfed.org/fred/release/series?release_id=$this->release_id&api_key=$this->api_key&file_type=json";
		$this->ch = curl_init();
		$this->download_obj = curl_exec($this->ch);
		$this->expected = NULL;
	}

	
	public function validate_dir() {
	/**
	 *	Validate directory dependent on OS. 
	 *
	 *	@return void
	 *	@access public
	 */

		$os = php_uname('s');
		if ($os == 'Linux') 
		{
			$this->dir = preg_replace("C:/Users/$this->user_name/Documents/test_directory/", "/home-ldap/$this->user_name/", $this->dir);
		}
		else 
		{
			echo "File Location: ".$this->dir."\n";
		}
	}


	
	public function count_series() {
	/**
	 * 	This function validates the count of series to upload. 
	 * 	The program will error if there is only one file, there are no files, or if 
	 *	there are an odd number of files.
	 *	
	 *	If there are an even number of files the program will proceed to validate whether 
	 *	the series count matches the expected count of files to be uploaded.
	 *
	 *	@return void
	 *	@access public
	 */	

		if ($this->file_count == 0)
		{
			echo "Error: There are no files in the directory. Exiting program.\n";
			exit;
		}
		elseif ($this->file_count == 1) 
		{
			echo "Error: There is only one file in the directory. Exiting program.\n";
			//Delete files from directory
			//Logging goes here.
			exit;
		} 
		elseif ($this->file_count % 2 !== 0) 
		{
			echo "Error: There are an odd number of files in the directory. Exiting program.\n";
			//Delete files from directory
			//Logging goes here.
			exit;
		} 
		elseif ($this->file_count % 2 == 0 and $this->file_count > 1)
		{
			echo "There are ".$this->series_count." series in the directory to transfer."."\n";
			$this->compare_series();
		}
		else
		{
			$this->kill();
		}
	}
	

	
	public function compare_series() {
	/**
	 *	This function compares the returned value of expected series (from func get_expected_count) against the count of the files in the directory.
	 *	If the returned value and count matches up the files will be uploaded to FRED.
	 *	If the returned value is greater than the count then release date exceptions will be added for the non-updated series.
	 *	If the returned value is less than the count then the program will error and close.
	 *
	 *	This function uses the get_expected_count() function for comparison.
	 *
	 *	@return void
	 *	@access public
	 */

		$this->get_expected_count();
		if ($this->expected == $this->series_count) 
		{
			$this->series_count_same();
			//$this->transfer_series();
		} 
		else
		{
			$this->series_count_different();
			//Logging goes here.
			exit;
		} 
	}


	
	public function get_expected_count() {
	/**
	 *	This function gets the expected series count by counting all of the series in a release that 
	 *	have the required frequency.
	 *	
	 *	The download_json() function is used to get the JSON object.
	 *
	 *	This function is used in the compare_series() function.
	 *
	 *	@return int
	 *	@access public
	 */	

		$this->download_json();
		
		//Prepare JSON object for scanning.
		$json_object = json_decode($this->download_obj);
		
		//Validate that encoded JSON object has been assigned to variable
		if (isset($json_object)) 
		{
			$i = 0;
			$expected_count = array();
			
			//Loop through each item (series) in the 'seriess' subsection of the JSON object.
			while (isset($json_object->seriess[$i])) 
			{
				
				//Assign string from 'frequency_short' key ('D','W','BW','M','Q','SA','A') to check against the expected series frequency in order to determine the count of files that are to be uploaded.
				$freq_item = ($json_object->seriess[$i]->frequency_short);
				
				if ($freq_item == $this->frequency)
				{
					$expected_count[$i] = $freq_item;
					$i++;
				}
				elseif ($freq_item !== $this->frequency)
				{
					echo "Could not determine the series count that is listed in the downloaded file.\n";
                    //Logging goes here.
                    exit;
				}
				else
				{
					$this->kill();
				}
			}
			return $this->expected = count($expected_count);
		} 
		else
		{
			echo "error";
			exit;
		}
	}


	public function download_json() {
	/**
	 *	This function downloads the JSON object that is used to determine the count of series to compare against
	 *	the count of files in the directory.
	 *
	 *	This function is used in the get_expected_count() function.
	 *
	 *	@access public
	 */

		curl_setopt($this->ch, CURLOPT_URL, $this->request);
		curl_setopt($this->ch, CURLOPT_USERAGENT, Compare::USERAGENT);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_PROXY, Compare::PROXY);
		
		while (!isset($this->download_obj) || $this->download_obj === false || preg_match("/\<\!DOCTYPE HTML PUBLI/", $this->download_obj)) 
		{
			$this->download_obj = curl_exec($this->ch);
		}
	}	



	public function series_count_different() {
	/**
	 *	Messaging in the event that the expected number of series does not match the number of series to be transferred.
	 *
	 *	@return void
	 *	@access public
	 */

		if ($this->expected > $this->series_count)
		{
			echo "The ".$this->series_count." series to transfer is less than the ".$this->expected." expected series.\nExiting program.\n";
			//Create release date exception.	
		}
		elseif ($this->expected < $this->series_count)
		{
			echo "The ".$this->series_count." series to transfer is greater than the ".$this->expected." expected series.\nExiting program.\n";
		}
		else
		{
			$this->kill();
		}
		
	}

	
	public function series_count_same() {
	/**
	 *	Messaging in the event that the expected number of series matches the number of series to be transferred.
	 *
	 *	@return void
	 *	@access public
	 */

		echo "The expected number of series (".$this->expected.") matches the number of processed series (".$this->series_count.").\nProceeding to upload the files to FRED";

		for ($seconds = 0; $seconds < 5; $seconds++) 
		{
			print ".";
			sleep(1);
		}
		echo "\n";
	}

	
	
	public function kill() {
	/**
	 *	Kill function that is used for error trapping.
	 *	This should be used at the very end of a conditional statement, once all possible conditions have been exhausted.
	 *
	 *	@access public
	 */

		echo "Something has gone horribly wrong. Turn back now...";
		echo error_get_last();
		exit;
	}


	

	public function __destruct() {
	/**
	* 	Destructor
	*
	*	@access public
	*/
	}

}

?>
