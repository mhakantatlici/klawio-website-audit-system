<?php
// DEBUG
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// CONFIG
$API_KEY = "GOOGLE_API_INSIGHT_KEY";

$SMTP_TO_YOU   = "TO_YOU_EMAIL"; 
$SMTP_FROM     = "FROM_EMAIL";
$SITE_NAME     = "WEBSITE_NAME";

function respond($arr){
    echo json_encode($arr);
    exit;
}

// INPUT
$url   = $_POST["url"]   ?? "";
$email = $_POST["email"] ?? "";

if(!$url || !$email){
    respond(["error" => "URL and email are required"]);
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    respond(["error" => "Invalid email address"]);
}

if(!preg_match("~^https?://~i", $url)){
    $url = "https://" . $url;
}



// ----------------------------------------------------
// FETCH HTML BASICS
// ----------------------------------------------------

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false
]);
$html = curl_exec($ch);
curl_close($ch);

$title = $desc = $h1 = null;

if($html){
    if(preg_match("~<title>(.*?)</title>~is", $html, $m))
        $title = trim($m[1]);

    if(preg_match('~<meta[^>]+name=[\'"]description[\'"][^>]+content=[\'"](.*?)[\'"]~i', $html, $m))
        $desc = trim($m[1]);

    if(preg_match("~<h1[^>]*>(.*?)</h1>~is", $html, $m))
        $h1 = trim(strip_tags($m[1]));
}



// ----------------------------------------------------
// CALL GOOGLE PAGESPEED (PERFORMANCE SCORE ONLY)
// ----------------------------------------------------

$apiUrl = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?" . http_build_query([
    "url"      => $url,
    "strategy" => "mobile",
    "category" => ["performance"],
    "key"      => $API_KEY
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20
]);
$json = curl_exec($ch);
curl_close($ch);

$data = json_decode($json, true);

$perf = 0;

if(isset($data["lighthouseResult"]["categories"]["performance"]["score"])){
    $perf = round($data["lighthouseResult"]["categories"]["performance"]["score"] * 100);
}



// ----------------------------------------------------
// KLW SEO SCORING SYSTEM (100 POINT SEO ENGINE)
// ----------------------------------------------------

$seo_score = 0;

// 1) Title (10)
if($title) $seo_score += 10;

// 2) Meta Description (10)
if($desc) $seo_score += 10;

// 3) H1 (10)
if($h1) $seo_score += 10;

// 4) Canonical tag (10)
$canonical = null;
if (preg_match('~<link[^>]+rel=[\'"]canonical[\'"][^>]+href=[\'"](.*?)[\'"]~i', $html, $m)) {
    $canonical = $m[1];
}
if($canonical) $seo_score += 10;

// 5) Open Graph tags (10)
$og_title = preg_match('~property=[\'"]og:title[\'"]~i', $html);
$og_desc  = preg_match('~property=[\'"]og:description[\'"]~i', $html);
$og_img   = preg_match('~property=[\'"]og:image[\'"]~i', $html);
$og_count = $og_title + $og_desc + $og_img;

if($og_count == 3) $seo_score += 10;
elseif($og_count >= 1) $seo_score += 5;

// 6) Image ALT attributes (10)
preg_match_all('~<img[^>]*>~i', $html, $imgs);
$total_imgs = count($imgs[0]);
$imgs_with_alt = preg_match_all('~<img[^>]+alt=[\'"](.*?)[\'"]~i', $html);

if($total_imgs > 0 && ($imgs_with_alt / $total_imgs) >= 0.5){
    $seo_score += 10;
}

// 7) Sitemap check (10)
$sitemap_url = rtrim($url, "/") . "/sitemap.xml";
$headers = @get_headers($sitemap_url);

if($headers && strpos($headers[0], "200") !== false){
    $seo_score += 10;
}

// 8) Robots.txt check (10)
$robots_url = rtrim($url, "/") . "/robots.txt";
$headers = @get_headers($robots_url);

if($headers && strpos($headers[0], "200") !== false){
    $seo_score += 10;
}

// 9) Noindex check (10)
if(!preg_match('~<meta[^>]+name=[\'"]robots[\'"][^>]+content=[\'"]noindex[\'"]~i', $html)){
    $seo_score += 10;
}

// 10) H1 keyword inside content (10)
if($h1 && stripos($html, $h1) !== false){
    $seo_score += 10;
}



// ----------------------------------------------------
// OVERALL SCORE (Performance + SEO → 50/50 Weight)
// ----------------------------------------------------

$overall = round(($perf * 0.5) + ($seo_score * 0.5));



// ----------------------------------------------------
// FINDINGS SYSTEM
// ----------------------------------------------------

$findings = [];

if(!$desc)   $findings[] = "Missing meta description.";
if(!$title)  $findings[] = "Missing <title> tag.";
if(!$h1)     $findings[] = "Missing <h1> heading.";
if(!$canonical) $findings[] = "Missing canonical tag.";

if($og_count == 0) $findings[] = "Open Graph tags missing.";
if($imgs_with_alt < ($total_imgs / 2)) $findings[] = "More than half of images missing alt attributes.";

if($perf < 60) $findings[] = "Slow performance — optimization needed.";
if($seo_score < 60) $findings[] = "SEO score below recommended level.";

if(empty($findings)){
    $findings[] = "Website looks healthy — only minor improvements recommended.";
}



// ----------------------------------------------------
// EMAIL SENDER (Native PHP mail())
// ----------------------------------------------------

$subject = "Your Website Audit Report – Klawio";

$body = "
Hello,<br><br>
Your website audit results are ready.<br><br>

<b>Overall Score:</b> {$overall}/100<br>
<b>Performance:</b> {$perf}/100<br>
<b>SEO:</b> {$seo_score}/100<br><br>

<b>Key Findings:</b><br>
<ul>";

foreach($findings as $f){
    $body .= "<li>{$f}</li>";
}

$body .= "</ul><br>

To improve your website instantly, book a free call:<br>
<a href='https://klawio.com/contact'>https://klawio.com/contact</a><br><br>

Regards,<br>
<b>Klawio Web Agency</b>
";

$headers  = "From: {$SITE_NAME} <{$SMTP_FROM}>\r\n";
$headers .= "Reply-To: {$SMTP_FROM}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Send to customer
mail($email, $subject, $body, $headers);

// Send copy to you
mail($SMTP_TO_YOU, "COPY: ".$subject, $body, $headers);



// ----------------------------------------------------
// FINAL JSON OUTPUT
// ----------------------------------------------------

respond([
    "status"             => "ok",
    "overall_score"      => $overall,
    "performance_score"  => $perf,
    "seo_score"          => $seo_score,
    "mobile_score"       => $perf,   // ⭐ Force mobile = performance
    "findings"           => $findings,
    "email_sent"         => true
]);
