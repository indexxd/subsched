<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use App\Util\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/teachers", name="teachers")
 */
class TeacherController extends AbstractController
{
    /**
     * @Route("/", name="teachers", name="_show", methods={"GET"})
     */
    public function index(TeacherRepository $teacherRepository)
    {
        $teachers = $teacherRepository->findAllMapped();

        return $this->json($teachers, 200);
    }


    /**
     * @Route("/available", name="_available", methods={"GET"})
     */
    public function available(Request $request, TeacherRepository $teacherRepository)
    {
        $date = $request->query->get("date", null);
        $hour = $request->query->get("hour", null);
        $subject = $request->query->get("subject", null);

        if ($date === null || $hour === null || $subject === null || !Date::isValid($date)) {
            throw new \Exception();
        }
        
        $date = new Date(new \DateTime($date));
        
        $teachers = $teacherRepository->findAvailable($date, $hour, $subject);

        return $this->json($teachers);
    }


}
