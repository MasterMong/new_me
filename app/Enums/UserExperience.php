<?php

namespace App\Enums;

enum UserExperience: string
{
    case LessThanTwo = '<2y';
    case TwoToFive = '2-5y';
    case FiveToTen = '5-10y';
    case TenToTwenty = '10-20y';
    case OverTwenty = '>20y';

    public function label(): string
    {
        return match ($this) {
            self::LessThanTwo => 'น้อยกว่า 2 ปี',
            self::TwoToFive => '2 - 5 ปี',
            self::FiveToTen => '5 - 10 ปี',
            self::TenToTwenty => '10 - 20 ปี',
            self::OverTwenty => 'มากกว่า 20 ปี',
        };
    }
}
