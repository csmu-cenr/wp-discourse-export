<?php
	// print_r($args) ;
	if (count($args)>0) {
		$output_path = $args[0] ;
		$post_args = array(
			'numberposts' => -1,
		);
		$posts = get_posts( $post_args );
		$index = 0 ;
		$file_handler = fopen($output_path, "w") or die("Unable to open $output_path!") ;

		$index = 0 ;
		foreach($posts as $post){
			
			$comment_args = array( 'post_id' => $post->ID, 'order' => 'ASC' );
			$comments = get_comments( $comment_args );
			$comment_post_ids = [];
			foreach ( $comments as $comment ) {
				$raw = $comment->comment_content;
				$author_email = $comment->comment_author_email;
				$author = get_user_by( 'email', $author_email);
				$comment_id = $comment->comment_ID;
				// This needs to resolve multi-level comments down to 2 threads.
				$comment_parent = $comment->comment_parent;
				$reply_to_post_number = ! empty( $comment_post_ids[ $comment_parent ] ) ? $comment_post_ids[ $comment_parent ] : null;
				
				$output = array() ;
				foreach($comment as $key => $value) {
					$include = true ;
					if ( $key == 'children') {
						$include = false ;
					}
					if ( $key == 'populated_children') {
						$include = false ;
					}
					if ( $key == 'post_fields') {
						$include = false ;
					}
					if ($include){
						$output[$key] = str_replace("\t",'\t',str_replace("\n", '\n',$value )) ;
					}
				}
				
				if ( $index == 0){
					$headers = array() ;
					foreach ($output as $key => $value) {
						$headers[] = $key ;
					}
					fwrite($file_handler,implode("\t",$headers) . "\n" ) ;			
				} else {
					$fields = array() ;
					foreach ($output as $key => $value) {
						$fields[] = $value ;
					}
					fwrite($file_handler,implode("\t",$fields) . "\n" ) ;
				}
				$index++ ;
				echo( $index . "\n" ) ;
			}	
		}
		fclose($file_handler) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-posts.php posts.txt\n\n") ;
	}
?>