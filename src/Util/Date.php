<?php

namespace App\Util;

use DateTime;

class Date
{
    /**
     * @var DateTime
     */
    private $date;
    
    const DAYS = ["", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek"];
    
    
       
    public function __construct(DateTime $date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        }

        $this->date = $date;
    }
   

    public function getNamedDayOfWeek()
    {
        return self::DAYS[$this->date->format("w")];
    }
    

    public function getNamedWeek(): int 
    {
        return $this->isWeekOdd() ? "Lichý" : "Sudý";
    }
    
    
    /**
     * Sets and returns the new DateTime object
     */
    public function setToClosestWorkDay(): DateTime
    {
        $day = $this->date->format("w");
        
        if ($day == "6" || $day == "0") {
            $add = $day == "0" ? "1" : "2";
            
            return $this->date->modify("+ $add days");
        }
        
        return $this->date;
    }


    public function getDbFormat(): string
    {
        return $this->date->format("Y-m-d");
    }
    

    public function getDayOfWeek(): int
    {
        return $this->date->format("w");
    }
    
    
    public function isWeekOdd(): bool
    {
        return $this->date->format("W") % 2 === 0 ? false : true;
    }

    
    public function getDateTimeObject(): DateTime
    {
        return $this->date;
    }

    /**
     * Checks if string is a valid input for DateTime
     */
    public static function isValid(string $date)
    {
        try {
            new DateTime($date);
            return true;
        } 
        catch (\Throwable $th) {
            return false;
        }
    }

    public static function nextTwoWeeks()
    {
        $today = new Date();
        $even = [];
        $odd = [];
        
        for ($i = 0; $i < 12; $i++) { 
            $date = date("Y-m-d", strtotime("next week" . "+" . $i . " day"));

            if ($i <= 6) {
                if ($i != 5 && $i != 6) {
                    $even[] = $date;
                }
            }
            else {
                $odd[] = $date;
            }
            
        }
	
		return $today->isWeekOdd() ? [$even, $odd] : [$odd, $even];

    }
}