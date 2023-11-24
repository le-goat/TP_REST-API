<?php

namespace App\Controller;

use App\Service\UrssafApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HomeController extends AbstractController
{

    private $urssafApiService;

    public function __construct(UrssafApiService $urssafApiService)
    {
        $this->urssafApiService = $urssafApiService;
    }

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
                $raisonSociale = $item["nom_raison_sociale"] ?? "non disponible";


                $detailsCompanyToDisplay[] = [
                    "name" => $corporateName,
                    "siren" => $siren,
                    "siret" => $siret,
                    "address" => $address,
                    "raisonSociale" => $raisonSociale,
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

    /**
     * @throws TransportExceptionInterface
     */
    #[Route("/result", name: "my_form")]
    public function myForm(Request $request)
    {
        $name = $request->request->get("name");
        $COMPANY_URL = "https://recherche-entreprises.api.gouv.fr/search?q=${name}";

        $data = $this->apiRequestAction($COMPANY_URL);

        $resultPreviewCompany = $this->getPreviewCompany($data);

        return $this->render("home/home.html.twig", [
            "resultsCompanies" => $resultPreviewCompany,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/{siren}', name: 'company_details')]
    public function companyDetails(string $siren, Request $request): Response
    {
        $COMPANY_URL = "https://recherche-entreprises.api.gouv.fr/search?q=${siren}";

        $data = $this->apiRequestAction($COMPANY_URL);
        $resultDetailsCompany = $this->getDetailsCompany($data);

        // Correction : utilisez "salaire_brut" au lieu de "salaire_brut" dans la ligne suivante
        $salaireBrut = $request->request->get("salaire_brut");

        $salaireCDI = $this->urssafApiService->calculateSalaryCDI($resultDetailsCompany[0]['siren'], $salaireBrut);
        $gratificationStage = $this->urssafApiService->calculateInternship($resultDetailsCompany[0]['siren']);
        $salaireAlternance = $this->urssafApiService->calculateWorkStudy($resultDetailsCompany[0]['siren'], $salaireBrut);
        $salaireCDD = $this->urssafApiService->calculateFixedTermContract($resultDetailsCompany[0]['siren'], $salaireBrut);

        return $this->render('home/company_details.html.twig', [
            "detailsCompany" => $resultDetailsCompany,
            "salaireCDI" => $salaireCDI,
            "gratificationStage" => $gratificationStage,
            "salaireAlternance" => $salaireAlternance,
            "salaireCDD" => $salaireCDD,
        ]);
    }

}