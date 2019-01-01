<?php
	// print_r($args) ;
	if (count($args)>0) {
		$output_path = $args[0] ;
		$file_parts = pathinfo($output_path);
		$yml = $file_parts['extension'] == 'yml' ;
		$txt = $file_parts['extension'] == 'txt' ;
		$args = array(
			'numberposts' => -1,
		);
		$posts = get_posts( $args );
		$index = 0 ;
		$file_hahdler = fopen($output_path, "w") or die("Unable to open $output_path!") ;
		$index = 0 ;
		
		$users = get_users() ;
		$users_hash = array() ;
		foreach($users as $user ) {
			$users_hash[$user->id] = $user ;
		}
		
		$user_header = 'user_email' ;
		foreach($posts as $post){
			if ( $index == 0 ) {
				$headers = array() ;
				foreach ($post as $key => $value) {
				 	echo $key . "\t" ;
					$headers[] = $key ;
				}
			 	echo $key . "\n" ;
				if ( $txt ) {
					fwrite($file_hahdler,implode("\t",$headers). "\t$user_header\n") ;			
				}
			}

			
			$fields = array() ;
			echo ( $index . "\n" ) ;
			foreach ($post as $key => $value) {
				if ( $txt ) {
					$fields[] = str_replace("\r\n", '\n', $post->$key ) ;					
				}
				if ( $yml ) {
					$fields[] = $post->$key ;		
				}
			}
			$user = $users_hash[$post->post_author];
			if ( $txt ) {
				
				fwrite($file_hahdler,implode("\t",$fields)."\t". $user->id . "\n") ;				
			}
			if  ( $yml ) {
				$data = '' ;
				foreach($headers as $header ) {
					if($data==''){
						// start of new record
						$data .= '- ' . $header . ': ' ;
					} else {
						$data .= '  ' . $header . ': ' ;
					}
					if ( strpos($post->$header, "\n")) {
						// handle newlines
						$data .= "|\n" ;
					}
					$data .= $post->$header . "\n" ;
				}
				$data .= '  user_email: ' . $user->user_email . "\n" ;
				fwrite($file_hahdler,$data) ;	
			}
			//echo("$index\t" . implode("\t",$fields) . "\n");
			$index++ ;
		}
		fclose($file_hahdler) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-posts.php posts.txt\n\n") ;
	}
?>