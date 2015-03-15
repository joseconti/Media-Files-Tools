<?php
/*
Plugin Name: Media File Size
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
	define( 'WANG_SIZE_MEDIA', 'beta 2' );
	define( 'WANG_SIZE_MEDIA_DOMAIN', 'wang_size_media') ;
	add_action('init', 'wang_size_media_init');
	function wang_size_media_init() {
		if (function_exists('load_plugin_textdomain')) {
			$plugin_dir = basename(dirname(__FILE__));
			load_plugin_textdomain(WANG_SIZE_MEDIA_DOMAIN, false, $plugin_dir . "/languages/" );
		}
	}
	function wang_size_media( $column ) {
    	$column['filesize']  = _x( 'File Size', WANG_SIZE_MEDIA_DOMAIN );
		return $column;
	}
	function wang_size_media_content( $column_name, $post_id ){
		$filesize = size_format( get_post_meta( $post_id, '_filesize', true ) );
		if ( $filesize ){
			if( 'filesize' == $column_name ) echo $filesize;
			} else { ?>
				<a href="<?php { echo esc_url( admin_url( add_query_arg( array( 'page' => 'wang_filesize' ), 'upload.php' ) ) ); }  ?>"><?php _e( 'Generate All Size', WANG_SIZE_MEDIA_DOMAIN ); ?></a>
			<?php }
	}
	function wang_size_media_content_sortable( $columns ){
    	$columns['filesize']  = '_filesize';
		return $columns;
	}
	function wang_size_media_metadata_generate( $image_data, $att_id ){
		$file  = get_attached_file( $att_id );
		$file_size = false;
		$file_size = filesize( $file );

		if ( ! empty( $file_size ) ) {
				//echo size_format( $file_size );
				update_post_meta( $att_id, '_filesize', $file_size );
			} else {
				update_post_meta( $att_id, '_filesize', 'N/D' );
			}
		return $image_data;
	}
	function wang_size_media_columns_sortable( $columns ){
    	$columns['filesize'] = '_filesize';
		return $columns;
	}
	function wang_size_media_columns_do_sort(&$query){
    	global $current_screen;

		if( 'upload' != $current_screen->id ) return;
		$is_filesize = (isset( $_GET['orderby'] ) && '_filesize' == $_GET['orderby']);
		if( !$is_filesize ) return;
		if ( '_filesize' == $_GET['orderby'] ){
        	$query->set('meta_key', '_filesize');
			$query->set('orderby', 'meta_value_num');
    	}
	}
	function wang_size_media_generate_all_size(){
   		global $wpdb;
   		$attachments = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment'" );
   		foreach( $attachments as $att ){
       		$file  = get_attached_file( $att->ID );
	   		$file_size = false;
	   		$file_size = filesize( $file );
	   		if ( ! empty( $file_size ) ) {
				update_post_meta( $att_id, '_filesize', $file_size );
			} else {
				update_post_meta( $att_id, '_filesize', 'N/D' );
			}
    	}
	}
	if( is_admin() ){
		add_filter( 'manage_media_custom_column', 'wang_size_media_content', 10, 2);
		add_filter( 'manage_upload_columns', 'wang_size_media' );
		add_filter( 'manage_upload_sortable_columns', 'wang_size_media_content_sortable' );
		add_filter( 'wp_generate_attachment_metadata', 'wang_size_media_metadata_generate', 10, 2);
		add_action( 'pre_get_posts', 'wang_size_media_columns_do_sort' );
	}

	function wang_size_media_menu() {
		$size_media = add_media_page( 'File Size', 'File Size', 'activate_plugins', 'wang_filesize', 'wang_size_media_wizard');
	}
	if( ! is_network_admin() ) add_action( 'admin_menu', 'wang_size_media_menu' );
	function wang_size_media_wizard(){
		global $wpdb;
		if ( !current_user_can('level_10') )
		die(__('Cheatin&#8217; uh?', WANG_SIZE_MEDIA_DOMAIN ));
		echo '<div class="wrap">';
		echo '<h2>' . __( 'Image Size' ) . '</h2>';
		$action = isset($_GET['action']) ? $_GET['action'] : 'show';
			switch ( $action ) {
				case "size":
					$attachments = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment'" ); ?>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php _e( 'File', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
								<th><?php _e( 'Size', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
								<th><?php _e( 'MIME Type', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
								<th><?php _e( 'State', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'File', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
								<th><?php _e( 'Size', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
								<th><?php _e( 'MIME Type', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
								<th><?php _e( 'State', WANG_SIZE_MEDIA_DOMAIN ) ?></th>
							</tr>
						</tfoot>
						<tbody><?php
							foreach( $attachments as $att ){
								$att_id = $att->ID;
								$file  = get_attached_file( $att_id );
								$filename_only = basename( get_attached_file( $att_id ) );
								$type = get_post_mime_type( $att_id );
								$file_size = false;
								$file_size = filesize( $file );
								$file_size_format = size_format( $file_size );

								if ( ! empty( $file_size ) ) {
									update_post_meta( $att_id, '_filesize', $file_size ); ?>
									<tr>
										<td><?php echo $filename_only; ?></td>
										<td><?php echo $type; ?></td>
										<td><?php echo $file_size_format; ?></td>
										<td>Done!</td>
									</tr><?php
								} else {
									update_post_meta( $att_id, '_filesize', 'N/D' ); ?>
									<tr>
										<td><?php echo $filename_only; ?></td>
										<td><?php echo 'Error'; ?></td>
										<td>Done!</td>
									</tr><?php
									}
							} ?>
						</tbody>
					</table><?php
				break;
				case 'show':
				default: ?>
					<p><?php _e( 'Update all files size can take a while.', WANG_SIZE_MEDIA_DOMAIN ); ?></p>
					<p><a class="button" href="admin.php?page=wang_filesize&action=size"><?php _e( 'Get Files Size', WANG_SIZE_MEDIA_DOMAIN ); ?></a></p><?php
				break;
			}
		}
?>