<?php

    function Connection(){
        $login = (new mysqli("127.0.0.1","testUser","12345","IT490"));
        if ($login->connect_errno != 0)
	    {
	    	echo "Error connecting to database: ".$login->connect_error.PHP_EOL;
		    exit(1);
	    }
	    echo "correctly connected to database".PHP_EOL;
        return $login;
    }
    
?>