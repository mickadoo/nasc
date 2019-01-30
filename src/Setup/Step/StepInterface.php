<?php

namespace Nasc\Setup\Step;

interface StepInterface
{
    /**
     * Apply the step on installation or upgrade
     *
     * @return void
     */
    public function apply();

    /**
     * Undo the step actions done in apply()
     *
     * @return void
     */
    public function remove();
}