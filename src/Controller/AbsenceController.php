<?php

namespace App\Controller;

use Datetime;
use App\Entity\Absence;
use App\Annotation\JsonParams;
use App\Entity\Teacher;
use App\Repository\AbsenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/api/absence", name="absence")
 */
class AbsenceController extends AbstractController
{
    /**
     * @Route("", name="_show_all", methods={"GET"})
     * IsGranted("ROLE_ADMIN")
     */
    public function index(AbsenceRepository $absenceRepository)
    {
        $result = $absenceRepository->findAll();
        return $this->json($result);
    }
    
    
    /**
     * @Route("/{id}", name="_delete", methods={"DELETE"})
     * IsGranted("ROLE_ADMIN")
     */
    public function delete(Absence $absence)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($absence);
        $em->flush();

        return $this->json("");
    }
   
   
    /**
     * @Route("/{date}", name="_show", methods={"GET"})
     * IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function show(string $date, AbsenceRepository $absenceRep)
    {
        $result = $absenceRep->findAllWithDate(new DateTime($date));

        return $this->json($result, 200);
    }

    
    /**
     * @Route("", name="_create", methods={"POST"})
     * IsGranted("ROLE_ADMIN")
     *
     * @JsonParams(name="from", type="date")
     * @JsonParams(name="to", type="date")
     * @JsonParams(name="teacher", type="int", entity="App\Entity\Teacher")
     */
    public function create(Request $request)
    {
        $data = $request->attributes->get("data");

        $absence = new Absence();

        $absence->setFrom($data["from"]);
        $absence->setTo($data["to"]);
        $absence->setTeacher($data["teacher"]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($absence);
        $em->flush();
        
        return $this->json("", 201);
    }


    /**
     * @Route("/{id}", name="_update", methods={"PATCH"})
     * IsGranted("ROLE_ADMIN")
     * 
     * @JsonParams(name="from", type="date")
     * @JsonParams(name="to", type="date")
     * @JsonParams(name="teacher", type="int", entity="App\Entity\Teacher")
     */
    public function update(Request $request, Absence $absence)
    {
        $data = $request->attributes->get("data");

        $absence->setFrom($data["from"]);
        $absence->setTo($data["to"]);

        $this->getDoctrine()->getManager()->flush();
        
        return $this->json([], 200);
    }

    
}
