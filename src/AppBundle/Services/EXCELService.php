<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class EXCELService {

    protected $phpexcel;

    public function __construct($phpexcel) {

        $this->phpexcel = $phpexcel;
    }

    public function excel_to_array($input_filename) {
        $phpExcelObject = $this->phpexcel->createPHPExcelObject($input_filename);
        $data = $phpExcelObject->getActiveSheet()->toArray();
        //cration tab header en minuscule
        foreach ($data[0] as $key => $value) {
            $data[0][$key] = strtolower($value);
        }
        $headers = array_values($data[0]);
        //delete header from data;
        unset($data[0]);

        //parcours de data et injection d'id correspondant pour chaque colone
        foreach ($data as $key => $value) {
            $data[$key] = array_combine($headers, $value);
        }
        return $data;
    }

    public function array_to_excel($array, $output_filename) {

        $phpExcelObject = $this->phpexcel->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("hassine")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.");


        $sheet = $phpExcelObject->setActiveSheetIndex(0);
        //generation alphapets tab;     
        $alphabets = range('A', 'Z');
        //generation tableau des keys des entètes de fichier excel
        $keys_header = array_keys($array[0]);

        //creation header fichier excel:
        foreach ($keys_header as $key => $value) {
            $sheet->setCellValue($alphabets[$key] . '1', strtoupper($value));
        }

        //creation core fichier excel
        foreach ($array as $keyligne => $ligne) {
            foreach ($keys_header as $keycolone => $colone) {
                $sheet->setCellValue($alphabets[$keycolone] . ($keyligne + 2), $ligne[$colone]);
            }
        }

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->phpexcel->createWriter($phpExcelObject, 'Excel5');

        // create the response
        $response = $this->phpexcel->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $output_filename);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
    
     public function clients_array_to_excel($array, $output_filename) {

        $phpExcelObject = $this->phpexcel->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("hassine")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.");


        $sheet = $phpExcelObject->setActiveSheetIndex(0);
        //generation alphapets tab;     
        $alphabets = range('A', 'Z');
        //generation tableau des keys des entètes de fichier excel
        $keys_header = array_keys($array[0]);
        

        //creation header fichier excel:
        foreach ($keys_header as $key => $value) {
            $sheet->setCellValue($alphabets[$key] . '1', strtoupper($value));
        }
        //creation core fichier excel
        foreach ($array as $keyligne => $ligne) {
            foreach ($keys_header as $keycolone => $colone) {
                if($colone=='adresses'){
                    $contentColone=$ligne[$colone];
                    if(is_array($contentColone)){
                       $souslignes=  implode(";", $contentColone);
                       $sheet->setCellValue($alphabets[$keycolone] . ($keyligne + 2), $souslignes);
                    }else{
                      $sheet->setCellValue($alphabets[$keycolone] . ($keyligne + 2), 'pas adresses');   
                    }
                }else{
                    $sheet->setCellValue($alphabets[$keycolone] . ($keyligne + 2), $ligne[$colone]);
                }
            }
        }

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->phpexcel->createWriter($phpExcelObject, 'Excel5');

        // create the response
        $response = $this->phpexcel->createStreamedResponse($writer);

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $output_filename);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

}
