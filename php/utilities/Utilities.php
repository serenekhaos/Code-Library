<?php

// uses the Singleton Pattern: http://php.net/manual/en/language.oop5.patterns.php

class Utilities
{
	private static $instance;

    private function __construct()
    {
    }

    // The singleton method
    public static function singleton() 
    {
        if (!isset(self::$instance))
        {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

	public function getNodeValue($node)
	{
		$returnString = '';
	
		if (isset($node))
		{
			$returnString = sprintf("%s", $node[0]);
		}
			
		return $returnString;
	}
    
	public function getAttributeFromXmlNode($node, $attributeName)
	{
		if ( (!isset($node)) || (!isset($attributeName)) )
		{
			return '';
		}
		
	    $attributes = $node->attributes();
	
	    return $attributes[$attributeName];
	}

	public function tinyUrl($url)  
	{  
		$tinyUrlLink = 'http://tinyurl.com/api-create.php?url=' . $url;
		
		$link = $this->connectViaCurl($tinyUrlLink);
		
		return $link;  
	}

	// code snippet taken from http://davidwalsh.name/bitly-php
	public function bitlyUrl($url, $login, $appkey, $format = 'xml', $version = '2.0.1')
	{  
		//create the URL
		$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;
		
		//get the url
		$response = file_get_contents($bitly);
		
		//parse depending on desired format
		if(strtolower($format) == 'json')
		{
			$json = @json_decode($response,true);
			$link = $json['results'][$url]['shortUrl'];
		}
		else //xml
		{
			$xml = simplexml_load_string($response);
			$link = 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
		}

		return $link;  
	}
	
	public function createTagCloud($data, $count, $sortCol)
	{
		$sortedData;
		
		// sort by number of active items
		for ($i = 0; $i < $count; ++$i)
		{
			for ($j = 0; $j < $count - 1; ++$j)
			{
				$item1 = &$data[$j];
				$item2 = &$data[$j + 1];

				if ($item2['count'] > $item1['count'])
				{
					$temp = $item2;
					$item2 = $item1;
					$item1 = $temp;
				}
			}
		}

		// assign tagcloud value
		$previousCount = 0;
		$previousTag = 11;

		for ($i = 0; $i < $count; ++$i)
		{
			$item = &$data[$i];

			if ($previousCount != $item['count'])
			{
				$previousCount = $item['count'];
				$previousTag = --$previousTag;
			}
			
			$item['tag'] = 'tag' . $previousTag;
		}

		// sort by location name
		for ($i = 0; $i < $count; ++$i)
		{
			for ($j = 0; $j < $count - 1; ++$j)
			{
				$item1 = &$data[$j];
				$item2 = &$data[$j + 1];

				if (0 > strcasecmp($item2[$sortCol], $item1[$sortCol]))
				{
					$temp = $item2;
					$item2 = $item1;
					$item1 = $temp;
				}
			}
		}
		
		$sortedData = $data;
		
		return $sortedData;
	}
	
	public function connectViaCurl($url)
	{
		$ch = curl_init();  
		$timeout = 5;  
		curl_setopt($ch,CURLOPT_URL,$url);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
		$data = curl_exec($ch);  
		curl_close($ch);  

		return $data;  
	}
	
	public function connectViaFile($url)
	{
		$handle = fopen($url, "rb");
		$data = stream_get_contents($handle);
		fclose($handle);
		
		return $data;
	}

    public function sanitizeData($data, $allowed = '')
	{
    	$returnString = '';
    	
    	if (isset($data))
    	{
    		if (is_array($allowed))
    		{
				$returnString = strip_tags($data, '<' . implode('><', $allowed) . '>');
			}
			else
			{
				$returnString = strip_tags($data, $allowed);
			}
    	}
    	
    	return $returnString;	
	}
	
    public function validateData($data, $key, $allowed = '')
    {
    	$returnString = '';
    	
    	if (isset($data[$key]))
    	{
    		$returnString = trim(stripslashes($this->sanitizeData($data[$key], $allowed)));
    	}
    	
    	return $returnString;
    }

	/**
	 * Strips extra whitespace from output
	 *
	 * @param string $str String to sanitize
	 * @return string whitespace sanitized string
	 * @access public
	 * @static
	 */
	function stripWhitespace($str)
	{
		$r = preg_replace('/[\n\r\t]+/', '', $str);

		return preg_replace('/\s{2,}/', ' ', $r);
	}

	public function displayCreateDateString($date)
	{
		$dateString = date('d M Y', strtotime($date));
		
		return $dateString;
	}

	public function date_diff($start, $end="NOW")
	{
		$timeshift = '0 sec';
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $time = $edate - $sdate;

        if ($time>=0 && $time<=59)
        {
            // Seconds
            $timeshift = $time.' sec ';
        }
        elseif ($time>=60 && $time<=3599)
        {
            // Minutes + Seconds
            $pmin = ($edate - $sdate) / 60;
            $premin = explode('.', $pmin);
            
            $presec = $pmin-$premin[0];
            $sec = $presec*60;
            
//          $timeshift = $premin[0].' min '.round($sec,0).' sec ';
            $timeshift = $premin[0].' min ';
        }
        elseif ($time>=3600 && $time<=86399)
        {
            // Hours + Minutes
            $phour = ($edate - $sdate) / 3600;
            $prehour = explode('.',$phour);
            
            $premin = $phour-$prehour[0];
            $min = explode('.',$premin*60);
            
            $presec = '0.'.$min[1];
            $sec = $presec*60;

//          $timeshift = $prehour[0].' hrs '.$min[0].' min '.round($sec,0).' sec ';
            $timeshift = $prehour[0].' hrs ';
        }
        elseif ($time>=86400)
        {
            // Days + Hours + Minutes
            $pday = ($edate - $sdate) / 86400;
            $preday = explode('.',$pday);

            $phour = $pday-$preday[0];
            $prehour = explode('.',$phour*24); 

            $premin = ($phour*24)-$prehour[0];
            $min = explode('.',$premin*60);
            
            $presec = '0.';
            
            if (isset($min[1]))
            {
                $presec .= $min[1];
            }
            
            $sec = $presec*60;
            
//          $timeshift = $preday[0].' days '.$prehour[0].' hrs '.$min[0].' min '.round($sec,0).' sec ';
            $timeshift = $preday[0].' days ';
        }
        
        return $timeshift;
	}   

	public function mondayTimestamp($numMondaysBack = 1)
	{
		$now = time();
		
		// check if today is Monday
		if (1 == date('N', $now))
		{
			if (1 == $numMondaysBack)
			{
				$mondayTimestamp = $now;
			}
			else
			{
				$mondayTimestamp = strtotime('-' . $numMondaysBack . ' Monday', $now);
			}
		}
		else
		{
			$mondayTimestamp = strtotime('Monday', $now);
			
			// check if it is for upcoming monday
			if ($now < $mondayTimestamp)
			{
				$mondayTimestamp = strtotime('-' . $numMondaysBack . ' Monday', $now);
			}
		}
		
		return $mondayTimestamp;
	}
		
	public function mondayDateString($numMondaysBack = 1)
	{		
		$mondayDateString = date('Y-m-d', $this->mondayTimestamp($numMondaysBack));
		
		return $mondayDateString;
	}
}

?>