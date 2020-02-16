<?php

namespace App\Repository;

use App\Entity\Room;
use App\Util\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function findAllMapped()
    {
        $map = "SELECT * FROM room";

        $db = $this->getEntityManager()->getConnection();
     
        $query = $db->prepare($map);
        $query->execute();
        $map = $query->fetchAll();

        $mapSorted = [];

        foreach ($map as $key => $room) {
            $mapSorted[$key]["id"] = $room["id"];
            $mapSorted[$key]["name"] = $room["name"];
        }

        return $mapSorted;
    }

    public function findAvailable(Date $date, $hour)
    {
        $availableRooms = "SELECT room.name FROM (
            SELECT room_id FROM (
                SELECT id AS room_id FROM room
            ) AS all_rooms WHERE room_id NOT in (
                SELECT room_id FROM lesson WHERE day = :day AND week = :week AND hour = :hour
            )
            UNION
            SELECT lesson.room_id FROM reschedule JOIN lesson on lesson_id = lesson.id
            WHERE type in ('SUBSTITUTE', 'CANCEL') AND date = :date AND reschedule.hour = :hour
            UNION
            SELECT lesson.room_id FROM reschedule JOIN lesson on lesson_id = lesson.id
            WHERE type = 'MOVE' AND original_date = :date AND reschedule.hour = :hour
            UNION 
            SELECT room_id FROM lesson WHERE day = :day AND week = :week AND hour = :hour AND grade_id in (
                SELECT grade_id FROM reschedule
                WHERE type = 'CANCEL' AND date = :date AND reschedule.hour = :hour AND lesson_id IS NULL
            )
        ) as empty JOIN room on room_id = room.id WHERE room_id NOT in (
            SELECT room_id FROM reschedule
            WHERE type in ('SUBSTITUTE', 'MOVE') AND date = :date AND reschedule.hour = :hour
        ) ORDER BY  name ASC
        ";
        
        $unavailableRooms = "SELECT room.name FROM (
            SELECT room_id FROM lesson WHERE day = :day AND week = :week AND hour = :hour
        ) as taken JOIN room on room_id = room.id WHERE room_id NOT IN (
            SELECT lesson.room_id FROM reschedule JOIN lesson on lesson_id = lesson.id
            WHERE type IN ('SUBSTITUTE', 'CANCEL') AND date = :date AND reschedule.hour = :hour 
            UNION
            SELECT lesson.room_id FROM reschedule JOIN lesson on lesson_id = lesson.id
            WHERE type = 'MOVE' AND original_date = :date AND reschedule.hour = :hour
            UNION 
            SELECT room_id FROM lesson WHERE day = :day AND week = :week AND hour = :hour AND grade_id in (
                SELECT grade_id FROM reschedule
                WHERE type = 'CANCEL' AND date = :date AND reschedule.hour = :hour AND lesson_id IS NULL
            )
        ) UNION 
            SELECT room.name FROM reschedule JOIN room on room_id = room.id 
            WHERE type in ('SUBSTITUTE', 'MOVE') AND date = :date AND reschedule.hour = :hour
            ORDER BY  name ASC
        ";

        $db = $this->getEntityManager()->getConnection();
        $args = [
            "hour" => $hour,
            "day" => $date->getDayOfWeek() - 1,
            "date" => $date->getDbFormat(),
            "week" => $date->isWeekOdd(),
        ];


        $query = $db->prepare($availableRooms);
        $query->execute($args);

        $availableRooms = $query->fetchAll();

        
        $query = $db->prepare($unavailableRooms);
        $query->execute($args);

        $unavailableRooms = $query->fetchAll();
        
        return [
            "available" => $availableRooms,
            "unavailable" => $unavailableRooms,
        ];
    }
}
