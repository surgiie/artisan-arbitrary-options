<?php

// Stub out the app() function for testing purposes since it's not defined in this context/dependencies.
function app()
{
    return new class
    {
        public function runningUnitTests()
        {
            return false;
        }
    };
}
