<?php
// Klasa do obslugi informacji o opoznieniach pociagow

class trains 
{
    // curl instance
    protected $curl;

    // xsl instance
    protected $xsl;

    // common instance
    protected $common;

    public $trainTypes = ['TLK' => 'TLK', 'IC' => 'Intercity', 'EIC' => 'Express Intercity', 'EIP' => 'Express Intercity Premium', 'R' => 'Regio', 'SKM' => 'Szybka Kolej Miejska Trójmiasto', 'KM' => 'Koleje Mazowiecie', 'SKW' => 'Szybka Kolej Miejska Warszawa', 'LKA' => 'Łódzka Kolej Aglomeracyjna', 'KW' => 'Koleje Wielkopolskie', 'KD' => 'Koleje Dolnośląskie', 'KS' => 'Koleje Śląskie'];

    const ROZKLAD_CONNECTION_BASE_URL = 'http://rozklad-pkp.pl/pl/ts?';

    const INFOPASAZER_CONNECTION_BASE_URL = 'http://infopasazer.intercity.pl/index.php?';

    const GET_NEAREST_STATION_REAL_CHECK_MINIMUM_DELAY = 10;

    public function __construct() {
        $this->curl = new curl();
        $this->xsl = new xsl();
        $this->common = new common();
        $this->db = new db();
    }

    /**
     * Get a list of stations for a specific train.
     *
     * @param string $number
     *
     * @return array
     */
    public function getStationsByTrainNumber($number) {
        $date = date('d') . '.' . date('m') . '.' . date('y');
        $connection = $this->curl->connect(self::ROZKLAD_CONNECTION_BASE_URL . 'start=&trainname=' . $number . '&stationname=&date=' . $date . '&dateStart=' . $date . '&dateEnd=' . $date . '&REQ0JourneyDate=' . $date . '&selectDate=oneday&start=#focus');
        try {
            if (empty($connection)) {
                throw new Exception('Empty connection results in ' . __CLASS__ .' -> ' . __FUNCTION__);
            }
        } catch (Exception $e) {
            new monolog('WARNING', $e->getMessage());
            return [];
        }
        // Exception handler - no results for the query from rozklad-pkp
        if (preg_match('^Brak wyniku dla zapytania^', $connection)) {
            return ['error' => 'rozklad_pkp_no_matches_for_query'];
        }
        preg_match_all('^\<a href\=\"(.*)\" class=\"train\-details\-link\"\>([a-zA-Z ]{1,10}' . $this->common->stripNonDigitCharacters($number) . ')\<\/a\>^', str_replace('&nbsp;', '', strip_tags($connection, '<a>')), $stationListUrls);
        // Expected match results
        // $stationListUrls[0] - entire string, $stationListUrls[1] - train track url, $stationListUrls[2] - train name

        $stationListUrl = $this->getStationListForSupportedTrainType($stationListUrls);
        try {
            if (!empty($stationListUrl)) {
                $connection = $this->curl->connect($stationListUrl);
            } else {
                throw new Exception('Empty results for $stationListUrl preg_match in ' . __CLASS__ .' -> ' . __FUNCTION__ . ' => $number value = ' . $number);
            }
        } catch (Exception $e) {
            new monolog('ERROR', $e->getMessage());
            return ['error' => 'train_not_supported'];
        }

        try {
            if (empty($connection)) {
                throw new Exception('Empty connection results (2) in ' . __CLASS__ .' -> ' . __FUNCTION__);
            }
        } catch (Exception $e) {
            new monolog('WARNING', $e->getMessage());
            return [];
        }

        preg_match_all('^<td>\s<a href=".*">(.*)</a>\s</td>\s<td>\s(.*)\s</td>\s<td>\s(.*)\s</td>^', strip_tags($connection, '<table><thead><tbody><th><tr><td><a>'), $stationList);
        // Expected match results
        // $stationList[0] - entire string, $stationList[1] - station name, $stationList[2] - arrival time, $stationList[3] - departure time
        $stationList = $this->common->stripHtmlInArray($stationList, 2);

        try {
            if (!empty($stationList)) {
                return ['stations' => $stationList[1], 'arrival_time' => $stationList[2], 'departure_time' => $stationList[3]];
            } else {
                throw new Exception('Empty results for $stationList preg_match in ' . __CLASS__ .' -> ' . __FUNCTION__);
            }
        } catch (Exception $e) {
            new monolog('ERROR', $e->getMessage());
            return [];
        }
    }

