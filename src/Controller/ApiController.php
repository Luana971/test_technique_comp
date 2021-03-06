<?php
/**
 * Created by PhpStorm.
 * User: luana
 * Date: 15/06/19
 * Time: 12:30
 */

namespace App\Controller;


use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    public function getApiData()
    {
        $apiContent = file_get_contents("https://agrcf.lib.id/exercice@dev/");
        $data = json_decode($apiContent, true);

        return $data;
    }

    public function getAllOperationsFromApi()
    {
        /* get all JSON from API url */
        $allOperations = array();
        $data = $this->getApiData();

        /* store every operations in associative array */
        foreach ($data as $key => $datum) {
            if ($key !== 'statut') {
                foreach ($datum as $operation) {
                    $allOperations[$operation['RIB']][$operation['Libelle']] = array(
                        'date' => $operation['Date'],
                        'montant' => $operation['Montant']
                    );
                }
            }
        }

        return $allOperations;
    }

    /* get operations for asked rib and period */
    public function getNeededOperations(Request $request)
    {
        $results = array();
        if ($request->isXmlHttpRequest()) {
            $rib = $request->request->get('rib');

            $startDate = $request->request->get('startDate');
            if ($startDate) {
                $startDate = new \DateTime($startDate);
                $startDate = $startDate->getTimestamp();
            }

            $endDate = $request->request->get('endDate');
            if ($endDate) {
                $endDate = new \DateTime($endDate);
                $endDate = $endDate->getTimestamp();
            }

            $allOperations = $this->getAllOperationsFromApi();

            if ($rib) {
                if ($startDate > $endDate || $endDate < $startDate) {
                    $response = 'Veuillez sélectionner une période valide.';
                } else {
                    /*
                     * create a new associative array that only contains the operations of the rib that has been asked
                     * use the date timeswamp as an array key to facilitate chronological handling
                     */
                    foreach ($allOperations[$rib] as $key => $operation) {
                        $date = str_replace('/', '-', $operation['date'] );
                        $date = new \DateTime($date);
                        $date = $date->getTimestamp();

                        if (is_int($startDate)) {
                            if ($endDate) {
                                if ($date >= $startDate && $date <= $endDate) {
                                    $results[$date][$key] = array(
                                        'montant' => $operation['montant'],
                                        'date' => $operation['date'],
                                    );
                                }
                            } else {
                                if ($date >= $startDate) {
                                    $results[$date][$key] = array(
                                        'montant' => $operation['montant'],
                                        'date' => $operation['date'],
                                    );
                                }
                            }
                        } elseif (is_int($endDate)) {
                            if ($date <= $endDate) {
                                $results[$date][$key] = array(
                                    'montant' => $operation['montant'],
                                    'date' => $operation['date'],
                                );
                            }
                        } else {
                            $results[$date][$key] = array(
                                'montant' => $operation['montant'],
                                'date' => $operation['date'],
                            );
                        }
                    }

                    /* return help messages if error */
                    if (empty($results)) {
                        $response = 'Aucune opération réalisée sur cette période.';
                    } else {
                        $response = $results;
                    }
                }
            } else {
                $response = 'Veuillez sélectionner un RIB.';
            }

        } else {
            $response = 'Une erreur s\'est produite, veuillez réessayer.';
        }

        return $response;
    }

    public function sortByDate($a, $b)
    {
        return $b - $a;
    }

    public function operationsList(Request $request)
    {
        $operations = $this->getNeededOperations($request);

        /* if $operations is a sting it means there was an error */
        if (!is_string($operations)) {
            uksort($operations, array($this, "sortByDate"));

            foreach ($operations as $key => $operation) {

                /* add the revenue and expense fields to each operation */
                foreach ($operation as $libelle => $transaction) {
                    if ($transaction['montant'] > 0) {
                        $revenue = $transaction['montant'];
                    } else {
                        $revenue = 0;
                    }
                    $operations[$key][$libelle]['recette'] = $revenue;

                    if ($transaction['montant'] < 0) {
                        $expense = trim($transaction['montant'], '-');
                    } else {
                        $expense = 0;
                    }
                    $operations[$key][$libelle]['depense'] = $expense;
                }
            }
        }

        return new Response(json_encode($operations));
    }

    public function operationsTotal(Request $request)
    {
        $operations = $this->getNeededOperations($request);

        /* if $operations is a sting it means there was an error */
        if (!is_string($operations)) {
            $total = 0;

            foreach ($operations as $operation) {
                foreach ($operation as $transaction) {
                    $amount = (float)str_replace(',', '.', $transaction['montant']);
                    $total += $amount;
                }
            }

            return new Response($total);
        }

        return new Response(json_encode($operations));
    }
}