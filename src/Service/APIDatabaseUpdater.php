<?php

namespace App\Service;

use App\Entity\Grade;
use App\Entity\Lesson;
use App\Entity\Room;
use App\Entity\Subject;
use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use App\Util\Date;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class APIDatabaseUpdater
{
    private $url = "https://is.sps-prosek.cz/api.php";
    /** @var EntityManager */
    private $em;
    /** @var TeacherRepository */
    private $teacherRepository;


    public function __construct(EntityManagerInterface $em, TeacherRepository $teacherRepository)
    {
        $this->em = $em;    
        $this->teacherRepository = $teacherRepository;
    }

    
    public function updateDatabase()
    {

        $classes = $this->getClasses();

        $rawTimetable = $this->fetchTimetable($classes);

        $result = $this->processTimetable($rawTimetable);
        $result["classes"] = $classes;

        $this->executeInserts($result);
    }

    private function executeInserts($data)
    {
        $teacher_names = $data["teacher_names"];
        $teacher_shorts = $data["teacher_shorts"];
        $subjects = $data["subjects"];
        $rooms = $data["rooms"];
        $classes = $data["classes"];
        $lessons = $data["lessons"];

        $helper = [];
        
        $em = $this->em;
        
        foreach ($rooms as $name) {
            $room = (new Room())->setName($name);
            $em->persist($room);

            $helper["room"][$name] = $room;
        }

        foreach ($subjects as $shortcut => $fullname) {
            $subject = (new Subject())
                ->setName($fullname)
                ->setShort($shortcut)
            ;
            $em->persist($subject);

            $helper["subject"][$shortcut] = $subject;
        }

        foreach ($teacher_shorts as $short) {
            $teacher = (new Teacher())
                ->setFirstname($teacher_names[$short][0])
                ->setLastname($teacher_names[$short][1])
                ->setShortname($short)
            ;
            $em->persist($teacher);

            $helper["teacher"][$short] = $teacher;
        }

        foreach ($classes as $name) {
            $grade = (new Grade())->setName($name);
            $em->persist($grade);

            $helper["grade"][$name] = $grade;
        }
        
        foreach ($lessons as $lesson) {
            $lessonEnt = new Lesson();
            $lessonEnt
                ->setDay($lesson->day)
                ->setHour($lesson->hour)
                ->setWeek($lesson->week)
                ->setGroup($lesson->group)
                ->setRoom($helper["room"][$lesson->room])
                ->setSubject($helper["subject"][$lesson->subject])
                ->setGrade($helper["grade"][$lesson->class])
                ->setTeacher($helper["teacher"][$lesson->teacher])
            ;
            $em->persist($lessonEnt);
        }

        $em->flush();

        $this->populateTeacherSubjectTable($subjects);
    }

    private function populateTeacherSubjectTable($subjects)
    {
        $this->teacherRepository->generateSubjects();
    }

    private function processTimetable($result)
    {
        $teacher_names = [];
        $teacher_shorts = [];
        
        $subjects = [];
        $rooms = [];
        $lessons = [];
        
        foreach ($result as $class) {
            foreach ($class as $week) {
                foreach ($week as $day) {
                    foreach ($day as $hour) {
                        if (!in_array($hour["TSHORTCUT"], $teacher_shorts)) {
                            $teacher_shorts[] = $hour["TSHORTCUT"];
                            $teacher_names[$hour["TSHORTCUT"]][] = $hour["TFIRSTNAME"];
                            $teacher_names[$hour["TSHORTCUT"]][] = $hour["TSURNAME"];
                        }
        
                        if (!in_array($hour["ROOM"], $rooms)) {
                            $rooms[] = $hour["ROOM"];
                        }
        
                        if (!in_array($hour["SUBJECT"], $subjects)) {
                            $subjects[$hour["SHORTCUT"]] = $hour["SUBJECT"];
                        }
        
                        $lesson = new \stdClass();
        
                        $lesson->class = array_search($class, $result);
                        $lesson->week = array_search($week, $class);
                        $lesson->day = array_search($day, $week);
                        $lesson->hour = $hour["HOUR"];
                        $lesson->teacher = $hour["TSHORTCUT"];
                        $lesson->subject = $hour["SHORTCUT"];
                        $lesson->room = $hour["ROOM"];
                        $lesson->group = $hour["GROUP"];
        
                        $lessons[] = $lesson;
                    }
                }
            }
        }

        return [
            "teacher_names" => $teacher_names,
            "teacher_shorts" => $teacher_shorts,
            "subjects" => $subjects,
            "rooms" => $rooms,
            "lessons" => $lessons,
        ];
    }
    
    private function fetchTimetable($classes)
    {
        $data = array(
            "API" => "tt",
            "KEY" => "{{key}}",
            "PASS" => "{{pass}}",
        );

        $weeks = Date::nextTwoWeeks();


        $result = [];

        foreach ($classes as $class) {
            for ($i = 0; $i < count($weeks); $i++) {
                $days = $weeks[$i];
        
                foreach ($days as $day) {
                    $data["DATE"] = $day;
                    $data["CLASS"] = $class;        
        
                    $result = array_merge_recursive($result, $this->parseLesson($data, $i));
                }
            }
        }

        return $result;
    }
    
    private function getClasses()
    {
        $data = [
            "API" => "cls",
            "KEY" => "{{key}}",
            "PASS" => "{{pass}}",
        ];
        
        $classesRaw = $this->request($data);

        $classes = [];

        foreach ($classesRaw["CLS"] as $class) {
            $classes[] = trim($class["CLASS"]);
        }
        
        return $classes;
    }
    
    private function parseLesson($data, $week)
    {
        $date = new Date($data["DATE"]);

        $array = $this->request($data);
	
        $classArray = [];

        $classIndex = $data["CLASS"];
        $weekIndex = $week ? "A" : "B";
        $dayOfWeekIndex = $date->getDayOfWeek();
        $classArray[$classIndex][$weekIndex][$dayOfWeekIndex] = $array["SCH"];

        return $classArray;
    }

    private function request($data)
    {
		$payload = json_encode($data);
		
		$resource = curl_init();
		curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($resource, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($resource, CURLOPT_URL, $this->url);
		curl_setopt($resource, CURLOPT_POST, 1);
		curl_setopt($resource, CURLOPT_POSTFIELDS, array("data" => $payload));
		curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($resource);
		curl_close($resource);
		
		$array = json_decode($result, true);

		return $array;

    }

}