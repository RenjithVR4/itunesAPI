<?php 


function getItunes_data($albumname, $artist)
{

    if(!$albumname)
    {
        return array("error" => "missing parameter - Album Name");
        exit();
    }

    if(!$artist)
    {
        return array("error" => "missing parameter - Artist Name");
        exit();
    }

    // Search API with Entity & Attribute
	$query ="https://itunes.apple.com/search?entity=album&attribute=artistTerm&term=".$albumname . "&term=".$artist;
    // Generic search API
    // $query ="https://itunes.apple.com/search?term=".$albumname . "&term=".$artist;

    $data = getdata_curl($query);
    $itunes = array();
    // $itracks = array();

    foreach ($data['results'] as $value)
    {
        foreach ($value as $key => $val)
        {
            if($key == 'artworkUrl100')
            {
                $itunesData['albumimageURL'] = $val;
            }

            if($key == 'artistName')
            {
                $itunesData['artistName'] = $val;
            }

            if($key == 'collectionName')
            {
                $itunesData['albumName'] = $val;
            }

            if($key == 'releaseDate')
            {
                $timestamp = strtotime($val);
                $year = date('Y', $timestamp);
                $itunesData['year'] = $year;
            }

            if($key == 'collectionViewUrl')
            {
                $itunesData['trackList'] = $val;
                $trackurl[] = $itunesData['trackList'];
                $gettrackData = getdata_multi($trackurl);

                $itunesData['trackList'] = $gettrackData;             
            }

            if($key == 'collectionViewUrl')
            {
                $itunesData['itunesLink'] = $val;
            }
        }

        $itunes[] = $itunesData;
        
    }

    return $itunes;
}


function getdata_multi($urlset)
{
    $entries = array();
    $tracks = array();

    foreach($urlset as $url)
    {
        $html = file_get_contents( $url);

        libxml_use_internal_errors( true);
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadHTML( $html);
        $xpath = new DOMXpath( $doc);

        $query = "//div[@class='table__row__headline we-selectable-item__link-text__headline we-truncate we-truncate--single-line ember-view']";

        $entries = $xpath->query($query);
    }
   
    foreach ($entries as $value)
    {
      $tracks[] = preg_replace(['(\s+)u', '(^\s|\s$)u'], [' ', ''],trim(simplexml_import_dom($value)->asXML()));
    }

    return $tracks;

    libxml_use_internal_errors(false);
}


function getdata_curl($url)
{
    $headers[]  = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; 
        rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13";
    $headers[]  = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,
        */*;q=0.8";
    $headers[]  = "Accept-Language:en-us,en;q=0.5";
    $headers[]  = "Accept-Encoding:gzip,deflate";
    $headers[]  = "Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $headers[]  = "Keep-Alive:115";
    $headers[]  = "Connection:keep-alive";
    $headers[]  = "Cache-Control:max-age=0";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_ENCODING, "gzip");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_COOKIESESSION, true );
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt' );
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt' );
    //curl_setopt($curl, CURLOPT_MAXREDIRS, 1);

    $data = curl_exec($curl);
    $data = json_decode($data, true);

    curl_close($curl);

    return $data;
}

$getdata = getItunes_data('Thriller', 'Micheal+Jackson');

echo '<pre>';
print_r($getdata);
echo '</pre>';

 ?>