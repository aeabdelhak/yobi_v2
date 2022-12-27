<?php

use App\Enums\orderStatus;

function statusToString(int $status)
{
    switch ($status) {
        case orderStatus::$new:
            return 'NOUVEAU';

        case orderStatus::$verified:
            return 'CONFIRME';

        case orderStatus::$pushedToDelivery:
            return 'TAWSILIX';

        case orderStatus::$shipping:
            return 'EXPEDIER';

        case orderStatus::$delivered:
            return 'LIVRE';

        case orderStatus::$canceled:
            return 'ANNULER';

        case orderStatus::$noResponce:
            return 'NRP';
        case orderStatus::$callRequested:
            return 'RAPPEL';

        case orderStatus::$callOv3:
            return 'APPEL +3';
        case orderStatus::$voiceMail:
            return 'BOITE VOCAL';

        case orderStatus::$delayed:
            return 'REPORTE';

        case orderStatus::$outOfArea:
            return 'HORS ZONE';

        case orderStatus::$returned:
            return 'RETOUR';

        case orderStatus::$collected:
            return 'RAMASSÉ';
        case orderStatus::$readyToDeliver:
            return 'PRET POR EXPIDITION';
        case orderStatus::$receivedByDelivery:
            return 'REÇU PAR LIVREUR';

        default:
            break;
    }
}