<?php

/** 
 * 	Compare class for datadesk_workflow
 * 	@author h1tjm03
 * 
 */

//require dirname(__FILE__) . '\logger.php';

class Compare {
	
	/**
	 *	Local Machine Variables
	 *	These variables set the parameters that are used for local processing prior the to the transfer of data to the AP server.
	 *
	 */
	
	/**
	 *	User name for local directory 	
	 *
	 *	@var string
	 *	@access public 
	 */
	public $user_name;

	/**
	 *	Directory of files to be loaded 	
	 *
	 *	@var string
	 *	@access public 
	 */
	public $dir;
	
	/**
	 *	Variable used to remove '..', '.' in order to get correct count of files. Finds the nominal difference between the array of filenames and an array that has '..' and '.' as values.  	
	 *
	 *	@var array
	 *	@access public 
	 */
	public $files;
	
	/**
	 *	Records the returned value from $files. 	
	 *
	 *	@var integer
	 *	@access public 
	 */
	public $file_count;

	/**
	 *	Divides the integer in $files by two to get the amount of series that are going to be uploaded. 	
	 *
	 *	@var integer
	 *	@access public 
	 */
	public $series_count;
	
	/**
	 *	API key that is used in URL to download JSON object. 	
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
	 *	Records amount of expected series to upload depending on frequency of series observations. 	
	 *
	 *	@var NULL
	 *	@access public 
	 */
	public $expected;

	/**
	 *	Validates that expected count of series is correct by preg_matching a number from the JSON object.. 	
	 *
	 *	@var NULL
	 *	@access public 
	 */
	public $matches;


	/**
	 *	Constants
	 *	Web proxy and browser useragent for curl download functions.
	 *
	 */
	const PROXY = "http://h1proxy.frb.org:8080/";
	const USERAGENT = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0";


	/**
	 * 	Constructor function to initialize class and assign variables.
	 *
	 */
	public function __construct(/*$dir, $release_id*, *$frequency*/) {

		$this->user_name = strtolower(exec("ECHO %USERNAME%", $output_temp, $return_temp));;
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
		$this->matches = NULL;
	}

	/**
	 *	Validate directory dependent on OS. 
	 *
	 */
	public function validate_dir() {

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


	/**
	 * This function validates the count of series to upload. 
	 * The program errors out if there is only one file, no files, or if there are an odd number of files.
	 *
	 */
	public function count_series() {
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
		else if ($this->file_count % 2 !== 0) 
		{
			echo "Error: There are an odd number of files in the directory. Exiting program.\n";
			//Delete files from directory
			//Logging goes here.
			exit;
		} 
		else 
		{
			echo "There are ".$this->series_count." series."."\n";
			$this->compare_series();
		}
	}
	

	public function download_json() {

		curl_setopt($this->ch, CURLOPT_URL, $this->request);
		curl_setopt($this->ch, CURLOPT_USERAGENT, Compare::USERAGENT);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_PROXY, Compare::PROXY);
		
		while (!isset($this->download_obj) || $this->download_obj === false || preg_match("/\<\!DOCTYPE HTML PUBLI/", $this->download_obj)) 
		{
			$this->download_obj = curl_exec($this->ch);
		}
	}	

	public function get_expected_count($frequency) {
		
		$this->frequency = $frequency;
		$this->download_json();
		$json = json_decode($this->download_obj);
		if (isset($json)) 
		{
			$i = 0;
			$expected_count = array();
			while (isset($json->seriess[$i])) 
			{
				$freq_item = ($json->seriess[$i]->frequency_short);
				if ($freq_item = $frequency)
				{
					$expected_count[$i] = $freq_item;
					$i++;
				}
			}
			$this->expected_count = count($expected_count);
			echo $this->expected_count;
		} 
		else
		{
			echo "error";
			exit;
		}
	}

	public function compare_series() {

			$this->get_expected_count($this->frequency);
			if ($this->expected == $this->series_count) 
			{
				echo "The expected number of series "."(".$this->expected.")"." matches the number of processed series. "."(".$this->series_count.")".".\nProceeding to upload the files to FRED";
				$this->loading_animation();
				//$this->transfer_series();
			} 
			elseif ($this->expected > $this->series_count)
			{
				echo "There are fewer series to transfer than expected. Please see the log for details.\n";
				$this->series_different();
				//Logging goes here.
				exit;
			} 
			else
			{
				echo "There are more series to transfer than expected. Please see the log for details.\n";
				$this->series_different();
				//Logging goes here.
				exit;
			}
	}


	public function series_different() {

		if ($this->expected > $this->series_count)
		{
			echo $this->series_count." series is less than the ".$this->expected." expected series."."\n";	
		}
		elseif ($this->expected < $this->series_count)
		{
			echo $this->expected." expected series is less than ".$this->series_count."."."\n";
		}
		else
		{
			$this->kill();
		}
		
	}


	public function success_message() {

		echo "File upload successful.\n";
	}


	public function error_message() {

		echo "Oops. There was an error. Please see the log for details.\n";
	}


	public function loading_animation() {

		for ($seconds = 0; $seconds < 5; $seconds++) 
		{
			print ".";
			sleep(1);
		}
		echo "\n";
	}

	
	public function kill() {

		echo "Something has gone horribly wrong. Turn back now...";
		exit;
	}


	/**
	 * Destructor
	 *
	 */

	public function __destruct() {
		
	}
}

?>
