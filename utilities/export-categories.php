<?php
	// print_r($args) ;
	if (count($args)>0) {
		$output_path = $args[0] ;
		$categories = get_categories() ;
		$categories_hash = array() ;
		$index = 0 ;
		$file_handler = fopen($output_path, "w") or die("Unable to open $output_path!") ;
		$headers = null ;
		
		foreach($categories as $category){
			$categories_hash['term_id'] = $category ;
		}
		echo( 'categories.count: ' . count($categories) . "\n" ) ;
		foreach($categories as $category){
			if ( $headers == null ) {
				$headers = array() ;
				foreach($category as $key => $value ){
					$headers[] = $key ;
				}
				fwrite($file_handler,implode("\t",$headers)."\n") ;
			}  
			
			$fields = array() ;
			foreach($category as $key => $value ){
				if (strpos($category->$key,"\n") > 0 ) {
					$fields[] = str_replace("\n", '\n', $category->$key) ;
				} else {
					$fields[] = $category->$key ;						
				}
			}
			fwrite($file_handler,implode("\t",$fields) . "\n") ;
			
			$index++ ;
		}
		fclose($file_handler) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-categories.php categories.txt\n\n") ;
	}
?>