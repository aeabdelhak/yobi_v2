<?php

namespace App\Enums;

class orderStatus
{
    public static $new = 1;
    public static $verified = 2;
    public static $pushedToDelivery = 3;
    public static $shipping = 4;
    public static $delivered = 5;
    public static $canceled = 6;
    public static $noResponce = 7;
    public static $callRequested = 8;
    public static $callOv3 = 8;
    public static $voiceMail = 10;
    public static $delayed = 11;
    public static $outOfArea = 12;
    public static $collected = 13;
    public static $returned = 14;
    public static $readyToDeliver = 15;
    public static $receivedByDelivery = 16;
    public static $deleted = 88;

}