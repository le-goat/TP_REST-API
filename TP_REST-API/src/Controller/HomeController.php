<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HomeController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    public function apiRequestAction($apiUrl)
    {
        $client = HttpClient::create();

        $response = $client->request('GET', $apiUrl);

        $content = $response->getContent();

        $data = json_decode($content, true);

        return $data["results"];
    }

    function getPreviewCompany($resultData) {
        $resultsToDisplay = [];

        if (isset($resultData) && is_array($resultData)) {
            foreach ($resultData as $index => $item) {
                $nom = $item['nom_complet'] ?? 'Inconnu';
                $address = $item['siege']['adresse'] ?? 'Adresse inconnue';
                $siren = $item["siren"] ?? "inconnu";

                $resultsToDisplay[] = [
                    "name" => $nom,
                    "address" => $address,
                    "id" => $siren,
                ];
            }
        }

        return $resultsToDisplay;
    }

    function getDetailsCompany($resultData) {
        $detailsCompanyToDisplay = [];

        if (isset($resultData) && is_array($resultData)) {
            foreach ($resultData as $index => $item) {
                $corporateName = $item["nom_raison_sociale"] ?? 'Inconnu';
                $siren = $item["siren"] ?? 'Siren inconnue';
                $siret = $item["siege"]["siret"] ?? 'Siret inconnue';
                $address = $item['siege']['adresse'] ?? 'Adresse inconnue';


                $detailsCompanyToDisplay[] = [
                    "name" => $corporateName,
                    "siren" => $siren,
                    "siret" => $siret,
                    "address" => $address,
                    "id" => $siren,
                ];
            }
        }

        return $detailsCompanyToDisplay;
    }

    #[Route("/", name: "home")]
    public function index(): Response
    {
        return $this->render("home/home.html.twig", [
            "resultsCompanies" => "Nothing to display yet."
        ]);
    }

    #[Route("/result", name: "my_form")]
    public function myForm(Request $request)
    {
        $name = $request->request->get("name");
        $COMPANY_URL = "https://recherche-entreprises.api.gouv.fr/search?q=${name}";

        $data = $this->apiRequestAction($COMPANY_URL);

        dump($data);

        $resultPreviewCompany = $this->getPreviewCompany($data);
        $resultDetailsCompany = $this->getDetailsCompany($data);

        return $this->render("home/home.html.twig", [
            "resultsCompanies" => $resultPreviewCompany,
            "detailsCompany" => $resultDetailsCompany,
        ]);
    }
}