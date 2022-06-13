<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class UploadFiles
{
    protected $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    abstract public function putFiles($file, $destinationFolder);
}