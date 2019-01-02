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
		
		$categories = get_categories() ;
		$categories_hash = array() ;
		foreach($categories as $category){
			$categories_hash[$category->cat_ID] = $category ;
		}
		
		$user_header = 'user_email' ;
		$post_categories = array() ;
		foreach($posts as $post){
			
			if ( $index == 0 ) {
				$headers = array() ;
				foreach ($post as $key => $value) {
				 	echo $key . "\t" ;
					$headers[] = $key ;
				}
			 	echo $key . "\n" ;
				if ( $txt ) {
					fwrite($file_hahdler,implode("\t",$headers). "\t$user_header\tcategory_slug\tparent_category_slug\ttags\tpermalink\n") ;			
				}
			}

			$fields = array() ;
			
			foreach ($post as $key => $value) {
				if ( $txt ) {
					$fields[] = str_replace("\r\n", '\n', $post->$key ) ;					
				}
				if ( $yml ) {
					$fields[] = $post->$key ;		
				}
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
			$permalink = str_replace($wordpress_host_from, $wordpress_host_to, get_permalink($post->ID)) ;
			echo ( $index . " $permalink\n" ) ;
			
			$post_tags = get_the_tags($post->ID) ;
			$tags = array() ;
			if ($post_tags){
				foreach($post_tags as $post_tags){
					$tags[] = $post_tags->slug ;
				}				
			}
			// print_r( $post_categories ) ;
			if ( $txt ) {	
				fwrite($file_hahdler,implode("\t",$fields)."\t". $user->id . "\t" . $main_category->slug . "\t" . $parent_category->slug . "\t" . implode(",",$tags) . "\t" . $permalink . "\n") ;				
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
				$data .= '  category_slug: ' . $main_category->slug . "\n" ;
				$data .= '  parent_category_slug: ' . $parent_category->slug . "\n" ;
				$data .= '  tags: ' . implode(",",$tags) . "\n" ;
				$data .= '  permalink: ' . $permalink . "\n" ;
				fwrite($file_hahdler,$data) ;	
			}
			//echo("$index\t" . implode("\t",$fields) . "\n");
			//print_r($post_tags) ;
			$index++ ;
		}
		fclose($file_hahdler) ;
		echo( $wordress_host_from . "\n" ) ;
		echo( $wordress_host_to . "\n" ) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-posts.php posts.txt\n\n") ;
	}
?>