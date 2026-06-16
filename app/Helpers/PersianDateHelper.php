<?php
// app/Helpers/PersianDateHelper.php

namespace App\Helpers;

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class PersianDateHelper
{
    /**
     * Convert Persian date to Gregorian
     */
    public static function persianToGregorian($persianDate)
    {
        try {
            // Format: 1405-3-12
            $jalali = Jalalian::fromFormat('Y-m-d', $persianDate);
            return $jalali->toCarbon();
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    /**
     * Convert Gregorian to Persian date
     */
    public static function gregorianToPersian($date)
    {
        if ($date instanceof Carbon) {
            return Jalalian::fromCarbon($date)->format('Y-m-d');
        }
        return Jalalian::now()->format('Y-m-d');
    }

    /**
     * Get current Persian date
     */
    public static function getCurrentPersianDate()
    {
        return Jalalian::now()->format('Y-m-d');
    }
}