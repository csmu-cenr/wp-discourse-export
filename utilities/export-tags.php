<?php
	// print_r($args) ;
	if (count($args)>0) {
		$output_path = $args[0] ;
		$tags = get_tags() ;
		$index = 0 ;
		$file_handler = fopen($output_path, "w") or die("Unable to open $output_path!") ;
		$headers = array('term_id','name','slug','term_group','term_taxonomy_id','taxonomy','description','parent','count','filter') ;		
		fwrite($file_handler,implode("\t",$headers)."\n") ;
		foreach($tags as $tag){
			$fields = array( $tag->term_id,  $tag->name,  $tag->slug,  $tag->term_group,  $tag->term_taxonomy_id,  $tag->taxonomy,  $tag->description,  $tag->parent,  $tag->count,  $tag->filter ) ;
			fwrite($file_handler,implode("\t",$fields) . "\n") ;
			echo("$index\t" . implode("\t",$fields) . "\n");
			$index++ ;
		}
		fclose($file_handler) ;
	} else {
		echo("Please supply an output path as the first argumemt\nExample call \n\n /usr/bin/wp --allow-root --path=/var/www/html eval-file /var/www/html/wp-content/plugins/wp-discourse-export/utilities/export-tags.php tags.txt\n\n") ;
	}
?>