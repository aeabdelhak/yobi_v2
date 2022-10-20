<?php

namespace App\Enums;

class PushModel
{
    public $fullName, $address, $phone, $product, $note;
    public $city, $id_token, $qty, $change, $openpackage, $id;
    public $prix;

    public function __construct($id, $fullName, $city, $address, $phone, int $id_token, $product, int $qty, $note, int $change, int $openpackage, $prix)
    {
        $this->$id = $id;
        $this->$fullName = $fullName;
        $this->$city = $city;
        $this->$address = $address;
        $this->$phone = $phone;
        $this->$id_token = $id_token;
        $this->$product = $product;
        $this->$qty = $qty;
        $this->$note = $note;
        $this->$change = $change;
        $this->$openpackage = $openpackage;
        $this->$prix = $prix;
    }

}