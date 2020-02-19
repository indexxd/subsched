<?php

namespace App\Repository;

use App\Entity\Grade;
use App\Entity\Lesson;
use App\Entity\Reschedule;
use App\Entity\Room;
use App\Entity\Teacher;
use DateTime;
use App\Util\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\FetchMode;

/**
 * @method Reschedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reschedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reschedule[]    findAll()
 * @method Reschedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RescheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reschedule::class);
    }
       

    public function nvm(Date $date)
    {
        $sql = "SELECT hour, grade.name as grade, type, custom, lesson_id, room_id, teacher_id from reschedule
        join grade on grade_id = grade.id 
				where date = :date group by grade_id, hour, lesson_id
		union
		SELECT lesson.hour, grade.name as grad, 'CANCEL' as type, custom, lesson_id, lesson.room_id, lesson.teacher_id 
		from reschedule
				join grade on reschedule.grade_id = grade.id 
				join lesson on lesson_id = lesson.id
				where original_date = :date group by lesson.grade_id, lesson.hour, lesson_id
        ";

        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare($sql);
        $query->execute(["date" => $date->getDbFormat()]);
        
        return $query->fetchAll(FetchMode::STANDARD_OBJECT);
    }

    
    
    public function deletePreviousChanges(Date $date, Grade $grade, $hour, Lesson $lesson = null)
    {

        if ($lesson === null) {
            $qb = $this->createQueryBuilder("r");
            $qb
                ->delete()
                ->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->eq("r.date", ":date"),
                        $qb->expr()->eq("r.hour", ":hour"),
                        $qb->expr()->eq("r.grade", ":grade"),
                        $qb->expr()->neq("r.type", "'MOVE'"),
                    )
                )
                ->setParameters([
                    "date" => $date->getDateTimeObject(),
                    "hour" => $hour,
                    "grade" => $grade,
                ])
                ->getQuery()
                ->execute()
            ;

            $temp = "CREATE temporary TABLE temp 
            SELECT reschedule.id 
            FROM   reschedule 
                   JOIN lesson 
                     ON lesson_id = lesson.id 
            WHERE  lesson.grade_id = :grade_id 
                   AND original_date = :original_date 
                   AND type = 'MOVE' 
                   AND lesson.week = :week 
                AND lesson.day = {$date->getDayOfWeek()}";

            $delete = "DELETE FROM reschedule 
            WHERE  id IN (SELECT *
                            FROM   temp); DROP TABLE temp";
            
            $sql = $temp . "; " . $delete;
            
            $db = $qb->getEntityManager()->getConnection();

            $query = $db->prepare($sql);
            $query->execute([
                ":grade_id" => $grade->getId(), 
                ":original_date" => $date->getDbFormat(),
                ":week" => $date->isWeekOdd(),
                // wtf ???????????????????
                // ":day" => $date->getDayOfWeek(),
            ]);
            
            return true;
        }
        
        $qb = $this->createQueryBuilder("r");
        $qb
            ->delete()
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq("r.date", ":date"),
                    $qb->expr()->eq("r.hour", ":hour"),
                    $qb->expr()->eq("r.grade", ":grade"),
                    $qb->expr()->neq("r.type", "'MOVE'"),
                    $qb->expr()->orX(
                        $qb->expr()->eq("r.lesson", ":lesson"),
                        $qb->expr()->isNull("r.lesson"),
                    )
                )
            )
            ->setParameters([
                "date" => $date->getDateTimeObject(),
                "hour" => $hour,
                "grade" => $grade,
                "lesson" => $lesson,
            ])
            ->getQuery()
            ->execute()
        ;

        $qb = $this->createQueryBuilder("r");
        $qb
            ->delete()
            ->andWhere("r.originalDate = :date")
            ->andWhere("r.type = 'MOVE'")
            ->andWhere("r.lesson = :lesson")
            ->setParameters([
                "date" => $date->getDateTimeObject(),
                "lesson" => $lesson,    
            ])
            ->getQuery()
            ->execute()
        ;
    }
    
}
