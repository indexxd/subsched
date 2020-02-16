<?php

namespace App\Controller;

use App\Repository\GradeRepository;
use App\Repository\LessonRepository;
use App\Util\Date;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/{vueRoute}", requirements={"vueRoute" = "^(?!api).+"}, name="default", defaults={"vueRoute": null})
     */
    public function index()
    {        
        return $this->render('default/index.html.twig');
    }

}
