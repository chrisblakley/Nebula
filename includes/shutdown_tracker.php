<?php
	register_shutdown_function('nebula_shutdown_handler');
	function nebula_shutdown_handler() {
		echo '<br/><br/><strong>Shutdown initialized</strong><br/>';
	
		$lasterror = error_get_last();
		switch ( $lasterror['type'] ) {
		    case E_ERROR:
		    case E_CORE_ERROR:
		    case E_COMPILE_ERROR:
		    case E_PARSE:
		        $error = array(
			        'type' => 'Fatal Error',
			        'level' => $lasterror['type'],
			        'message' => $lasterror['message'],
			        'file' => $lasterror['file'],
			        'line' => $lasterror['line']
			    );
		        //gaBuildData($error);
		        echo '<br/><strong>[SHUTDOWN]' . $error['type'] . ' [' . $error['level'] . ']:</strong> ' . $error['message'] . ' in <strong>' . $error['file'] . '</strong> on <strong>line ' . $error['line'] . '</strong>';
		        break;
		        
			default:
				echo 'shutdown defaulted';
		}
	}
?>