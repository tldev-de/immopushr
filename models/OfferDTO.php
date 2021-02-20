<?php

namespace Models;

class OfferDTO
{
    public string $provider;

    public string $foreignId;

    public string $title;

    public string $link;

    public string $price;

    public string $flatSize;

    public string $rooms;

    public string $address;

    public function __construct($provider, $foreignId, $title, $link, $price, $flatSize, $rooms, $address)
    {
        $this->provider = $provider;
        $this->foreignId = $foreignId;
        $this->title = $title;
        $this->link = $link;
        $this->price = $price;
        $this->flatSize = $flatSize;
        $this->rooms = $rooms;
        $this->address = $address;
    }

    public function toString()
    {
        return implode("\n", [
            'Neues Angebot bei ' . $this->provider . ': ' . $this->title,
            $this->price,
            $this->flatSize,
            $this->rooms . ' Zimmer',
            'in ' . $this->address,
            $this->link
        ]);
    }
}
