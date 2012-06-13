<?php
/**
 * common classes
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Creae class used to load data
    class Data {

    // The following fields are (in order) the fields returned from the backend on
    // a standard query.
        public $title;
        public $subtitle;
        public $description;
        public $programid;
        public $starttime;

        public function __construct($data) {
            $this->title           = trim($data[0]);    # program name/title
            $this->subtitle        = $data[1];          # episode name
            $this->description     = $data[2];          # episode description
            $this->programid       = $data[3];          # programid 
            $this->starttime       = $data[4];          # program start time
        }
    }
