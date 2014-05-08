<?php
function get_google_csv($url, $mapRow = false){
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$csv_data = curl_exec( $ch ) or die( 'CURL ERROR: '.curl_error( $ch ) );
	curl_close( $ch );
	
	/* Call function to parse .CSV data string into an indexed array. */
	$csv = parse_gcsv( $csv_data );
	if ($mapRow){
		$header =  array_shift($csv);
		$output = array();
		foreach($csv as $row){
			$d = array_combine($header,$row);
			$output[] = $d;
		}
	} else {
		$output = $csv;
	}
	return $output;
}

// http://www.php.net/manual/en/function.str-getcsv.php#111665
function parse_gcsv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
{
    $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
    $enc = preg_replace_callback(
        '/"(.*?)"/s',
        function ($field) {
            return urlencode(utf8_encode($field[1]));
        },
        $enc
    );
    $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
    return array_map(
        function ($line) use ($delimiter, $trim_fields) {
            $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
            return array_map(
                function ($field) {
                    return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                },
                $fields
            );
        },
        $lines
    );
}
?>