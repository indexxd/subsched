<?php

namespace App\Controller;


use App\Repository\RescheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\JsonParams;
use App\Util\Date;
use App\Entity\Reschedule;
use App\Repository\GradeRepository;
use Exception;

/**
 * @Route("/api/reschedule", name="reschedule")
 */
class RescheduleController extends JsonController
{

    /**
     * @Route("/sub", name="_sub", methods={"POST"})
     * 
     * @JsonParams(name="date", type="date")
     * @JsonParams(name="hour", type="int")
     * @JsonParams(name="grade", type="int", entity="App\Entity\Grade")
     * @JsonParams(name="room", type="int", required=false, entity="App\Entity\Room")
     * @JsonParams(name="lesson", type="int", entity="App\Entity\Lesson")
     * @JsonParams(name="teacher", type="int", entity="App\Entity\Teacher")
     */
    public function sub(Request $request, RescheduleRepository $rescheduleRepository)
    {
        $data = $request->attributes->get("data");


        $sub = new Reschedule();
        $sub->setDate($data["date"])
            ->setGrade($data["grade"])
            ->setHour($data["hour"])
            ->setRoom($data["room"])
            ->setLesson($data["lesson"])
            ->setTeacher($data["teacher"])
            ->setType(Reschedule::SUBSTITUTE)
            ->setOriginalDate(null)
        ;
        
        $rescheduleRepository->deletePreviousChanges(
            new Date($data["date"]), $data["grade"], $data["hour"], $data["lesson"]
        );

        $em = $this->getDoctrine()->getManager();
        $em->persist($sub);
        $em->flush();
        
        return $this->json(["message" => "success"], 201);
    }

    /**
     * @Route("/cancel", name="_cancel", methods={"POST"})
     * 
     * @JsonParams(name="date", type="date")
     * @JsonParams(name="hour", type="int")
     * @JsonParams(name="grade", type="int", entity="App\Entity\Grade")
     * @JsonParams(name="lesson", type="int", entity="App\Entity\Lesson")
     * @JsonParams(name="merge", type="bool", required=false)
     */
    public function cancel(Request $request, RescheduleRepository $rescheduleRepository)
    {
        $data = $request->attributes->get("data");

        $sub = new Reschedule();
        $sub->setDate($data["date"])
            ->setGrade($data["grade"])
            ->setHour($data["hour"])
            ->setType(Reschedule::CANCEL)
        ;
        
        if (!$data["merge"]) {
            $sub->setLesson($data["lesson"]);
        }
        
        $rescheduleRepository->deletePreviousChanges(
            new Date($data["date"]), $data["grade"], $data["hour"], $data["merge"] ? null : $data["lesson"]
        );
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($sub);
        $em->flush();
        
        return $this->json([], 201);
    }

    /**
     * @Route("/bulkedit", name="_bulkedit", methods={"POST"})
     * 
     * @JsonParams(name="date", type="date")
     * @JsonParams(name="grades", type="array")
     * @JsonParams(name="hours", type="array")
     * @JsonParams(name="value")
     */
    public function bulkEdit(Request $request, RescheduleRepository $rescheduleRepository, GradeRepository $gradeRepository)
    {
        try {
            $data = $request->attributes->get("data");
    
            $value = $data["value"];
            $grades = $data["grades"];
            $hours = $data["hours"];
    
            foreach ($grades as $grade) {
                $grade = $gradeRepository->find($grade);

                foreach ($hours as $hour) {
                    $rescheduleRepository->deletePreviousChanges(
                        new Date($data["date"]), $grade, $hour
                    );

                    if ($value !== "") {
                        $sub = new Reschedule();
                        $sub->setDate($data["date"])
                            ->setGrade($grade)
                            ->setHour($hour)
                            ->setType($value === "0" ? Reschedule::CANCEL : Reschedule::CUSTOM)
                            ->setCustom($value !== "0" ? $value : null)
                        ;

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($sub);
                        $em->flush();          
                
                    }
                }
            }

            return $this->json([], 201);
        } 
        catch (\Throwable $th) {
            return $this->errorResponse(self::INVALID_INPUT);
        }
    }

    /**
     * @Route("/custom", name="_custom", methods={"POST"})
     * 
     * @JsonParams(name="date", type="date")
     * @JsonParams(name="grade_name")
     * @JsonParams(name="hour", type="int")
     * @JsonParams(name="value", required=false)
     */
    public function custom(Request $request, GradeRepository $gradeRepository, RescheduleRepository $rescheduleRepository)
    {
        $data = $request->attributes->get("data");
        
        $value = $data["value"];
        
        $grade = $gradeRepository->findOneBy(["name" => $data["grade_name"]]);
        
        $rescheduleRepository->deletePreviousChanges(new Date($data["date"]), $grade, $data["hour"]);
        
        if ($value) {
            if ($grade === null) {
                throw new Exception();
            }
            
            $sub = new Reschedule();
            $sub->setDate($data["date"])
                ->setGrade($grade)
                ->setHour($data["hour"])
                ->setType($value === "0" ? Reschedule::CANCEL : Reschedule::CUSTOM)
                ->setCustom($value !== "0" ? $value : null)
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($sub);
            $em->flush();          
        }

        return $this->successResponse(self::CREATED);
    }

    /**
     * @Route("/move", name="_move", methods={"POST"})

     * @JsonParams(name="originalDate", type="date")
     * @JsonParams(name="date", type="date")
     * @JsonParams(name="hour", type="int")
     * @JsonParams(name="grade", type="int", entity="App\Entity\Grade")
     * @JsonParams(name="room", type="int", entity="App\Entity\Room")
     * @JsonParams(name="lesson", type="int", entity="App\Entity\Lesson")
     */
    public function move(Request $request, RescheduleRepository $rescheduleRepository)
    {
        $data = $request->attributes->get("data");

        $rescheduleRepository->deletePreviousChanges(
            new Date($data["originalDate"]), $data["grade"], $data["hour"], $data["lesson"]
        );

        $res = new Reschedule();
        $res->setDate($data["date"])
            ->setOriginalDate($data["originalDate"])
            ->setHour($data["hour"])
            ->setGrade($data["grade"])
            ->setType(Reschedule::MOVE)
            ->setLesson($data["lesson"])
            ->setRoom($data["room"])
        ;

        $em = $this->getDoctrine()->getManager();
        $em->persist($res);
        $em->flush();

        return $this->successResponse(self::CREATED);
    }
}
