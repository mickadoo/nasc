<?php

namespace Repo;

use Nasc\Repo\CustomGroupRepo;

class CustomGroupRepoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEntityNameReturnsNameFromClass()
    {
        $repo = new CustomGroupRepo();
        $this->assertEquals('CustomGroup', $repo->getEntityName());
    }
}