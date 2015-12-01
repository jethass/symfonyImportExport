<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Article;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvController extends Controller {

    /**
     * @Route("/importcsv", name="csv_import")
     */
    public function importCSVAction() {

        $filename = $this->get('kernel')->getRootDir() . '/../web/csv/test.csv';
        $header=array('id', 'title', 'content', 'url');
        $lignes = $this->get('csv_service')->csv_to_array($filename, ',', $header);
        $em = $this->getDoctrine()->getManager();

        foreach ($lignes as $col) {
            $article = $em->getRepository('AppBundle:Article')->find($col['id']);
            if (!$article) {
                $article = new Article();
                $article->setTitle($col['title']);
                $article->setContent($col['content']);
                $article->setUrl($col['url']);
                $em->persist($article);
            } else {
                $article->setTitle($col['title']);
                $article->setContent($col['content']);
                $article->setUrl($col['url']);
            }
            $em->flush();
        }
        die;
    }

    /**
     * @Route("/exportcsv", name="csv_export")
     */
    public function exportCSVAction() {
        if ($this->getRequest()->getMethod() == "POST") {
            $normalizer = new ObjectNormalizer();
            $filename = 'export.csv';
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository('AppBundle:Article')->findAll();
            foreach ($data as $key => $obj) {
                $data[$key] = $normalizer->normalize($obj);
            }

            $header=array('id', 'title', 'content', 'url');
            $this->get('csv_service')->array_to_csv($data, $filename, ',', $header);

            die;
        }
        return $this->render('AppBundle:Default:exportcsv.html.twig');
    }


    /**
     * @Route("/exportcsvpro", name="export_pro")
    */
    public function exportProCSVAction() {
        $now = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();
        $response = new StreamedResponse();
        $response->setCallback(
            function () use ($em) {
                $normalizer = new ObjectNormalizer();
                $count = $em->getRepository('AppBundle:Client')->getCount();
                $total =intval($count[1]);
                $header = array('NOM', 'PRENOM', 'EMAIL','SEXE');
                $handle = fopen('php://output', 'r+');
                fputcsv($handle, $header, ";");
                $row = 1;
                while ($total >= 0) {
                    $clients = $em->getRepository('AppBundle:Client')->findAllClients(($row - 1) * 2, 2);
                    
                    foreach ($clients as $key => $obj) {
                        $clients[$key] = $normalizer->normalize($obj);
                    }
                    
                    foreach ($clients as $client) {
                        fputcsv($handle, $client, ";");
                    }
                    $total=$total-2;
                    $row++;
                }
                fclose($handle);
            }
        );
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        return $response;
    }
    
}