    /**
     * Get a list of trains for a specific train station.
     *
     * @param string $name
     *
     * @return array
     */
    public function getTrainsByStationName($name) {
        $connection = $this->curl->connect(self::INFOPASAZER_CONNECTION_BASE_URL . 'p=stations&q=' . urlencode($name));
        try {
            if (empty($connection)) {
                throw new Exception('Empty connection results in ' . __CLASS__ .' -> ' . __FUNCTION__);
            }
        } catch (Exception $e) {
            new monolog('WARNING', $e->getMessage());
            return [];
        }
        if (preg_match('^Nie znaleziono wyników pasujących do wyszukiwanej frazy^', $connection)) {
            return ['error' => 'infopasazer_station_not_found'];
        }
        preg_match('^window\.location\=\'\?p\=station\&id\=([0-9]{1,10})\'^', $connection, $stationId);
        // Expected match results
        // $stationId[0] - entire string, $stationId[1] - station id

        try {
            if (!empty($stationId)) {
                $connection = $this->curl->connect(self::INFOPASAZER_CONNECTION_BASE_URL . 'p=station&id=' . $stationId[1]);
            } else {
                throw new Exception('Empty results for $stationId preg_match in ' . __CLASS__ .' -> ' . __FUNCTION__);
            }
        } catch (Exception $e) {
            new monolog('ERROR', $e->getMessage());
            return ['error' => 'empty_station_id'];
        }

        try {
            if (empty($connection)) {
                throw new Exception('Empty connection results (2) in ' . __CLASS__ .' -> ' . __FUNCTION__);
            }
        } catch (Exception $e) {
            new monolog('WARNING', $e->getMessage());
            return ['error' => 'empty_connection'];
        }
        // Exception handler - no results from inforpasazer
        if (preg_match('^Brak informacji^', $connection)) {
            return ['error' => 'infopasazer_no_information_for_this_station'];
        } elseif (preg_match('^Nie znaleziono^', $connection)) {
            return ['error' => 'infopasazer_station_error'];
        }

        return $this->getTrainsByStationNameHelper($connection);
        //dodac nazwenictwo kluczy tablicy - odjazdy przyjazdy
    }

    /**
     * TODO.
     *
     * @param string $number
     *
     * @return array
     */
    private function getTrainsByStationNameHelper($trains) {
        $result = [];
        $mainContentTables = $this->xsl->parseHtmlByElement($trains, '//table');
        foreach ($mainContentTables as $table) {
            try {
                if (preg_match('^Przyjazd^', $table)) {
                    $directionType = 'arrivals';
                } elseif (preg_match('^Odjazd^', $table)) {
                    $directionType = 'departures';
                } else {
                    throw new Exception('Empty results for preg_match in ' . __CLASS__ .' -> ' . __FUNCTION__);
                }
            } catch (Exception $e) {
                new monolog('ERROR', $e->getMessage());
                return [];
            }
            $rows = $this->xsl->parseHtmlByElement($table, '//tr');
            foreach ($rows as $rowKey => $row) {
                $cells = $this->xsl->parseHtmlByElement($row, '//td');
                if (!empty($cells)) {
                    $result[$directionType][$rowKey] = $cells;
                }
            }
        }
        return $this->getTrainsByStationNameArrayParser($this->common->stripHtmlInArray($result, 3));
    }

