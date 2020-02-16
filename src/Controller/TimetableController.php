<?php

namespace App\Controller;

use App\Annotation\JsonParams;
use App\Entity\Lesson;
use App\Repository\GradeRepository;
use App\Repository\LessonRepository;
use App\Repository\RescheduleRepository;
use App\Util\Date;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TimetableController extends AbstractController
{
    /**
     * @Route("/api/timetable", name="timetable", methods={"GET"})
     */
    public function index(Request $request, GradeRepository $gradeRep, LessonRepository $lessonRep)
    {
        $query = $request->query->get("date", "");

        if (!Date::isValid($query)) {
            return $this->redirectToRoute("timetable");
        }
        
        $date = new Date(new DateTime($query));


        // If date is weekend, redirect to next week
        $date->setToClosestWorkDay();

        if ($date->getDateTimeObject()->diff(new DateTime($query))->days != 0) {
            return $this->redirect("?date=" . $date->getDateTimeObject()->format("d-m-Y"));
        }
        

        $grades = $gradeRep->findAll();

        // Load every lesson for this date
        $lessons = [];
        
        foreach ($grades as $grade) {
            $lessons[$grade->getName()] = $lessonRep->findBy([
                // "day" => $date->getDateTimeObject()->format("w"),
                "day" => $date->getDayOfWeek() - 1,
                "grade" => $grade,
                "week" => $date->isWeekOdd(),
            ]);
        }


        // Arange into 2D array
        $timetable = [];

        foreach ($lessons as $grade) {
            foreach ($grade as $lesson) {
                $timetable
                    [$lesson->getGrade()->getName()]
                    [$lesson->getHour()]
                [] = $lesson;
            }
        }

        return $this->json([
            "date" => $date->getDateTimeObject()->format("d-m-Y"),
            "timetable" => $timetable,
        ]);
        
    }

    /**
     * @Route("/api/output/", name="timetable_output")
     */
    public function output(Request $request, RescheduleRepository $rescheduleRepository, GradeRepository $gradeRepository)
    {
        $query = $request->query->get("date", "");

        if (!Date::isValid($query)) {
            return $this->redirectToRoute("timetable_output");
        }

        $dateTime = new DateTime($query);
        $date = new Date($dateTime);

        $date->setToClosestWorkDay();

        if ($date->getDateTimeObject()->diff($dateTime)->days != 0) {
            return $this->redirect("?date=" . $date->getDateTimeObject()->format("d-m-Y"));
        }
        
        $changed = $rescheduleRepository->nvm($date);
        $grades = $gradeRepository->findAll();

        $result = $this->formatOutput($changed, $grades);

        return $this->json([
            "date" => $date->getDateTimeObject()->format("d-m-Y"),
            "timetable" => $result
        ]);
    }

    private function formatOutput($changed, $grades) 
    {
        $tt = [];
        
        foreach ($grades as $grade) {
            $grade = $grade->getName();
            $tt[$grade] = [];
            
            for ($hour = 1; $hour <= 9; $hour++) {
                $tt[$grade][$hour - 1] = "";
                $current = [null, null];

                foreach ($changed as $change) {
                    if ($change->hour == $hour && $change->grade == $grade) {
                        $break = $current[0] == $hour && $current[1] == $grade ? "\n" : "";
                        
                        if ($change->type === "CANCEL") {
                            $value = "";

                            if (empty($change->lesson_id)) {
                                $value = "";
                            }
                            else {
                                $group = $this->getDoctrine()->getRepository(Lesson::class)->find($change->lesson_id)->getGroup();
                                
                                if ($group !== "celá") {
                                    $value = $group . "\n";
                                }
                            }
                            $tt[$grade][$hour - 1] .= $break . $value . "0";
                        }

                        else if ($change->type === "MOVE") {
                            $lesson = $this->getDoctrine()->getRepository(Lesson::class)->find($change->lesson_id);
                            
                            $tt[$grade][$hour - 1] 
                                .= $break 
                                . $lesson->getSubject()->getShort() . "\n" 
                                . $lesson->getTeacher()->getShortname() . "," 
                                . $lesson->getRoom()->getName()
                            ;
                        }

                        else if ($change->type === "SUBSTITUTE") {
                            $lesson = $this->getDoctrine()->getRepository(Lesson::class)->find($change->lesson_id);

                            $group = $lesson->getGroup() !== "celá" ? $lesson->getGroup() : "";
                            
                            $tt[$grade][$hour - 1] 
                                .= $break
                                . $group . " "
                                . $lesson->getSubject()->getShort() . "\n"
                                . $lesson->getTeacher()->getShortname() . ","
                                . $lesson->getRoom()->getName()
                            ;
                        }

                        if ($change->type === "CUSTOM") {
                            $tt[$grade][$hour - 1] .= $change->custom;
                        }
                        
                        $current = [ 
                            $hour, 
                            $grade 
                        ];
                    }
                }
            }
        }

        return $tt;
    }
}
