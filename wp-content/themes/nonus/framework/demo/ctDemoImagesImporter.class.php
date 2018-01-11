<?php

/**
 * Imports attachemnts via ajax
 * @see Wordpress Attachemnt Importer for details
 * @author alex
 */
class ctDemoImagesImporter {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ), 11 );
	}

	public function init() {
		add_action( 'wp_ajax_ct-demo-images-xml', array( 'ctDemoImagesImporter', 'handleCtDemoImagesXml' ) );
		add_action( 'wp_ajax_ct-demo-images-upload', array( 'ctDemoImagesImporter', 'handleCtDemoImagesUpload' ) );
		add_action( 'wp_ajax_ct-demo-images-upload-completed', array(
			'ctDemoImagesImporter',
			'handleCtDemoImagesUploadCompleted'
		) );

		/** @var ctNHP_Options $NHP_OPtions */
		global $NHP_Options;
		add_action(
			'nhp-opts-load-page-' . $NHP_Options->getOptionsPageName(),
			array( $this, 'optionsPage' )
		);
	}

	public function optionsPage() {
		wp_enqueue_style( 'ct_jquery_ui', CT_THEME_ADMIN_ASSETS_URI . '/css/jquery.ui/jquery-ui.css' );

		wp_localize_script( 'ct_import_demo',
			'ctSecurity',
			array(
				'nonce' => wp_create_nonce( 'ct-demo-import' )
			) );
		wp_localize_script( 'ct_import_demo',
			'ctL10n',
			array(
				'emptyInput'    => __( 'Please select a file.', 'attachment-importer' ),
				'noAttachments' => __( 'There were no attachment files found in the import file.',
					'ct_theme' ),
				'parsing'       => __( 'Parsing the file.', 'attachment-importer' ),
				'importing'     => __( 'Importing file ', 'attachment-importer' ),
				'progress'      => __( 'Overall progress: ', 'attachment-importer' ),
				'retrying'      => __( 'An error occured. In 5 seconds, retrying file ', 'attachment-importer' ),
				'done'          => __( 'All done!', 'attachment-importer' ),
				'ajaxFail'      => __( 'There was an error connecting to the server.', 'attachment-importer' ),
				'pbAjaxFail'    => __( 'The program could not run. Check the error log below or your JavaScript console for more information',
					'ct_theme' ),
				'fatalUpload'   => __( 'There was a fatal error. Check the last entry in the error log below.',
					'ct_theme' )
			) );
	}

	/**
	 * Echos import xml
	 */
	public static function handleCtDemoImagesXml() {
		if ( ! check_ajax_referer( 'ct-demo-import', false, false ) ) {
			$nonce_error = new WP_Error( 'nonce_error',
				__( 'Are you sure you want to do this?', 'ct_theme' ) );
			//no escape required
			echo json_encode( array(
					'fatal'   => true,
					'type'    => 'error',
					'code'    => $nonce_error->get_error_code(),
					'message' => $nonce_error->get_error_message(),
					'text'    => __( 'Nonce error. Please try again.)',
						'ct_theme' ),
					$nonce_error->get_error_code(),
					$nonce_error->get_error_message()
				)
			);
			die();
		}

		$xmlPath = ctImport::getXmlPath();
		//no escape required
		echo file_get_contents( $xmlPath );
		exit;
	}

	/**
	 * All images imported
	 */

	public static function handleCtDemoImagesUploadCompleted() {
		do_action( 'ct_import.images.completed' );
	}

	/**
	 * Import image
	 */

	public static function handleCtDemoImagesUpload() {
// check nonce before doing anything else
		if ( ! check_ajax_referer( 'ct-demo-import', false, false ) ) {
			$nonce_error = new WP_Error( 'nonce_error',
				__( 'Are you sure you want to do this?', 'ct_theme' ) );
			//no escape required
			echo json_encode( array(
					'fatal'   => true,
					'type'    => 'error',
					'code'    => $nonce_error->get_error_code(),
					'message' => $nonce_error->get_error_message(),
					'text'    => __( 'Nonce error. Please try again.)',
						'ct_theme' ),
					$nonce_error->get_error_code(),
					$nonce_error->get_error_message()
				)
			);
			die();
		}

		$parameters = array(
			'url'            => $_POST['url'],
			'post_title'     => $_POST['title'],
			'link'           => $_POST['link'],
			'pubDate'        => $_POST['pubDate'],
			'post_author'    => $_POST['creator'],
			'guid'           => $_POST['guid'],
			'import_id'      => $_POST['post_id'],
			'post_date'      => $_POST['post_date'],
			'post_date_gmt'  => $_POST['post_date_gmt'],
			'comment_status' => $_POST['comment_status'],
			'ping_status'    => $_POST['ping_status'],
			'post_name'      => $_POST['post_name'],
			'post_status'    => $_POST['status'],
			'post_parent'    => $_POST['post_parent'],
			'menu_order'     => $_POST['menu_order'],
			'post_type'      => $_POST['post_type'],
			'post_password'  => $_POST['post_password'],
			'is_sticky'      => $_POST['is_sticky'],
		);

		function process_attachment( $post, $url ) {

			$pre_process = pre_process_attachment( $post, $url );
			if ( is_wp_error( $pre_process ) ) {
				return array(
					'fatal'   => false,
					'type'    => 'error',
					'code'    => $pre_process->get_error_code(),
					'message' => $pre_process->get_error_message(),
					'text'    => sprintf( __( '%1$s was not uploaded. (<strong>%2$s</strong>: %3$s)',
							'ct_theme' ),
						$post['post_title'],
						$pre_process->get_error_code(),
						$pre_process->get_error_message() )
				);
			}

			// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
			/*if ( preg_match( '|^/[\w\W]+$|', $url ) ) {
				$url = rtrim( $this->base_url, '/' ) . $url;
			}*/

			$upload = fetch_remote_file( $url, $post );
			if ( is_wp_error( $upload ) ) {
				return array(
					'fatal'   => ( $upload->get_error_code() == 'upload_dir_error' && $upload->get_error_message() != 'Invalid file type' ? true : false ),
					'type'    => 'error',
					'code'    => $upload->get_error_code(),
					'message' => $upload->get_error_message(),
					'text'    => sprintf( __( '%1$s could not be uploaded because of an error. (<strong>%2$s</strong>: %3$s)',
							'ct_theme' ),
						$post['post_title'],
						$upload->get_error_code(),
						$upload->get_error_message() )
				);
			}

			if ( $info = wp_check_filetype( $upload['file'] ) ) {
				$post['post_mime_type'] = $info['type'];
			} else {
				$upload = new WP_Error( 'attachment_processing_error',
					__( 'Invalid file type', 'ct_theme' ) );

				return array(
					'fatal'   => false,
					'type'    => 'error',
					'code'    => $upload->get_error_code(),
					'message' => $upload->get_error_message(),
					'text'    => sprintf( __( '%1$s could not be uploaded because of an error. (<strong>%2$s</strong>: %3$s)',
							'ct_theme' ),
						$post['post_title'],
						$upload->get_error_code(),
						$upload->get_error_message() )
				);
			}

			$post['guid']        = $upload['url'];
			$post['post_author'] = (int) wp_get_current_user()->ID;

			// as per wp-admin/includes/upload.php
			$post_id = wp_insert_attachment( $post, $upload['file'] );
			wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

			update_post_meta( $post_id, 'previous_id', $post['import_id'] );

			// remap image URL's
			backfill_attachment_urls( $url, $upload['url'] );

			return array(
				'fatal' => false,
				'type'  => 'updated',
				'text'  => sprintf( __( '%s was uploaded successfully', 'ct_theme' ), $post['post_title'] )
			);
		}

		function pre_process_attachment( $post, $url ) {
			global $wpdb;

			$imported = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT ID, post_date_gmt, guid
				FROM $wpdb->posts
				WHERE post_type = 'attachment'
					AND post_title = %s
				",
					$post['post_title']
				)
			);

			if ( $imported ) {
				foreach ( $imported as $attachment ) {
					if ( basename( $url ) == basename( $attachment->guid ) ) {
						if ( $post['post_date_gmt'] == $attachment->post_date_gmt ) {
							$headers = wp_get_http( $url );
							if ( filesize( get_attached_file( $attachment->ID ) ) == $headers['content-length'] ) {
								return new WP_Error( 'Error',
									__( 'File already exists', 'ct_theme' ) );
							}
						}
					}
				}
			}

			return false;
		}

		function fetch_remote_file( $url, $post ) {

			// extract the file name and extension from the url
			$file_name = basename( $url );

			// get placeholder file in the upload dir with a unique, sanitized filename
			$upload = wp_upload_bits( $file_name, 0, '', $post['post_date'] );
			if ( $upload['error'] ) {
				return new WP_Error( 'upload_dir_error', $upload['error'] );
			}

			// fetch the remote url and write it to the placeholder file
			$headers = wp_get_http( $url, $upload['file'] );

			// request failed
			if ( ! $headers ) {
				@unlink( $upload['file'] );

				return new WP_Error( 'import_file_error',
					__( 'Remote server did not respond', 'ct_theme' ) );
			}

			// make sure the fetch was successful
			if ( $headers['response'] != '200' ) {
				@unlink( $upload['file'] );

				return new WP_Error( 'import_file_error',
					sprintf( __( 'Remote server returned error response %1$d %2$s', 'ct_theme' ),
						esc_html( $headers['response'] ),
						get_status_header_desc( $headers['response'] ) ) );
			}

			$filesize = filesize( $upload['file'] );

			if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
				@unlink( $upload['file'] );

				return new WP_Error( 'import_file_error',
					__( 'Remote file is incorrect size', 'ct_theme' ) );
			}

			if ( 0 == $filesize ) {
				@unlink( $upload['file'] );

				return new WP_Error( 'import_file_error', __( 'Zero size file downloaded', 'ct_theme' ) );
			}

			return $upload;
		}

		function backfill_attachment_urls( $from_url, $to_url ) {
			global $wpdb;
			// remap urls in post_content
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE {$wpdb->posts}
					SET post_content = REPLACE(post_content, %s, %s)
				",
					$from_url,
					$to_url
				)
			);
			// remap enclosure urls
			$result = $wpdb->query(
				$wpdb->prepare(
					"
					UPDATE {$wpdb->postmeta}
					SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'
				",
					$from_url,
					$to_url
				)
			);
		}

		if ( ! empty( $parameters['attachment_url'] ) ) {
			$remote_url = $parameters['attachment_url'];
		} elseif ( ! empty( $parameters['url'] ) ) {
			$remote_url = $parameters['url'];
		} else {
			$remote_url = $parameters['guid'];
		}
		//no escape required
		echo json_encode( process_attachment( $parameters, $remote_url ) );

		die();
	}
}

if ( is_admin() ) {
	new ctDemoImagesImporter();
}