# Work in Progress: Do not install on a live site

# To export using wp command line utilities to use with Discourse Importers

[Make sure wp command line tool ghas been installed.][1]
 	
##tags
wp --allow-root eval-file wp-content/plugins/wp-discourse-export/utilities/export-tags.php tags.txt

##users
wp --allow-root eval-file wp-content/plugins/wp-discourse-export/utilities/export-users.php users.txt

##categories
wp --allow-root eval-file wp-content/plugins/wp-discourse-export/utilities/export-categories.php categories.txt

##topics
wp --allow-root eval-file wp-content/plugins/wp-discourse-export/utilities/export-topis.php topics.txt

##posts
wp --allow-root eval-file wp-content/plugins/wp-discourse-export/utilities/export-posts.php posts.txt


[1]: https://make.wordpress.org/cli/handbook/installing