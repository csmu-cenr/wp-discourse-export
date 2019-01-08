<?php
	// print_r($args) ;
	if (count($args)>0) {
		$output_path = $args[0] ;
		$users = get_users() ;
		$index = 0 ;
		$file_hahdler = fopen($output_path, "w") or die("Unable to open $output_path!") ;
		$headers = array('id','user_login','user_email','display_name','roles','user_registered', 'password', 'user_status') ;
		fwrite($file_hahdler,implode("\t",$headers)."\n") ;
		foreach($users as $user){
			//print_r($user) ;
			//print_r($user->roles) ;
			$fields = array( $user->id, $user->user_login, $user->user_email, $user->display_name, implode(',',$user->roles), $user->user_registered, $user->password, $user->user_status ) ;
			fwrite($file_hahdler,implode("\t",$fields)."\n") ;
			echo("$index\t" . implode("\t",$fields) . "\n");
			$index++ ;
			//break ;
		}
		fclose($file_hahdler) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-users.php users.txt\n\n") ;
	}
?>