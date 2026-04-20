<?php
    session_start();
    require_once("../classes/class.user.php");
    $user = new USER();

    if ( $user->is_loggedin() ) {
        if ( $user->checkTimeout() ) {
            if ( isset($_GET['redirect']) && !empty($_GET['redirect']) ) {
                date_default_timezone_set('Asia/Colombo');
                $baseDomain='http://'.$_SERVER['HTTP_HOST']; 
                $webPages = array(
                    array("", "2022-12-10", "1.0"),
                    array("blogs", "2022-12-10", "1.0"),
                    array("testimonials", "2022-12-10", "0.9"),
                    array("about", "2022-12-10", "0.8"),
                    array("login", "2022-12-10", "0.8")
                );
                function createXmlUrlElement($xmlObject, $arr, $baseDomain) {
                    foreach ( $arr as $key=>$value ) {
                        $url = $xmlObject->addChild("url"); 
                        $url->addChild("loc","$baseDomain/".$value[0]); 
                        $url->addChild("lastmod",date("c", strtotime($value[1]))); 
                        $url->addChild("priority", $value[2]); 
                    }
                    return true;
                }
                $xmlHeader = "<?xml version='1.0' encoding='UTF-8' ?>\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" />';
                $xml = new SimpleXMLElement($xmlHeader);

                foreach ( $user->fetchAll(array("id","timestamp"), array("tour_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("tour?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                foreach ( $user->fetchAll(array("id","timestamp"), array("blog_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("blog?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                foreach ( $user->fetchAll(array("id","timestamp"), array("ad1_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("ad1?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                foreach ( $user->fetchAll(array("id","timestamp"), array("ad2_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("ad2?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                foreach ( $user->fetchAll(array("id","timestamp"), array("pdf_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("pdf?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                foreach ( $user->fetchAll(array("id","timestamp"), array("homework_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("homework?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                foreach ( $user->fetchAll(array("id","timestamp"), array("books_details"), array("status"=>1)) as $row ) {
                    array_push($webPages, array("books?id=".$row['id'], $row['timestamp'], "1.0"));
                }
                createXmlUrlElement($xml, $webPages, $baseDomain);

                header('Content-type: text/xml'); 
                $xml->asXML("../sitemap.xml");

                $user->redirect("./".$_GET['redirect']);
            }
        } else {
            $user->doLogout();
        }
    } else {
        $user->doLogout();
    }
?>