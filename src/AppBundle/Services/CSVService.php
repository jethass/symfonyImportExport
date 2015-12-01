<?php

namespace AppBundle\Services;

class CSVService {

    public function csv_to_array($input_filename, $delimiter, $header) {
        if (!file_exists($input_filename) || !is_readable($input_filename))
            return FALSE;

        $data = array();
        if (($handle = fopen($input_filename, 'r')) !== FALSE) {
            while (($ligne = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {

                $data[] = array_combine($header, $ligne);
            }
            fclose($handle);
        }
        unset($data[0]);
        return $data;
    }

    public function array_to_csv($array, $output_filename, $delimiter, $header) {

        $handle = fopen('php://memory', 'w');
        fputcsv($handle, $header, $delimiter);
        foreach ($array as $line) {
            fputcsv($handle, $line, $delimiter);
        }
        /** rewrind the "file" with the csv lines * */
        fseek($handle, 0);
        /** modify header to be downloadable csv file * */
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="' . $output_filename . '";');
        /** Send file to browser for download */
        fpassthru($handle);
    }

}
