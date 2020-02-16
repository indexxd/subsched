<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lesson
 *
 * @ORM\Table(name="lesson", indexes={@ORM\Index(name="fkIdx_39", columns={"teacher_id"}), @ORM\Index(name="fkIdx_83", columns={"grade_id"}), @ORM\Index(name="fkIdx_56", columns={"room_id"}), @ORM\Index(name="fkIdx_75", columns={"subject_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\LessonRepository")
 */
class Lesson
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="day", type="integer", nullable=false)
     */
    private $day;

    /**
     * @var int
     *
     * @ORM\Column(name="hour", type="integer", nullable=false)
     */
    private $hour;

    /**
     * @var bool
     *
     * @ORM\Column(name="week", type="boolean", nullable=false)
     */
    private $week;

    /**
     * @var string|null
     *
     * @ORM\Column(name="group", type="string", length=5, nullable=true, options={"default"="NULL"})
     */
    private $group = 'NULL';

    /**
     * @var Teacher
     *
     * @ORM\ManyToOne(targetEntity="Teacher")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="teacher_id", referencedColumnName="id")
     * })
     */
    private $teacher;

    /**
     * @var Room
     *
     * @ORM\ManyToOne(targetEntity="Room")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     * })
     */
    private $room;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="Subject")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subject_id", referencedColumnName="id")
     * })
     */
    private $subject;

    /**
     * @var Grade
     *
     * @ORM\ManyToOne(targetEntity="Grade")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
     * })
     */
    private $grade;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getHour(): ?int
    {
        return $this->hour;
    }

    public function setHour(int $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getWeek(): ?bool
    {
        return $this->week;
    }

    public function setWeek(bool $week): self
    {
        $this->week = $week;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): self
    {
        $this->grade = $grade;

        return $this;
    }


}
