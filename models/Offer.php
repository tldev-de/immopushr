<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    public static function fromOfferDTO(OfferDTO $dto)
    {
        $offer = new Offer();
        $offer->provider = $dto->provider;
        $offer->foreign_id = $dto->foreignId;
        $offer->title = $dto->title;
        $offer->flat_size = $dto->flatSize;
        $offer->rooms = $dto->rooms;
        $offer->price = $dto->price;
        $offer->address = $dto->address;
        return $offer;
    }
}
