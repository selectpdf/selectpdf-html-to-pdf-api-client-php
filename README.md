# HTML To PDF API

SelectPdf [HTML To PDF Online](https://selectpdf.com/) REST API is a professional solution that lets you create PDF from web pages and raw HTML code in your applications. The API is easy to use and the integration takes only a few lines of code.

## Features

* Create PDF from any web page or html string.
* Full html5/css3/javascript support.
* Set PDF options such as page size and orientation, margins, security, web page settings.
* Set PDF viewer options and PDF document information.
* Create custom headers and footers for the pdf document.
* Hide web page elements during the conversion.
* Automatically generate bookmarks during the html to pdf conversion.
* Support for partial page conversion.
* Easy integration, no third party libraries needed.
* Works in all programming languages.
* No installation required.

Sign up for for free to get instant API access to SelectPdf [HTML to PDF API](https://selectpdf.com/html-to-pdf-api/).

## PHP Client Library

This code converts a web page and sends the generated PDF as an HTTP response:

```php
<?php
require 'selectpdf.php';

try
{   
    // create a SelectPdf API client instance
    $client = new SelectPdf("Your API key here");

    /*
    // get the number of conversions available in the current month
    $conv = $client->availableConversions();
    echo("Available conversions: $conv");
    exit();
    */

    /*
    // get service usage details
    $usage = $client->usageDetails(true);
    echo("<pre>");
    var_dump($usage);
    echo("</pre>");
    exit();
    */

    // set some conversion parameters
    $client->setPageSize("A4");
    $client->setPageOrientation("Portrait");
    $client->setAllMargins(10);

    // convert a web page and store the generated PDF into a $pdf variable
    $pdf = $client->convertUrl('https://selectpdf.com/');
    //$pdf = $client->convertHtmlString('<b>Hello!</b>');

    // set HTTP response headers
    header("Content-Type: application/pdf");
    header("Cache-Control: max-age=0");
    header("Content-Disposition: attachment; filename=\"Document.pdf\"");

    // send the generated PDF 
    echo $pdf;
}
catch(SelectPdfException $ex)
{
    echo "SelectPdf API Error: " . $ex;
}
?>
```
