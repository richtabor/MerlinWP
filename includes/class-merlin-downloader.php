<?php
/**
 * Class for downloading a file from a given URL.
 *
 * @package Merlin WP
 */

class Merlin_Downloader {
	/**
	 * Holds full path to where the files will be saved.
	 *
	 * @var string
	 */
	private $download_directory_path = '';

	/**
	 * Constructor method.
	 *
	 * @param string $download_directory_path Full path to where the files will be saved.
	 */
	public function __construct( $download_directory_path = '' ) {
		$this->set_download_directory_path( $download_directory_path );
	}


	/**
	 * Download file from a given URL.
	 *
	 * @param string $url URL of file to download.
	 * @param string $filename Filename of the file to save.
	 * @return string|WP_Error Full path to the downloaded file or WP_Error object with error message.
	 */
	public function download_file( $url, $filename ) {
		$content = $this->get_content_from_url( $url );

		// Check if there was an error and break out.
		if ( is_wp_error( $content ) ) {
			Merlin_Logger::get_instance()->error( $content->get_error_message(), array( 'url' => $url, 'filename' => $filename ) );

			return $content;
		}

		$saved_file = file_put_contents( $this->download_directory_path . $filename, $content );

		if ( ! empty( $saved_file ) ) {
			return $this->download_directory_path . $filename;
		}

		Merlin_Logger::get_instance()->error( __( 'The file was not able to save to disk, while trying to download it', '@@textdomain' ), array( 'url' => $url, 'filename' => $filename ) );

		return false;
	}


	/**
	 * Helper function: get content from an URL.
	 *
	 * @param string $url URL to the content file.
	 * @return string|WP_Error, content from the URL or WP_Error object with error message.
	 */
	private function get_content_from_url( $url ) {
		// Test if the URL to the file is defined.
		if ( empty( $url ) ) {
			return new \WP_Error(
				'missing_url',
				__( 'Missing URL for downloading a file!', '@@textdomain' )
			);
		}

		// Get file content from the server.
		$response = wp_remote_get(
			$url,
			array( 'timeout' => apply_filters( 'merlin_timeout_for_downloading_import_file', 20 ) )
		);

		// Test if the get request was not successful.
		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
			// Collect the right format of error data (array or WP_Error).
			$response_error = $this->get_error_from_response( $response );

			return new \WP_Error(
				'download_error',
				sprintf(
					__( 'An error occurred while fetching file from: %1$s%2$s%3$s!%4$sReason: %5$s - %6$s.', '@@textdomain' ),
					'<strong>',
					$url,
					'</strong>',
					'<br>',
					$response_error['error_code'],
					$response_error['error_message']
				)
			);
		}

		// Return content retrieved from the URL.
		return wp_remote_retrieve_body( $response );
	}


	/**
	 * Helper function: get the right format of response errors.
	 *
	 * @param array|WP_Error $response Array or WP_Error or the response.
	 * @return array Error code and error message.
	 */
	private function get_error_from_response( $response ) {
		$response_error = array();

		if ( is_array( $response ) ) {
			$response_error['error_code']    = $response['response']['code'];
			$response_error['error_message'] = $response['response']['message'];
		}
		else {
			$response_error['error_code']    = $response->get_error_code();
			$response_error['error_message'] = $response->get_error_message();
		}

		return $response_error;
	}


	/**
	 * Get download_directory_path attribute.
	 */
	public function get_download_directory_path() {
		return $this->download_directory_path;
	}


	/**
	 * Set download_directory_path attribute.
	 * If no valid path is specified, the default WP upload directory will be used.
	 *
	 * @param string $download_directory_path Path, where the files will be saved.
	 */
	public function set_download_directory_path( $download_directory_path ) {
		if ( file_exists( $download_directory_path ) ) {
			$this->download_directory_path = $download_directory_path;
		}
		else {
			$upload_dir = wp_upload_dir();
			$this->download_directory_path = apply_filters( 'merlin_upload_file_path', trailingslashit( $upload_dir['path'] ) );
		}
	}

	/**
	 * Check, if the file already exists and return his full path.
	 *
	 * @param string $filename The name of the file.
	 *
	 * @return bool|string
	 */
	public function fetch_existing_file( $filename ) {
		if ( file_exists( $this->download_directory_path . $filename ) ) {
			return $this->download_directory_path . $filename;
		}

		return false;
	}
}
