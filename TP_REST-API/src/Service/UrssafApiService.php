<?php
/*
namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpClient\HttpClient;

class UrssafApiService
{
    private string $URSSAF_API_URL = 'https://mon-entreprise.urssaf.fr/api/v1/evaluate';

    public function calculateSalaryCDI($siren, $salaireBrut): array
    {
        $payload = [
            'situation' => [
                'salarié . contrat . salaire brut' => [
                    'valeur' => $salaireBrut,
                    'unité' => '€ / mois',
                ],
                'salarié . contrat' => '\'CDI\'',
            ],
            'expressions' => [
                'salarié . rémunération . net . à payer avant impôt',
                'salarié . cotisations . salarié',
                'salarié . coût total employeur',
            ],
        ];

        return $this->sendUrssafApiRequest($siren, $payload);
    }

    public function calculateInternshipGratification($siren)
    {
        $payload = [
            'situation' => [
                'salarié . contrat' => '\'stage\'',
            ],
            'expressions' => [
                'salarié . contrat . stage . gratification minimale',
            ],
        ];

        return $this->sendUrssafApiRequest($siren, $payload);
    }

    public function calculateWorkStudy($siren, $salaireBrut)
    {
        $payload = [
            'situation' => [
                'salarié . contrat . salaire brut' => [
                    'valeur' => $salaireBrut,
                    'unité' => '€ / mois',
                ],
                'salarié . contrat' => '\'apprentissage\'',
            ],
            'expressions' => [
                'salarié . rémunération . net . à payer avant impôt',
                'salarié . cotisations . salarié',
                'salarié . coût total employeur',
            ],
        ];

        return $this->sendUrssafApiRequest($siren, $payload);
    }

    public function calculateFixedTermContract($siren, $salaireBrut)
    {
        $payload = [
            'situation' => [
                'salarié . contrat . salaire brut' => [
                    'valeur' => $salaireBrut,
                    'unité' => '€ / mois',
                ],
                'salarié . contrat' => '\'CDD\'',
            ],
            'expressions' => [
                'salarié . rémunération . net . à payer avant impôt',
                'salarié . cotisations . salarié',
                'salarié . coût total employeur',
                'salarié . rémunération . indemnités CDD . fin de contrat',
            ],
        ];

        return $this->sendUrssafApiRequest($siren, $payload);
    }

    private function sendUrssafApiRequest($siren, $payload): array
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('POST', $this->URSSAF_API_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => ['siren' => $siren, 'data' => $payload],
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $responseContent = $e->getResponse() ? $e->getResponse()->getContent(false) : 'Aucune réponse reçue';
            return ['error' => $e->getMessage(), 'response' => $responseContent];
        }
    }
}*/


namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UrssafApiService
{
    private string $URSSAF_API_URL = 'https://mon-entreprise.urssaf.fr/api/v1/evaluate';

    public function calculateSalaryCDI($siren, $salaireBrut)
    {
        $payload = $this->buildPayload('CDI', $salaireBrut);
        return $this->sendUrssafApiRequest($siren, $payload);
    }

    public function calculateInternship($siren)
    {
        $payload = $this->buildPayload('stage');
        return $this->sendUrssafApiRequest($siren, $payload);
    }

    public function calculateWorkStudy($siren, $salaireBrut)
    {
        $payload = $this->buildPayload('apprentissage', $salaireBrut);
        return $this->sendUrssafApiRequest($siren, $payload);
    }

    public function calculateFixedTermContract($siren, $salaireBrut)
    {
        $payload = $this->buildPayload('CDD', $salaireBrut);
        return $this->sendUrssafApiRequest($siren, $payload);
    }

    private function buildPayload($contractType, $salaireBrut = null)
    {
        $payload = [
            'situation' => [
                'salarié . contrat . salaire brut' => [
                    'valeur' => $salaireBrut,
                    'unité' => '€ / mois',
                ],
                'salarié . contrat' => '\'' . $contractType . '\'',
            ],
            'expressions' => [
                'salarié . rémunération . net . à payer avant impôt',
                'salarié . cotisations . salarié',
                'salarié . coût total employeur',
            ],
        ];

        return $payload;
    }

    private function sendUrssafApiRequest($siren, $payload)
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('POST', $this->URSSAF_API_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $responseContent = $e->getResponse() ? $e->getResponse()->getContent(false) : 'Aucune réponse reçue';
            return ['error' => $e->getMessage(), 'response' => $responseContent];
        }
    }
}
