<?php

class user
{
    public function __construct (
        public int $id, public string $name, public string $email
    ) {}
}