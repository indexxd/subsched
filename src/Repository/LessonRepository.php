<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Util\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Lesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lesson[]    findAll()
 * @method Lesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }


    public function findEditted(Date $date) 
    {
        $week = $date->isWeekOdd();
        $day = $date->getDayOfWeek() - 1;
        
        $sql = "SELECT r.lesson_id FROM reschedule r
        WHERE r.type IN ('SUBSTITUTE', 'CANCEL') AND r.date = :date AND r.lesson_id IS NOT NULL
        UNION SELECT r.lesson_id FROM reschedule r 
        WHERE r.type = 'MOVE' AND original_date = :date
        UNION SELECT l.id FROM lesson l
        WHERE (l.hour, l.grade_id) IN (
                SELECT r.hour, r.grade_id FROM reschedule r
                WHERE r.type = 'CANCEL' AND r.date = :date AND r.lesson_id IS NULL
            ) AND week = :week AND day = :day
        ";
                
        
        $db = $this->getEntityManager()->getConnection();
        $query = $db->prepare($sql);
        $query->execute([
            "date" => $date->getDbFormat(),
            "day" => $day,
            "week" => $week,
        ]);

        return $query->fetchAll();

    }
    
    
}
