<?php
/**
 * Created by PhpStorm.
 * User: luana
 * Date: 15/06/19
 * Time: 12:35
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SiteController extends AbstractController
{
    public function index() {
        $apiController = new ApiController();
        $data = $apiController->getAllOperationsFromApi();

        return $this->render('site/index.html.twig', array('data' => $data));
    }
}