<?php

	namespace JX\Cns;

	use \Flight;
	use \DotNotation;

	class Cns {

		/**
		 * Constructor of the class
		 * @return type
		 */
		public function __construct() {
			$this->initConfig();
			$this->initUtility();
			$this->initRoutes();
		}

		/**
         * Get the global configuration
         * @return array The whole configuration until this moment
         */
        public function getConfig() {
            return Flight::cfg()->get();
        }

        /**
         * Set a configuration option based on a key
         * @param type $key The key of your configuration
         * @param type $value The value that you want to set
         * @return class Return always itself, so it can be chained within calls
         */
        public function setConfig( $key, $value ) {
            Flight::cfg()->set( $key, $value );

            return $this;
        }

        /**
         * Enables Twig Cache system
         * @return class Return always itself, so it can be chained within calls
         */
        public function enableCache() {
        	Flight::cfg()->set('twig', array(
        		'cache'	=>	Flight::cfg()->get('app.realBasePath') . '/cache',
        	));

        	return $this;
        }

        /**
         * Enables Twig Debug system ( mainly dump function inside templates )
         * @return class Return always itself, so it can be chained within calls
         */
        public function enableDebug() {
        	Flight::cfg()->set('twig', array(
        		'debug' => true
        	));

        	return $this;
        }

        /**
         * Run the application
         * @return class Return always itself, so it can be chained within calls
         */
		public function run() {
			$this->initTwig();

			Flight::start();

			return $this;
		}

		/* Utility / Internal */

		/**
		 * Initialize Twig engine and register it into the Flight Microframework
		 */
		private function initTwig() {
			// Prepare twig and it's data configurations
			$loader = new \Twig_Loader_Filesystem( Flight::cfg()->get('app.realBasePath') . '/www' );
			$twigConfig = array_merge( array(
				'cache' => false,
				'debug'	=>	false,
				'auto_reload' => true,
			), Flight::cfg()->get('twig') );

			// Override the Flight view method with Twig
			Flight::register('view', '\Twig_Environment', array($loader, $twigConfig), function($twig) {
				if ( Flight::cfg()->get('twig.debug') ) $twig->addExtension( new \Twig_Extension_Debug() ); // Add the debug extension
			});

			// And also the render method
			Flight::map('render', function( $template, $data ){
				Flight::cfg()->add(
					'data',
					array_merge($data, array(
						'basePath' => Flight::cfg()->get('app.basePath') . '/www',
					))
				);

			    Flight::view()->display( $template , array(
				    'app' => Flight::cfg()->get('data')
				));
			});
		}

		/**
         * Register the config array into the Flight Microframework
         */
        private function initConfig() {
            Flight::register( 'cfg', '\DotNotation', array(), function( $cfg ) {
                $cfg->set( 'app.basePath', '' );
                $cfg->set( 'app.realBasePath', realpath( __DIR__ . '/..' ) );
                $cfg->set( 'twig', array() );
            });
        }

        /**
         * Register the utility functions into the Flight Microframework
         */
        private function initUtility() {
        	Flight::register( 'h', '\JX\Cns\Utility', array() );
        }

        /**
         * Initialize the routing engine.
         * Here we manage the path navigations and the "gallery" clickable image.
         */
		private function initRoutes() {
			Flight::route( '/*', function($route) {
				$realPath = realpath( Flight::cfg()->get('app.realBasePath') . '/www/img/' . urldecode( $route->splat ) );

				if ( is_dir($realPath) && file_exists($realPath) ) {

					// Cycle through directories an show a list to the user
					$items = Flight::h()->getFilesAndDirs( $realPath );

					// Collect meta information about files
					$ret = array();
					// Collect info for directories
					foreach( $items['dirs'] as $dir ) {
						$fullpath = $realPath . '/' . $dir;
						$currentItemElements = Flight::h()->getFilesAndDirs( $fullpath );

						array_push( $ret, array(
							'name' => $dir,
							'path' => $dir . '/',
							'type' => 'directory',
							'count' => ( count( $currentItemElements['files'] ) + count( $currentItemElements['dirs'] ) ),
						));
					}
					// Collect info for files
					foreach( $items['files'] as $dir ) {
						$fullpath = $realPath . '/' . $dir;
						array_push( $ret, array(
							'name' => $dir,
							'type' => 'file'
						));
					}

					// Render the current directory items
					Flight::render( 'list.twig', array(
						'items' => $ret,
						'currentPath' => urldecode( $route->splat ),
						'root' => ( $route->splat == '' ),
						'bodyClass' => 'directory',
					));

				} elseif ( is_file($realPath) && file_exists($realPath) ) {
					$urlPath = Flight::cfg()->get('app.basePath') . '/' . $route->splat;

					$filename = Flight::h()->getFilename( $urlPath );
					$next = Flight::h()->fetchNext( dirname( $realPath ), $filename );
					$imageProp = Flight::h()->getImageProperties( Flight::cfg()->get('app.realBasePath') . '/www/img/' . $route->splat );

					// Render the clickable image
					Flight::render( 'index.twig', array(
						'fileName' => $filename,
						'currentPath' => dirname( $urlPath ) . '/',
						'nextUrl' => dirname( $urlPath ) . '/' . $next,
						'image' => Flight::cfg()->get('app.basePath') . '/www/img/' . $route->splat,
						'imageWidth' => $imageProp[0],
						'imageHeight' => $imageProp[1],
						'bodyClass' => 'image',
					));
				} else {
					Flight::redirect('/');
				}
			}, true);
		}
	}