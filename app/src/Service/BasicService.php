<?php
namespace App\Service;

class BasicService
{
    protected $domain;
    protected $errors = array();

    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    public function getErrors() : array
    {
        return $this->errors;
    }
}
