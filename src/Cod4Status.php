<?php

namespace MTX_GHOST\Cod4Status;

class Cod4Status
{
    private string $ip;
    private int $port;
    private int $timeout = 1;
    private $serverStatus;
    private string $protocol = 'udp';
    private $data;
    private $meta;
    private $handle;
    private $serverData = array();
    private $players = array();

    public function __construct(string $ip, int $port)
    {

        $this->ip = $ip;
        $this->port = $port;

        $this->serverStatus = $this->connect();

        if ($this->serverStatus) {

            $this->setServerData();
        }
    }

    private function connect()
    {
        $error = false;

        if (!empty($this->ip) && !empty($this->port)) {

            $handle = @fsockopen($this->protocol . '://' . $this->ip, $this->port);

            if ($handle) {

                $this->handle = $handle;

                socket_set_timeout($handle, $this->timeout);
                stream_set_blocking($handle, 1);
                stream_set_timeout($handle, 5);

                fputs($handle, "\xFF\xFF\xFF\xFFgetstatus\x00");
                fwrite($handle, "\xFF\xFF\xFF\xFFgetstatus\x00");

                $this->data = fread($handle, 8192);
                $this->meta = stream_get_meta_data($handle);

                $counter = 8192;

                while (!feof($handle) && !$error && $counter < $this->meta['unread_bytes']) {
                    $this->data .= fread($handle, 8192);
                    $this->meta = stream_get_meta_data($handle);

                    if ($this->meta['timed_out']) {
                        $error = true;
                    }

                    $counter += 8192;
                }
            }
        }

        if ($error) {
            echo 'Request timed out.';
            return false;
        } else {
            if (strlen(trim($this->data)) == 0) {
                echo 'No data received from server.';
                return false;
            } else {
                return true;
            }
        }

        fclose($handle);
    }

    private function setServerData()
    {
        $this->serverData = explode("\n", $this->data);

        $tempPlayersArray = array();

        for ($i = 2; $i <= sizeof($this->serverData) - 1; $i++) {

            $tempPlayersArray[sizeof($tempPlayersArray)] = trim($this->serverData[$i]);
        }

        $tempDataArray = array();
        $tempDataArray = explode("\\", $this->serverData[1]);

        $this->serverData = array();

        foreach ($tempDataArray as $key => $value) {

            if (fmod($key, 2) == 1) {
                $t = $key + 1;

                $this->serverData[$value] = $tempDataArray[$t];
            }
        }

        $this->serverData['sv_hostname'] = $this->colorCode($this->serverData['sv_hostname']);

        foreach ($tempPlayersArray as $key => $value) {

            if (strlen(trim($value)) > 1) {

                $temp = explode(' ', $value);

                $pos = strpos($value, '"') + 1;
                $endpos = strlen($value) - 1;

                $this->players[sizeof($this->players)] = [
                    "name" => substr($value, $pos, $endpos - $pos),
                    "score" =>  $temp[0],
                    "ping" =>  $temp[1],
                ];

            }
        }
    }

    public function colorCode($string)
    {

        $string .= "^";

        $find = array(
            '/\^0(.*?)\^/is',
            '/\^1(.*?)\^/is',
            '/\^2(.*?)\^/is',
            '/\^3(.*?)\^/is',
            '/\^4(.*?)\^/is',
            '/\^5(.*?)\^/is',
            '/\^6(.*?)\^/is',
            '/\^7(.*?)\^/is',
            '/\^8(.*?)\^/is',
            '/\^9(.*?)\^/is',
        );

        $replace = array(
            '<span style="color:#000000;">$1</span>^',
            '<span style="color:#F65A5A;">$1</span>^',
            '<span style="color:#00F100;">$1</span>^',
            '<span style="color:#EFEE04;">$1</span>^',
            '<span style="color:#0F04E8;">$1</span>^',
            '<span style="color:#04E8E7;">$1</span>^',
            '<span style="color:#F75AF6;">$1</span>^',
            '<span style="color:#FFFFFF;">$1</span>^',
            '<span style="color:#7E7E7E;">$1</span>^',
            '<span style="color:#6E3C3C;">$1</span>^',
        );


        $string = preg_replace($find, $replace, $string);
        return substr($string, 0, strlen($string) - 1);
    }

    public function __get($property)
    {
        $excludes = ['handle', 'meta'];

        if (!in_array($property, $excludes)) {

            if (property_exists($this, $property)) {
                return $this->$property;
            }
        }
    }
}