    /**
     * TODO.
     *
     * @param string $number
     *
     * @return array
     */
    private function getTrainsByStationNameArrayParser(array $array) {
        $keys = ['train_number', 'company', 'date', 'road', 'time', 'delay'];
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    try {
                        $combine = array_combine($keys, $value2);
                        if (is_array($combine)) {
                            $array[$key][$key2] = $combine;
                        } else {
                            throw new Exception('Empty array_combine results in ' . __CLASS__ .' -> ' . __FUNCTION__);
                        }
                    } catch (Exception $e) {
                        new monolog('ERROR', $e->getMessage());
                        return [];
                    }
                }
            }
            return $array;
        }
        return [];
    }

    /**
     * TODO.
     *
     * @param string $number
     *
     * @return array
     */
    public function getTrainNearestStation($number, array $stations) {
        $currentTime = date('H:i');
        $departureTime = current($stations['departure_time']);

        // Check if train is supported - destination and arrival stations need to be in Poland
        $departureStation = current($stations['stations']);
        $arrivalStation = end($stations['stations']);
        if (false === $this->checkIfStationIsConfirmed($departureStation) || false === $this->checkIfStationIsConfirmed($arrivalStation)) {
            if (false === $this->checkIfStationIsSupported($departureStation) || false === $this->checkIfStationIsSupported($arrivalStation)) {
                return ['error' => 'train_not_supported'];
            }
        }

        // Check if the train is not delayed at the departure station
        // Temporarily disabled
        /*
        $departureStationSchedule = $this->getTrainsByStationName($departureStation);
        // Check if getTrainsByStationName did not return an error
        if (!isset($departureStationSchedule['error'])) {
            if (true === $this->checkIfTrainIsInStationSchedule($number, $departureStationSchedule)) {
                return ['success' => 'train delayed in ' . $departureStation];
            }
        }*/

        $doRealCheck = false;
        if (strtotime($departureTime) < strtotime($currentTime)) {
            // Czy pociag jest opozniony na ostatniej stacji?
            $arrivalStationSchedule = $this->getTrainsByStationName($arrivalStation);
            if (!isset($arrivalStationSchedule['error'])) {
                if (true === $this->checkIfTrainIsInStationSchedule($number, $arrivalStationSchedule)) {
                    $departureStationDelay = $this->getTrainDelayFromStation($number, $arrivalStationSchedule['arrivals']);
                    if (!empty($departureStationDelay)) {
                        $doRealCheck = true;
                    } else {
                        if (self::GET_NEAREST_STATION_REAL_CHECK_MINIMUM_DELAY < $departureStationDelay) {
                            $doRealCheck = true;
                        }
                    }
                }
            }
            // Check the actual readings from infopasazer
            if (true === $doRealCheck) {
                foreach ($stations['stations'] as $key => $value) {
                    $station = $this->getTrainsByStationName($value);
                    if (!empty($station['arrivals'])) {
                        if (true === $this->checkIfTrainIsInStationSchedule($number, $value)) {
                            return ['success' => ['key' => $key, 'station' => $value, 'check_type' => 'real']];
                        }
                    }
                }
            }
            //Check the timetable
            foreach ($stations['arrival_time'] as $key => $value) {
                if (true === $this->common->checkIfStringIsValidDateTime($value, 'H:i')) {
                    if (strtotime($value) > strtotime($currentTime)) {
                        return ['success' => ['key' => $key, 'station' => $stations['stations'][$key], 'check_type' => 'timetable']];
                    }
                }
            }
            return ['error' => 'train_arrived_at_destination'];
        } else {
            return ['error' => 'train_not_departed_yet'];
        }
    }

    /**
     * Check if train is in a station schedule (infopasazer).
     *
     * @param string       $number
     * @param array|string $station
     *
     * @return array
     */
    private function checkIfTrainIsInStationSchedule($number, $station) {
        if (!is_array($station)) {
            $station = $this->getTrainsByStationName($station);
        }
        foreach ($station as $directionType => $trains) {
            if (is_array($trains)) {
                foreach ($trains as $train) {
                    $similarNumbers = $this->getSimilarTrainNumbers($number);
                    if (false !== strpos($train['train_number'], $number)) {
                        return true;
                    } elseif (!empty($similarNumbers) && (false !== strpos($train['train_number'], $similarNumbers[0]) || false !== strpos($train['train_number'], $similarNumbers[1]))) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * TODO.
     *
     * @param string $number
     *
     * @return array
     */
    public function getTrainFromStationSchedule($number, array $schedule) {
        $similarNumbers = $this->getSimilarTrainNumbers($number);
        foreach ($schedule as $train) {
            if (false !== strpos($train['train_number'], $number)) {
                return $train;
            } else {
                if (!empty($similarNumbers)) {
                    if (false !== strpos($train['train_number'], $similarNumbers[0]) || false !== strpos($train['train_number'], $similarNumbers[1])) {
                        return $train;
                    }
                }
            }
        }
        return [];
    }

    /**
     * When a query from rozklad-pkp returns more trains than one, we need to select only the one that is supported.
     *
     * @param string $number
     *
     * @return array
     */
    private function getStationListForSupportedTrainType(array $stationLists) {
        if (!empty($stationLists[0])) {
            foreach ($stationLists[0] as $key => $value) {
                if (true === $this->checkIfTrainNumberIsSupported($this->common->stripHtmlFromString($value))) {
                    return $stationLists[1][$key];
                }
            }
        }
        return '';
    }

    /**
     * Check if provided train number is supported by us.
     *
     * @param string $number
     *
     * @return array
     */
    private function checkIfTrainNumberIsSupported($number) {
        foreach (array_keys($this->trainTypes) as $type) {
            if ($this->common->stripNonLetterCharacters($number) == $type) {
                return true;
            }
        }
        return false;
    }

    private function checkIfTrainRunsAtNight($schedule) {
        
    }

    /**
     * TODO.
     *
     * @param string $number
     *
     * @return array
     */
    private function getTrainDelayFromStation($trainNumber, array $station) {
        foreach ($station as $key => $value) {
            if (false !== strpos($value['train_number'], $trainNumber)) {
                return $this->common->stripNonDigitCharacters($value['delay']);
            }
        }
        return '';
    }

    /**
     * TODO.
     *
     * @param string $number
     *
     * @return array
     */
    private function checkIfStationIsSupported($stationName) {
        $station = $this->getTrainsByStationName($stationName);
        if (!empty($station) && !isset($station['error'])) {
            return true;
        }
        return false;
    }

    /**
     * Get two other possible train numbers, one lower and the other one higher than the one provided.
     *
     * @param string $number
     *
     * @return array
     */
    public function getSimilarTrainNumbers($trainNumber) {
        $previousNumber = strval($trainNumber - 1);
        $nextNumber = strval($trainNumber + 1);
        if (!empty($previousNumber) && !empty($nextNumber)) {
            return [$previousNumber, $nextNumber];
        }
        return [];
    }

    public static function getNearesStationRealCheckMinimumDelay() {
        return self::GET_NEAREST_STATION_REAL_CHECK_MINIMUM_DELAY;
    }

    public function saveTrainStations(array $stations) {
        $sql = '
            INSERT INTO 
                `train_stations` 
                (`name`) 
            VALUES 
                (:station)
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->bindParam(':station', $station, PDO::PARAM_STR);
        
        foreach ($stations as $station) {
            $results = $query->execute();
        }
    }

    /**
     * Check in the database if the provided station is in train_stations table.
     *
     * @param string $station
     *
     * @return bool
     */
    public function checkIfStationIsConfirmed($station) {
        $sql = '
            SELECT 
                `name`
            FROM
                `train_stations`
            WHERE
                `name` = :station
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->bindParam(':station', $station, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetch(PDO::FETCH_COLUMN);
        if (!empty($results)) {
            return true;
        }
        return false;
    }

    /**
     * When $train (in search.html) has no results, get temporary data.
     *
     * @return array
     */
    public function getTemporaryTrainData() {
        return ['company' => 'N.d.', 'train_number' => '', 'road' => '', 'delay' => 'N.d.', 'origin' => 'manual'];
    }

    /**
     * Save train details to database.
     *
     * @param array $train
     *
     * @return bool
     */
    public function saveTrain(array $train) {
        $currentTimetableDetails = $this->getCurrentTimetableDetails();
        $trainNumbers = $this->getTrainNumbers($train['train_number']);
        $sql = '
            INSERT INTO 
                `trains` 
                (`timetables_id`, `company`, `name`, `number`, `number_alternative`, `road`) 
            VALUES 
                (:timetables_id, :company, :name, :number, :number_alternative, :road)
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->bindParam(':timetables_id', $currentTimetableDetails['id'], PDO::PARAM_INT);
        $query->bindParam(':company', $train['company'], PDO::PARAM_STR);
        $query->bindParam(':name', $train['train_number'], PDO::PARAM_STR);
        $query->bindParam(':number', $trainNumbers[0], PDO::PARAM_STR);
        $query->bindParam(':number_alternative', $trainNumbers[1], PDO::PARAM_STR);
        $query->bindParam(':road', $train['road'], PDO::PARAM_STR);
        $results = $query->execute();
        
        return $results;
    }

    /**
     * Get current timetable details.
     *
     * @return array
     */
    public function getCurrentTimetableDetails() {
        $sql = '
            SELECT 
                `id`,
                `valid_to`,
                `created_on`
            FROM
                `timetables`
            WHERE
                `valid_to` >= CURDATE()
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->execute();
        $results = $query->fetch(PDO::FETCH_ASSOC);
        if (!empty($results)) {
            return $results;
        }
        return [];
    }

    /**
     * Check if train has some other number (contains the / sign in the number).
     *
     * @param string $number
     *
     * @return bool
     */
    public function checkIfTrainHasOtherNumber($number) {
        if (false !== strpos($number, '/')) {
            return true;
        }
        return false;
    }

    /**
     * Get two possible numbers for a train.
     *
     * @param string $number
     *
     * @return array
     */
    public function getTrainNumbers($number) {
        if (false !== strpos($number, '/')) {
            preg_match('^([0-9]{1,5})\/([0-9]{1})^', $number, $numberResults);
            if (!empty($numberResults)) {
                $mainNumber = $numberResults[1];
                $otherNumber = substr($mainNumber, 0, -1) . $numberResults[2];
                if (1 === preg_match('^[0-9]{1,5}^', $mainNumber) && 1 === preg_match('^[0-9]{1,5}^', $otherNumber)) {
                    return [$mainNumber, $otherNumber];
                }
            }
        } elseif (1 === preg_match('^[0-9]{1,5}^', $number)) {
            preg_match('^[0-9]{1,5}^', $number, $numberResults);
            if (!empty($numberResults)) {
                $mainNumber = $numberResults[0];
                if (1 === preg_match('^[0-9]{1,5}^', $mainNumber)) {
                    return [$mainNumber, NULL];
                }
            }
        }
        return [];
    }
}