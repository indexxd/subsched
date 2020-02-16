<?php

namespace App\Controller;

use App\Repository\LessonRepository;
use App\Repository\RescheduleRepository;
use App\Util\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/lessons", name="lesson")
 */
class LessonController extends AbstractController
{
    /**
     * @Route("/{date}/editted", name="_editted", methods={"GET"})
     */
    public function index(\DateTime $date, LessonRepository $lessonRepository)
    {
        $date =  new Date($date);

        $result = $lessonRepository->findEditted($date);

        return $this->json($result);
    }
}
