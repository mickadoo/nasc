<?php

namespace Nasc\Service;

class DrupalSettingService
{
    public function setVariable($name, $value)
    {
        variable_set($name, $value);
    }
}