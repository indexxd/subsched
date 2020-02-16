<?php

namespace App\Repository;

use App\Entity\Teacher;
use App\Util\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Teacher|null find($id, $lockMode = null, $lockVersion = null)
 * @method Teacher|null findOneBy(array $criteria, array $orderBy = null)
 * @method Teacher[]    findAll()
 * @method Teacher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teacher::class);
    }

    public function generateSubjects()
    {
        $db = $this->getEntityManager()->getConnection();
        $subjects = $db->query("SELECT * FROM subject")->fetchAll();

        $list = [];
        
        foreach ($subjects as $subject) {
            $query = $db->prepare("SELECT DISTINCT teacher_id FROM lesson where subject_id = :subject_id");
            $query->execute(["subject_id" => $subject["id"]]);
            $teachers = $query->fetchAll();
            
            foreach ($teachers as $teacher) {
                $list[$subject["id"]][] = $teacher["teacher_id"];
            }
        }
        
        foreach ($list as $subject => $teacher) {
            $query = $db->prepare("INSERT INTO teacher_subject (teacher_id, subject_id) VALUES (?, ?)");
            $query->execute([$subject, $teacher]);
        }
    }
    
    public function findAvailable(Date $date, $hour, $subject) 
    {
        $recommended = "SELECT teacher.id from (
            select id from (
                select id from teacher
            ) as all_rooms where id not in (
                select teacher_id from lesson 
                where day = :day and week = :week and hour = :hour
            ) and id = any (
                select teacher_id from lesson
                where day = :day and week = :week and hour < :hour
            ) and id = any (
                select teacher_id from lesson
                where day = :day and week = :week and hour > :hour
            )
            union
            select lesson.teacher_id FROM reschedule join lesson on lesson_id = lesson.id
            WHERE type in ('SUBSTITUTE', 'CANCEL') and date = :date and reschedule.hour = :hour 
            union
            select lesson.teacher_id from reschedule join lesson on lesson_id = lesson.id
            where type = 'MOVE' and original_date = :date and reschedule.hour = :hour
            union 
            select teacher_id from lesson where day = :day and week = :week and hour = :hour and grade_id in (
                select grade_id from reschedule
                where type = 'CANCEL' and date = :date and reschedule.hour = :hour and lesson_id is null
            )
        ) as free join teacher on free.id = teacher.id where free.id not in (
            select teacher_id FROM reschedule
            WHERE type in ('SUBSTITUTE', 'MOVE') and date = :date and reschedule.hour = :hour
        ) and free.id = any (
            select teacher_id from teacher_subject as joined 
            where joined.subject_id = :subject_id
        )
        ";

        $available = "SELECT teacher.id from (
            select id from (
                select id from teacher
            ) as all_rooms where id not in (
                select teacher_id from lesson 
                where day = :day and week = :week and hour = :hour
            ) and id = any (
                select teacher_id from lesson
                where day = :day and week = :week and hour < :hour
            ) and id = any (
                select teacher_id from lesson
                where day = :day and week = :week and hour > :hour
            )
            union
            select lesson.teacher_id FROM reschedule join lesson on lesson_id = lesson.id
            WHERE type in ('SUBSTITUTE', 'CANCEL') and date = :date and reschedule.hour = :hour 
            union
            select lesson.teacher_id from reschedule join lesson on lesson_id = lesson.id
            where type = 'MOVE' and original_date = :date and reschedule.hour = :hour
            union 
            select teacher_id from lesson where day = :day and week = :week and hour = :hour and grade_id in (
                select grade_id from reschedule
                where type = 'CANCEL' and date = :date and reschedule.hour = :hour and lesson_id is null
            )
        ) as free join teacher on free.id = teacher.id where free.id not in (
            select teacher_id FROM reschedule
            WHERE type in ('SUBSTITUTE', 'MOVE') and date = :date and reschedule.hour = :hour
        ) and free.id = any (
            select teacher_id from teacher_subject as joined 
            where joined.subject_id != :subject_id
        )
        ";

        $unavailable = "SELECT teacher.id from teacher 
            where id not in (
                select id from (
                    select id from teacher
                ) as all_rooms where id not in (
                    select teacher_id from lesson 
                    where day = :day and week = :week and hour = :hour
                )
                union
                select lesson.teacher_id FROM reschedule join lesson on lesson_id = lesson.id
                WHERE type in ('SUBSTITUTE', 'CANCEL') and date = :date and reschedule.hour = :hour 
                union
                select lesson.teacher_id from reschedule join lesson on lesson_id = lesson.id
                where type = 'MOVE' and original_date = :date and reschedule.hour = :hour
                union 
                -- null cancel
                select teacher_id from lesson where day = :day and week = :week and hour = :hour and grade_id in (
                    select grade_id from reschedule
                    where type = 'CANCEL' and date = :date and reschedule.hour = :hour and lesson_id is null
                )
            )
            union (
                select teacher.id FROM reschedule join teacher on teacher_id = teacher.id
                WHERE type in ('SUBSTITUTE', 'MOVE') and date = :date and reschedule.hour = :hour
        )";

        $map = "SELECT * FROM teacher";

        $db = $this->getEntityManager()->getConnection();
        $args = [
            "hour" => $hour,
            "week" => $date->isWeekOdd(),
            "day" => $date->getDayOfWeek() - 1,
            "date" => $date->getDbFormat(),
            "subject_id" => $subject,
        ];

        $query = $db->prepare($recommended);
        $query->execute($args);
        $recommended = $query->fetchAll();

        $query = $db->prepare($available);
        $query->execute($args);
        $available = $query->fetchAll();

        $query = $db->prepare($unavailable);
        $query->execute($args);
        $unavailable = $query->fetchAll();

        $query = $db->prepare($map);
        $query->execute();
        $map = $query->fetchAll();

        $mapSorted = [];
        foreach ($map as $teacher) {
            $mapSorted[$teacher["id"]]["id"] = $teacher["id"];
            $mapSorted[$teacher["id"]]["firstname"] = $teacher["firstname"];
            $mapSorted[$teacher["id"]]["lastname"] = $teacher["lastname"];
        }

        return [
            "recommended" => $recommended,
            "available" => $available,
            "unavailable" => $unavailable,
        ];
    }

    public function findAllMapped()
    {
        $map = "SELECT * FROM teacher";

        $db = $this->getEntityManager()->getConnection();
     
        $query = $db->prepare($map);
        $query->execute();
        $map = $query->fetchAll();

        $mapSorted = [];

        foreach ($map as $key => $teacher) {
            $mapSorted[$key]["id"] = $teacher["id"];
            $mapSorted[$key]["firstname"] = $teacher["firstname"];
            $mapSorted[$key]["lastname"] = $teacher["lastname"];
        }

        return $mapSorted;
    }
}
