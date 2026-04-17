<?php

namespace App\Controller\Api;

use App\Controller\BaseAbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseApiAbstractController extends BaseAbstractController
{
    protected function getJsonRequestData(Request $request): array
    {
        $data = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return is_array($data) ? $data : [];
    }
}
