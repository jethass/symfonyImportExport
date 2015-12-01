<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Article;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemoController extends Controller {

    /**
     * @Route("/exportcsvpro", name="export_pro")
     */
    public function exportCSVAction() {

        $now = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse();
        $response->setCallback(
            function () use ($em) {
                $total = $em->getRepository('AppBundle:Client')->getCount();
                $header = array('NOM', 'PRENOM', 'EMAIL', 'NAISSANCE', 'SEXE');
                $handle = fopen('php://output', 'r+');
                fputcsv($handle, $header, ";");
                $row = 1;
                while ($total >= 0) {
                    $clients = $em->getRepository('AppBundle:Client')->findAllClients(($row - 1) * 10, 10);
                    foreach ($clients as $client) {
                        fputcsv($handle, $client, ";");
                    }
                    $total-=10;
                    $row++;
                }
                fclose($handle);
            }
        );
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="doctors_export_' . $now->format('Y-m-d_H-i-s') . '".csv"');
        return $response;
    }

}
