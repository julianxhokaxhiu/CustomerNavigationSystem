<?php

	namespace JX\Cns;

	class Utility {

		public function fetchNext($path, $current) {
			$ret = '';

			$gotCurrent = false;
			$first = '';
			$files = preg_grep( '/^([^.])/', scandir( $path ) );
			foreach( $files as $file ) {
				// Catch the first item of the folder
				if ( $first == '' )
					$first = $file;

				// We got which was the current,
				// so we now get the next one
				if ( $gotCurrent == true ) {
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

			$tmp = explode('/', $path);
			$ret = array_pop( $tmp );

			return $ret;
		}

	}