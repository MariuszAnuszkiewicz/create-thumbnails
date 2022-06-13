<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class UploadFiles
{
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    abstract public function putFiles($pathFile, $fileName);
}