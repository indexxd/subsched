<?php

namespace App\Controller;

use App\Service\APIDatabaseUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UpdateController extends AbstractController
{
    /**
     * @Route("/api/update", name="update", methods={"POST"})
     */
    public function index(APIDatabaseUpdater $updater)
    {
        // do not run - API is not active
        // $updater->updateDatabase();

        return $this->json([]);
    }
}
