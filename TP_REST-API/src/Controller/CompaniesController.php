<?php

namespace App\Controller;

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CompaniesController extends AbstractController
{
    #[Route("/companies", name: "companies")]
    public function getCompanies(): Response
    {
        $rootDir = dirname(__DIR__, 2); // Monte d'un niveau dans la hiérarchie des répertoires
        $json_data = file_get_contents($rootDir . "/companies.json");
        $companies = json_decode($json_data, true);

        return $this->json($companies);
    }
}
