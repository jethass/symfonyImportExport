<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Article;
use AppBundle\Entity\Client;
use AppBundle\Entity\Adresses;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller {

    /**
     * @Route("/importexcel", name="excel_import")
     */
    public function importExcelAction() {
        $filename = $this->get('kernel')->getRootDir() . '/../web/excel/test.xls';

        $lignes = $this->get('excel_service')->excel_to_array($filename);

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
     * @Route("/exportexcel", requirements={"_format" = "excel"}, name="excel_export")
     */
    public function exportExcelAction() {
        if ($this->getRequest()->getMethod() == "POST") {
            $normalizer = new ObjectNormalizer();

            $now = new \DateTime();
            $day = $now->format('d');$month = $now->format('m');$year = $now->format('Y');
            $date = $day . '-' . $month . '-' . $year;

            $filename = 'export_articles_' . $date . '.xls';
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository('AppBundle:Article')->findAll();

            foreach ($data as $key => $obj) {
                $data[$key] = $normalizer->normalize($obj);
            }

            return $this->get('excel_service')->array_to_excel($data, $filename);
            die;
        }

        return $this->render('AppBundle:Default:exportexcel.html.twig');
    }
    
    /**
     * @Route("/importclientsexcel", name="excel_import_clients")
     */
    public function importExcelClientsAction() {
        $filename = $this->get('kernel')->getRootDir() . '/../web/excel/clients.xls';
        $lignes = $this->get('excel_service')->clients_excel_to_array($filename);
        $em = $this->getDoctrine()->getManager();
        $this->get('excel_service')->processDataImportClients($lignes,$em);
        die;
    }
    
    /**
     * @Route("/exportclientsexcel", requirements={"_format" = "excel"}, name="excel_export_clients")
     */
    public function exportExcelClientsAction() {
        if ($this->getRequest()->getMethod() == "POST") {
            $now = new \DateTime();$day = $now->format('d');$month = $now->format('m');$year = $now->format('Y');
            $date = $day . '-' . $month . '-' . $year;
            $filename = 'export_clients_' . $date . '.xls';
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository('AppBundle:Client')->findAll();
            $tabs=$this->get('excel_service')->processDataExportClients($data);
            
            return $this->get('excel_service')->clients_array_to_excel($tabs, $filename);
           die;
        }
        return $this->render('AppBundle:Default:exportexcelclient.html.twig');
    }
    
     
}
