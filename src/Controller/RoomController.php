<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use App\Util\Date;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/rooms", name="rooms")
 */
class RoomController extends AbstractController
{
    /** 
     * @Route("", name="_show", methods={"GET"})
     */
    public function index(RoomRepository $roomRepository)
    {
        $rooms = $roomRepository->findAllMapped();

        return $this->json($rooms, 200);
    }
    
    /** 
     * @Route("/available", name="_available", methods={"GET"})
     */
    public function available(Request $request, RoomRepository $roomRepository)
    {
        $date = $request->query->get("date", null);
        $hour = $request->query->get("hour", null);

        if ($date === null || $hour === null || !Date::isValid($date)) {
            throw new Exception();
        }

        $date = new Date(new \DateTime($date));
        $rooms = $roomRepository->findAvailable($date, $hour);

        return $this->json($rooms);
    }

}
