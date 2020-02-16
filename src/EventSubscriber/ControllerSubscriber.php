<?php

namespace App\EventSubscriber;

use App\Annotation\JsonParams;
use App\Repository\GenericRepository;
use App\Util\Date;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\Request;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;


class ControllerSubscriber implements EventSubscriberInterface
{    
    /** @var Reader */
    private $reader;

    /** @var GenericRepository */
    private $genericRepository;

    /** @var Request */
    private $request;

    private $convertedParams = [];
    private $decodedData = [];

    public function __construct(Reader $reader, GenericRepository $genericRepository)
    {
        $this->reader = $reader;
        $this->genericRepository = $genericRepository;
    }   


    public function onControllerEvent(ControllerEvent $event)
    {
        $this->decodedData = json_decode($event->getRequest()->getContent());
        $this->request = $event->getRequest();
        
        $set = $event->getController();

        if (is_array($set)) {
            $controller = $event->getController()[0];
            $method = $event->getController()[1];    
            
            $this->handleJsonAnnotations($controller, $method);
        }


    }

    private function handleJsonAnnotations($controller, $method) 
    {
        $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($controller, $method), JsonParams::class);
        
        foreach ($annotations as $annotation) {
            if ($annotation instanceof JsonParams) {
                try {
                    $this->handleAnnotation($annotation);
                } 
                catch (\Exception $e) {
                    throw $e;
                }
            }
        }

        $this->request->attributes->add([
            "data" => $this->convertedParams,
        ]);
    }

    private function handleAnnotation(JsonParams $annotation)
    {
        try {
            $name = $annotation->name;
            $param = $this->decodedData->$name ?? null;
            
            if ($annotation->type) {
                switch ($annotation->type) {
                    case "int":
                        if (!is_numeric($param)) {
                            throw new \Exception("Parameter {$annotation->name} must be of type int, but is not.");
                        } break;
                    case "bool":
                        $param = !!$param;
                        break;
                    case "date":
                        if (!Date::isValid($param)) {
                            throw new \Exception("Parameter {$annotation->name} must be a date.");
                        } 
                        $param = new \DateTime($param);
                        break;
                    case "array":
                        if (!is_array($param)) {
                            throw new \Exception("Parameter {$annotation->name} must be an array.");
                        } break;
                }
            }
            
            if ($annotation->entity !== null) {
                $param = $this->genericRepository->setEntityType($annotation->entity)->find($param);
            }
            
            if ($param === null && $annotation->required) {
                throw new \Exception("Parameter {$annotation->name} is required, but is null.");
            }
                                    
            $this->convertedParams[$annotation->name] = $param;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }
    
    public static function getSubscribedEvents()
    {
        return [
            ControllerEvent::class => 'onControllerEvent',
        ];
    }
}
