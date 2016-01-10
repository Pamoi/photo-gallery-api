<?php

namespace AppBundle\Entity;

interface PublicJsonInterface
{
    /**
     * Create an array ready to be encoded to JSON containing the public data of the entity.
     *
     * @return array An array containing the public data of the entity.
     */
    public function toJson();
}