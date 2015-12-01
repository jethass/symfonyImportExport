<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Article;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DefaultController extends Controller {

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
            $day = $now->format('d');
            $month = $now->format('m');
            $year = $now->format('Y');
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

}
