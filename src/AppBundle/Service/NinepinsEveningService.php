<?php

namespace AppBundle\Service;

class NinepinsEveningService extends AbstractService
{

    public function __construct($manager, $dispatcher)
    {
        parent::__construct($manager, $dispatcher);
    }

	public function getTest()
	{
		return 'test2';
	}



}