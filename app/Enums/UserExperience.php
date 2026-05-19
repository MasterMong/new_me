<?php

namespace App\Enums;

enum UserExperience: string
{
    case LessThanTwo = '<2y';
    case TwoToFive = '2-5y';
    case FiveToTen = '5-10y';
    case TenToTwenty = '10-20y';
    case OverTwenty = '>20y';
}
