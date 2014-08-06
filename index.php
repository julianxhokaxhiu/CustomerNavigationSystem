<?php

	require 'vendor/autoload.php';

	use \JX\Cns\Cns;

	$app = new Cns();
	$app
	/* This is the basePath for the URLs */
	->setConfig('app.basePath', '/CustomerNavigationSystem')
	/* This is the name of the customer that will be appendend in the title */
	->setConfig('data.customer', 'My Cool Customer')
	/* If you need to cache pages */
	//->enableCache()
	/* Only for debug purposes */
	//->enableDebug()
	/* Start the show :) */
	->run();