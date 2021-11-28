<?php

namespace Laurentvw\Scrapher;

class Page
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getHTML()
    {
        $ch = curl_init();

        $options = array(
            CURLOPT_URL            => $this->url, // the URL
            CURLOPT_RETURNTRANSFER => true, // Dont output any response directly to the browser
            CURLOPT_HEADER         => false, // Dont return the header
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11', // Set a valid user agent
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        );

        curl_setopt_array($ch, $options);

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    public function getURL()
    {
        return $this->url;
    }
}
