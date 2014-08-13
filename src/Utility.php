<?php

	namespace JX\Cns;

	class Utility {

		public function fetchNext($path, $current) {
			$ret = '';

			$gotCurrent = false;
			$first = '';
			$items = $this->getFilesAndDirs( $path );
			foreach( $items['files'] as $file ) {
				// Catch the first item of the folder
				if ( $first == '' )
					$first = $file;

				// We got which was the current,
				// so we now get the next one
				if ( $gotCurrent == true && is_file( $path . '/' . $file ) ) {
					$ret = $file;
					$gotCurrent = false;
				}

				if ( $current == basename( $file ) )
					$gotCurrent = true;
			}
			if ( $ret == '' ) $ret = $first;

			return $ret;
		}

		public function getFilename($path) {
			$ret = '';

			$tmp = explode('/', urldecode( $path ) );
			$ret = array_pop( $tmp );

			return $ret;
		}

		public function getFilesAndDirs($path) {
			$ret = array(
				'dirs' => array(),
				'files' => array(),
			);

			$items = preg_grep( '/^([^.Thumbs])/', scandir( $path ) );
			foreach( $items as $item ) {
				$fullpath = $path . '/' . $item;

				if ( is_file( $fullpath ) )
					array_push( $ret['files'], $item );
				else if ( is_dir( $fullpath ) )
					array_push( $ret['dirs'], $item );
			}

			return $ret;
		}

		public function getImageProperties($path) {
			return getimagesize( urldecode( $path ) );
		}

	}