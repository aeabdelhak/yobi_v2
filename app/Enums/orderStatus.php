<?php

namespace App\Enums;

class orderStatus extends sharedStatus
{
    public static $new = 1;
    public static $verified = 2;
    public static $shipping = 3;
    public static $delivered = 4;
    public static $canceled = 5;
    public static $noResponce = 6;
    public static $callRequested = 7;
    public static $callOv3 = 8;
    public static $voiceMail = 9;
    public static $delayed = 10;
    public static $outOfArea = 11;

}