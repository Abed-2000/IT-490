<?php
function log_this($log_msg)
{
	$log_time = date('Y-m-d h:i:sa');
	$log_filename = 'rabbit';
	if (!file_exists($log_filename)) 
	{
		// create directory/folder uploads.
		mkdir($log_filename, 0777, true);
	}
	$log_file_data = $log_filename . '.log';
    file_put_contents($log_file_data, $log_time. "\n" .$_SERVER. "\n" .$log_msg . "\n", FILE_APPEND);
}
?>
