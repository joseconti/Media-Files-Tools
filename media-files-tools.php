<?php
/*
Plugin Name: Media Files Tool
Plugin URI: http://www.joseconti.com
Description: Add tools for media files.
Version: 1.1
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
	define( 'MEDIA_FILES_TOOLS_VERSION', '1.1' );
	add_action('init', 'media_files_tools_init');
	function media_files_tools_init() {
		if (function_exists('load_plugin_textdomain')) {
			$plugin_dir = basename(dirname(__FILE__));
			load_plugin_textdomain( 'media-file-tools', false, $plugin_dir . "/languages/" );
		}
	}
	function media_files_tools( $columns ) {
    	$columns['filesize']  = __( 'File Size', 'media-file-tools' );
    	$columns['mimetype']  = __( 'MiME Type', 'media-file-tools' );
		return $columns;
	}
	function media_files_tools_content( $column_name, $post_id ){
		$filesize = size_format( get_post_meta( $post_id, '_filesize', true ) );
		$filemime = get_post_meta( $post_id, '_filesmimetype', true );
		if ( $filesize ){
			if( 'filesize' == $column_name ) echo $filesize;
			} else {
				if( 'filesize' == $column_name ) { ?>
				<a href="<?php { echo esc_url( admin_url( add_query_arg( array( 'page' => 'wang_filesize' ), 'upload.php' ) ) ); }  ?>"><?php _e( 'Generate All Size', 'media-file-tools' ); ?></a>
			<?php }
			}
		if ( $filemime ){
			if( 'mimetype' == $column_name ) echo $filemime;
			} else {
				if( 'mimetype' == $column_name ){ ?>
					<a href="<?php { echo esc_url( admin_url( add_query_arg( array( 'page' => 'wang_filesize' ), 'upload.php' ) ) ); }  ?>"><?php _e( 'Generate All MIME Types', 'media-file-tools' ); ?></a>
			<?php }
				}
	}
	function media_files_tools_content_sortable( $columns ){
    	$columns['filesize'] = '_filesize';
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
        	$query->set('meta_key',	'_filesize');
			$query->set('orderby',	'meta_value_num');
    	}
	}
	function media_files_tools_mime_columns_do_sort(&$query){
    	global $current_screen;

		if( 'upload' != $current_screen->id ) return;
		$is_mimetype = (isset( $_GET['orderby'] ) && '_filesmimetype' == $_GET['orderby']);
		if( !$is_mimetype ) return;
		if ( '_filesmimetype' == $_GET['orderby'] ){
        	$query->set('meta_key',	'_filesmimetype');
			$query->set('orderby',	'meta_value');
    	}
	}
	if( is_admin() ){
		add_filter( 'manage_media_custom_column',		'media_files_tools_content', 10, 2);
		add_filter( 'manage_upload_columns',			'media_files_tools' );
		add_filter( 'manage_upload_sortable_columns',	'media_files_tools_content_sortable' );
		add_filter( 'wp_generate_attachment_metadata',	'media_files_tools_metadata_generate', 10, 2);
		add_action( 'pre_get_posts',					'media_files_tools_columns_do_sort' );
		add_action( 'pre_get_posts',					'media_files_tools_mime_columns_do_sort' );
	}

	function media_files_tools_menu() {
		$size_media = add_media_page( 'File Tools', 'File Tools', 'activate_plugins', 'wang_filesize', 'media_files_tools_wizard');
	}
	if( ! is_network_admin() ) add_action( 'admin_menu', 'media_files_tools_menu' );
	function media_files_tools_wizard(){
		global $wpdb;
		if ( !current_user_can('level_10') )
		die(__('Cheatin&#8217; uh?', 'media-file-tools' ));
		echo '<div class="wrap">';
		echo '<h2>' . __( 'File Size Generator', 'media-file-tools' ) . '</h2>';
		$action = isset($_GET['action']) ? $_GET['action'] : 'show';
			switch ( $action ) {
				case "size":
					$attachments = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment'" ); ?>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php _e( 'File',		'media-file-tools' ) ?></th>
								<th><?php _e( 'Size',		'media-file-tools' ) ?></th>
								<th><?php _e( 'MIME Type',	'media-file-tools' ) ?></th>
								<th><?php _e( 'State',		'media-file-tools' ) ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'File',		'media-file-tools' ) ?></th>
								<th><?php _e( 'Size',		'media-file-tools' ) ?></th>
								<th><?php _e( 'MIME Type',	'media-file-tools' ) ?></th>
								<th><?php _e( 'State',		'media-file-tools' ) ?></th>
							</tr>
						</tfoot>
						<tbody><?php
							foreach( $attachments as $att ){
								$att_id				= $att->ID;
								$file 				= get_attached_file( $att_id );
								$filename_only		= basename( get_attached_file( $att_id ) );
								$mimetype			= get_post_mime_type( $att_id );
								$file_size			= false;
								$file_size			= filesize( $file );
								$file_size_format	= size_format( $file_size );

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
					<p><?php _e( 'Update all files size can take a while.', 'media-file-tools' ); ?></p>
					<p><a class="button" href="admin.php?page=wang_filesize&action=size"><?php _e( 'Get Files Size', 'media-file-tools' ); ?></a></p><?php
				break;
			}
		}
// Add Featured Image to post list

	function media_files_tools_post_list( $columns ) {
    	$columns['featured_image'] = __( 'Featured Image', 'media-file-tools' );
		return $columns;
	}
	add_filter('manage_posts_columns',		'media_files_tools_post_list');
	add_filter('manage_page_posts_columns', 'media_files_tools_post_list');
	function media_files_tools_post_content( $column_name, $post_ID) {
		if ($column_name == 'featured_image') {
			$post_thumbnail_id = get_post_thumbnail_id( $post_ID );
			if ( $post_thumbnail_id ){
				$media_files_tools_nonce	= wp_create_nonce( 'set_post_thumbnail-' . $post_ID );
	    		$post_featured_image		= wp_get_attachment_image( $post_thumbnail_id, array( 80, 60 ), true );
	    		$thumbnail_html				= wp_get_attachment_image( $post_thumbnail_id, 'post-thumbnail' );
	    		echo $post_featured_image;
	    		$upload_iframe_src			= esc_url( get_upload_iframe_src('image', $post_ID ) );
				$set_thumbnail_link			= '<p class="hide-if-no-js"><div class="row-actions"><a title="' . esc_attr__( 'Change Featured Image', 'media-file-tools' ) . '" href="%1$s" id="set-post-thumbnail" class="media_files_tools_thickbox" data-thumbnail-id="%2$s">%3$s</a></div></p>';
				$content = sprintf( $set_thumbnail_link, $upload_iframe_src . '&_wpnonce=' . $media_files_tools_nonce, $post_thumbnail_id, esc_html__( 'Change featured image', 'media-file-tools' ) );
				echo $content;
				echo $content2;
	    	}else{
		    	$url = includes_url();
				$defaultimageurl			= $url . 'images/media/default.png';
				$media_files_tools_nonce	= wp_create_nonce( 'set_post_thumbnail-' . $post_ID );
				$upload_iframe_src			= esc_url( get_upload_iframe_src('image', $post_ID ) );
				$set_thumbnail_link			= '<p class="hide-if-no-js"><div class="row-actions"><a title="' . esc_attr__( 'Set featured image', 'media-file-tools' ) . '" href="%1$s" id="set-post-thumbnail" class="media_files_tools_thickbox" id="set-post-thumbnail">%2$s</a></div></p>';
				$content					= sprintf( $set_thumbnail_link, $upload_iframe_src . '&_wpnonce=' . $media_files_tools_nonce, esc_html__( 'Set featured image', 'media-file-tools' ) );
				echo '<img src="' . $defaultimageurl . '" />';
				echo $content;
	    	}
		}
	}
	function media_files_tools_load_js(){
		//add_thickbox();
        wp_enqueue_media();
		wp_enqueue_script('media_files_tools_load_js' , "/" . PLUGINDIR . '/media-files-tools/js/uploader.js' , array('jquery'),'2.0');
        wp_enqueue_script('media_files_tools_load_js');
        $translation_array = array(
			'mediaManager'	=> __( 'Featured Image', 'media-file-tools' ),
			'textButton'	=> __( 'Use as Featured Image', 'media-file-tools' )
			);
		wp_localize_script( 'media_files_tools_load_js', 'media_files_localize', $translation_array );
	}
	function media_files_tools_update_featured_image() {

        // Get the post id we are to attach the image to
        $post_ID = intval( $_POST['post_id'] );
        if ( ! current_user_can( 'edit_post', $post_ID ) )
            wp_die( -1 );

        // Check who's calling us before proceeding
        check_ajax_referer( 'set_post_thumbnail-' . $post_ID, $media_files_tools_nonce );

        // Get thumbnail ID so we can then get html src to use for thumbnail
        $thumbnail_id = intval( $_POST['thumbnail_id'] );
        $thumb_url = wp_get_attachment_image( $thumbnail_id, array( 80, 60 ), true );
        echo $thumb_url;

        die();
    }
    add_action( 'wp_ajax_media_files_tools_update_featured_image',	'media_files_tools_update_featured_image');
	add_action('admin_print_scripts-edit.php',						'media_files_tools_load_js');
	add_action('admin_enqueue_scripts',								'media_files_tools_load_js');
	add_action('manage_posts_custom_column',						'media_files_tools_post_content', 10, 2);
	add_action('manage_page_posts_custom_column',					'media_files_tools_post_content', 10, 2);
?>