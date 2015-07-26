<?php
	$parameters = include(__DIR__ . '/./parameters.php');
	$getParameter = function($param, $default) use (&$parameters)
	{
		return isset($parameters[$param]) ? $parameters[$param] : $default;
	};

	return [
		'parameters' => $parameters,
		'debug' => $getParameter('debug', false), // setting to true is equivalent to dev environment
		'bundles' => include(__DIR__.'/./bundles.php'),
		'db' => $getParameter('db', null),
		'twig' => $getParameter('twig', false),
		'assetsVersion' => 'v1',
		'locale' => $getParameter('locale', 'ru'),
		'settings' => [
			'siteurl' => [
				'title' =>  'Site URL',
			]
		],
	];