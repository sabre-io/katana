<?php

namespace Mock;

use Sabre\HTTP\Sapi as OriginalSapi;
use Sabre\HTTP\ResponseInterface;

class Sapi extends OriginalSapi
{
    public static function sendResponse(ResponseInterface $response)
    {
        return;
    }
}
