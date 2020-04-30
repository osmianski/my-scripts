<?php
/* @var string $path */
/* @var string $domain */
/* @var string $snippet_filename */
?>

server {
	listen 80;
	listen [::]:80;
    server_name <?php echo $domain ?>;

    set $MAGE_ROOT <?php echo $path ?>;
    include <?php echo $snippet_filename ?>;
}