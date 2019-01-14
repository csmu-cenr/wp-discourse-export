<?php
	// print_r($args) ;
	if (count($args)>0) {
		
		$output_path = $args[0] ;
		if ( count($args) > 1 ) {
			$wordress_host_from = trim($args[1]) ;			
		} else {
			$wordress_host_from = '' ;
		}
		if ( count($args) > 2 ) {
			$wordress_host_to = $args[2] ;			
		} else {
			$wordress_host_to = '' ;
		}
		
		$args = array(
			'numberposts' => -1,
		);
		$posts = get_posts( $args );
		$index = 0 ;
		$file_handler = fopen($output_path, "w") or die("Unable to open $output_path!") ;
		$index = 0 ;
		
		$users = get_users() ;
		$users_hash = array() ;
		foreach($users as $user ) {
			$users_hash[$user->id] = $user ;
		}
		
		$categories = get_categories() ;
		$categories_hash = array() ;
		foreach($categories as $category){
			$categories_hash[$category->cat_ID] = $category ;
		}
		
		$user_header = 'user_email' ;
		$post_categories = array() ;
		
		// https://stackoverflow.com/questions/1176904/php-how-to-remove-all-non-printable-characters-in-a-string
		// had an issue with ^{}
		$badchar=array(
		    // control characters
		    chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), //chr(9), chr(10), // keep tab and new line
		    chr(11), chr(12), //chr(13), // keep carriage return
			chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20),
		    chr(21), chr(22), chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30),
		    chr(31),
		    // non-printing characters
		    chr(127)
				) ;
		
		foreach($posts as $post){
			
			if ( $index == 0 ) {
				$headers = array() ;
				foreach ($post as $key => $value) {
					$headers[] = $key ;
				}
				fwrite($file_handler,implode("\t",$headers). "\t$user_header\tcategory_slug\tparent_category_slug\ttags\tpermalink\n") ;			
			}

			$fields = array() ;
			
			foreach ($post as $key => $value) {
				$fields[] = str_replace("\t",'\t',str_replace("\n", '\n', $post->$key )) ;					
			}
			$user = $users_hash[$post->post_author];
			$post_categories = get_the_category($post->ID) ;
			if ( count($post_categories) > 0 ) {
				$main_category = $post_categories[0] ;	
				if ( $main_category->parent > 0 ) {
					$parent_category = $categories_hash[$main_category->parent] ;
				} else {
					$parent_category = array('slug' => '') ;
				}		
			} else {
				$main_category = array('slug' => '') ;
				$parent_category = array('slug' => '') ;
			}
			// the following line is not working for some reason
			$permalink = str_replace($wordpress_host_from, $wordpress_host_to, get_permalink($post->ID)) ;
			echo ( $index . " $permalink\n" ) ;
			
			$post_tags = get_the_tags($post->ID) ;
			$tags = array() ;
			if ($post_tags){
				foreach($post_tags as $post_tags){
					$tags[] = $post_tags->slug ;
				}				
			}
			fwrite($file_handler,implode("\t",$fields)."\t". $user->user_email . "\t" . $main_category->slug . "\t" . $parent_category->slug . "\t" . implode(",",$tags) . "\t" . $permalink . "\n") ;				
			$index++ ;
		}
		fclose($file_handler) ;
		echo( $wordress_host_from . "\n" ) ;
		echo( $wordress_host_to . "\n" ) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-posts.php posts.txt\n\n") ;
	}
?>