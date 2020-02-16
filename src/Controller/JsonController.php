<?php

namespace App\Controller;

use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class JsonController extends AbstractController
{
    const INVALID_INPUT = 1;
    const CREATED = 2;
    
    protected function errorResponse(int $type)
    {
        switch ($type) 
        {
            case 1:
                return $this->json(["message" => "Invalid input."], 400); 
        }
    }

    protected function successResponse(int $type)
    {
        switch ($type) 
        {
            case 2:
                return $this->json([], 201); 
        }

    }
}