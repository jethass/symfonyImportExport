<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Article;
use AppBundle\Entity\Client;
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
     * @Route("/exportclientexcel", requirements={"_format" = "excel"}, name="excel_export_client")
     */
    public function exportExcelClientsAction() {
        if ($this->getRequest()->getMethod() == "POST") {
            $now = new \DateTime();$day = $now->format('d');$month = $now->format('m');$year = $now->format('Y');
            $date = $day . '-' . $month . '-' . $year;
            $filename = 'export_clients_' . $date . '.xls';
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository('AppBundle:Client')->findAll();
            $tabs=array();
            foreach($data as $key=>$ligne){
                $tabs[$key]['id']=$ligne->getId();
                $tabs[$key]['nom']=$ligne->getNom();
                $tabs[$key]['prenom']=$ligne->getPrenom();
                $tabs[$key]['email']=$ligne->getEmail();
                $tabs[$key]['sexe']=$ligne->getSexe();
                $adresses=$ligne->getAdresses();
                if(sizeof($adresses) > 0){
                    foreach ($adresses as $adresse){
                       $tabs[$key]['adresses'][]=$adresse->getAdresse();
                    }
                }else{
                    $tabs[$key]['adresses']=array();
                }
            }
           return $this->get('excel_service')->clients_array_to_excel($tabs, $filename);
           die;
        }
        return $this->render('AppBundle:Default:exportexcelclient.html.twig');
    }
    
     
}
