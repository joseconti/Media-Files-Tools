<?php
/*
Plugin Name: Media Files Tool
Plugin URI: http://URI_De_La_Página_Que_Describe_el_Plugin_y_Actualizaciones
Description: Una breve descripción del plugin.
Version: beta 2
Author: j.conti
Author URI: http://www.joseconti.com
License: GPL2
*/
/*  Copyright AÑO NOMBRE_AUTOR_PLUGIN  (email : EMAIL DEL AUTOR DEL PLUGIN)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
	define( 'MEDIA_FILES_TOOLS_VERSION', 'beta 2' );
	define( 'MEDIA_FILES_TOOLS', 'media-file-tools') ;
	add_action('init', 'media_files_tools_init');
	function media_files_tools_init() {
		if (function_exists('load_plugin_textdomain')) {
			$plugin_dir = basename(dirname(__FILE__));
			load_plugin_textdomain( MEDIA_FILES_TOOLS, false, $plugin_dir . "/languages/" );
		}
	}
	function media_files_tools( $column ) {
    	$column['filesize']  = _x( 'File Size', MEDIA_FILES_TOOLS );
    	$column['mimetype']  = _x( 'MiME Type', MEDIA_FILES_TOOLS );
		return $column;
	}
	function media_files_tools_content( $column_name, $post_id ){
		$filesize = size_format( get_post_meta( $post_id, '_filesize', true ) );
		$filemime = get_post_meta( $post_id, '_filesmimetype', true );
		if ( $filesize ){
			if( 'filesize' == $column_name ) echo $filesize;
			} else {
				if( 'filesize' == $column_name ) { ?>
				<a href="<?php { echo esc_url( admin_url( add_query_arg( array( 'page' => 'wang_filesize' ), 'upload.php' ) ) ); }  ?>"><?php _e( 'Generate All Size', MEDIA_FILES_TOOLS ); ?></a>
			<?php }
			}
		if ( $filemime ){
			if( 'mimetype' == $column_name ) echo $filemime;
			} else {
				if( 'mimetype' == $column_name ){ ?>
					<a href="<?php { echo esc_url( admin_url( add_query_arg( array( 'page' => 'wang_filesize' ), 'upload.php' ) ) ); }  ?>"><?php _e( 'Generate All Size', MEDIA_FILES_TOOLS ); ?></a>
			<?php }
				}
	}
	function media_files_tools_content_sortable( $columns ){
    	$columns['filesize']  = '_filesize';
    	$columns['mimetype'] = '_filesmimetype';
		return $columns;
	}
	function media_files_tools_metadata_generate( $image_data, $att_id ){
       		$file  = get_attached_file( $att_id );
	   		$file_size = false;
	   		$file_size = filesize( $file );
	   		$file_mime_type = get_post_mime_type( $att_id );
	   		if ( ! empty( $file_size ) ) {
				update_post_meta( $att_id, '_filesize', $file_size );
			} else {
				update_post_meta( $att_id, '_filesize', 'N/D' );
			}
			if ( ! empty( $file_mime_type ) ) {
				update_post_meta( $att_id, '_filesmimetype', $file_mime_type );
			} else {
				update_post_meta( $att_id, '_filesmimetype', 'N/D' );
			}
		return $image_data;
	}
	function media_files_tools_columns_do_sort(&$query){
    	global $current_screen;

		if( 'upload' != $current_screen->id ) return;
		$is_filesize = (isset( $_GET['orderby'] ) && '_filesize' == $_GET['orderby']);
		if( !$is_filesize ) return;
		if ( '_filesize' == $_GET['orderby'] ){
        	$query->set('meta_key', '_filesize');
			$query->set('orderby', 'meta_value_num');
    	}
	}
	function media_files_tools_mime_columns_do_sort(&$query){
    	global $current_screen;

		if( 'upload' != $current_screen->id ) return;
		$is_mimetype = (isset( $_GET['orderby'] ) && '_filesmimetype' == $_GET['orderby']);
		if( !$is_mimetype ) return;
		if ( '_filesmimetype' == $_GET['orderby'] ){
        	$query->set('meta_key', '_filesmimetype');
			$query->set('orderby', 'meta_value');
    	}
	}
	if( is_admin() ){
		add_filter( 'manage_media_custom_column', 'media_files_tools_content', 10, 2);
		add_filter( 'manage_upload_columns', 'media_files_tools' );
		add_filter( 'manage_upload_sortable_columns', 'media_files_tools_content_sortable' );
		add_filter( 'wp_generate_attachment_metadata', 'media_files_tools_metadata_generate', 10, 2);
		add_action( 'pre_get_posts', 'media_files_tools_columns_do_sort' );
		add_action( 'pre_get_posts', 'media_files_tools_mime_columns_do_sort' );
	}

	function media_files_tools_menu() {
		$size_media = add_media_page( 'File Tools', 'File Tools', 'activate_plugins', 'wang_filesize', 'media_files_tools_wizard');
	}
	if( ! is_network_admin() ) add_action( 'admin_menu', 'media_files_tools_menu' );
	function media_files_tools_wizard(){
		global $wpdb;
		if ( !current_user_can('level_10') )
		die(__('Cheatin&#8217; uh?', MEDIA_FILES_TOOLS ));
		echo '<div class="wrap">';
		echo '<h2>' . __( 'File Size Generator' ) . '</h2>';
		$action = isset($_GET['action']) ? $_GET['action'] : 'show';
			switch ( $action ) {
				case "size":
					$attachments = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment'" ); ?>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php _e( 'File', MEDIA_FILES_TOOLS ) ?></th>
								<th><?php _e( 'Size', MEDIA_FILES_TOOLS ) ?></th>
								<th><?php _e( 'MIME Type', MEDIA_FILES_TOOLS ) ?></th>
								<th><?php _e( 'State', MEDIA_FILES_TOOLS ) ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'File', MEDIA_FILES_TOOLS ) ?></th>
								<th><?php _e( 'Size', MEDIA_FILES_TOOLS ) ?></th>
								<th><?php _e( 'MIME Type', MEDIA_FILES_TOOLS ) ?></th>
								<th><?php _e( 'State', MEDIA_FILES_TOOLS ) ?></th>
							</tr>
						</tfoot>
						<tbody><?php
							foreach( $attachments as $att ){
								$att_id = $att->ID;
								$file  = get_attached_file( $att_id );
								$filename_only = basename( get_attached_file( $att_id ) );
								$mimetype = get_post_mime_type( $att_id );
								$file_size = false;
								$file_size = filesize( $file );
								$file_size_format = size_format( $file_size );

								if ( ! empty( $file_size ) ) {
									update_post_meta( $att_id, '_filesize', $file_size );
									update_post_meta( $att_id, '_filesmimetype', $mimetype ); ?>
									<tr>
										<td><?php echo $filename_only; ?></td>
										<td><?php echo $file_size_format; ?></td>
										<td><?php echo $mimetype; ?></td>
										<td>Done!</td>
									</tr><?php
								} else {
									update_post_meta( $att_id, '_filesize', 'N/D' );
									update_post_meta( $att_id, '_filesmimetype', $mimetype ); ?>
									<tr>
										<td><?php echo $filename_only; ?></td>
										<td><?php echo 'Error'; ?></td>
										<td><?php echo $mimetype; ?></td>
										<td>Done!</td>
									</tr><?php
									}
							} ?>
						</tbody>
					</table><?php
				break;
				case 'show':
				default: ?>
					<p><?php _e( 'Update all files size can take a while.', MEDIA_FILES_TOOLS ); ?></p>
					<p><a class="button" href="admin.php?page=wang_filesize&action=size"><?php _e( 'Get Files Size', MEDIA_FILES_TOOLS ); ?></a></p><?php
				break;
			}
		}
// Add Featured Image to post list

	function media_files_tools_post_list( $columns ) {
    	$columns['featured_image'] = _x( 'Featured Image', MEDIA_FILES_TOOLS );
		return $columns;
	}
	add_filter('manage_posts_columns', 'media_files_tools_post_list');
	add_filter('manage_page_posts_columns', 'media_files_tools_post_list');
	function media_files_tools_post_content( $column_name, $post_ID) {
		if ($column_name == 'featured_image') {
			$post_thumbnail_id = get_post_thumbnail_id($post_ID);
			if ( $post_thumbnail_id ){
	    		$post_featured_image = wp_get_attachment_image( $post_thumbnail_id, array( 80, 60 ), true );
	    		echo $post_featured_image;
	    	}else{
		    	$url = includes_url();
				$defaultimageurl = $url . 'images/media/default.png';
				echo '<img src="' . $defaultimageurl . '" />';
	    	}
		}
	}
	add_action('manage_posts_custom_column', 'media_files_tools_post_content', 10, 2);
	add_action('manage_page_posts_custom_column', 'media_files_tools_post_content', 10, 2);
?>