<?php

/*
* This file perform a local PAdES signature in one step using PKI Express.
*/

require __DIR__ . '/vendor/autoload.php';

use Lacuna\PkiExpress\PadesSigner;


// Retrieve the URL argument "userfile", this is filled after been redirected here by the file upload.php. We'll set the
// path of the file to be signed, which was saved in the "app-data" folder by upload.php.
$userfile = isset($_GET['userfile']) ? $_GET['userfile'] : null;

try {

    // Verify if the provided userfile exists.
    if (!file_exists("app-data/$userfile")) {
        throw new \Exception('File not found!');
    }

    // Get an instance of the PadesSigner class, responsible for receiving the signature elements and performing the
    // local signature.
    $signer = new PadesSigner(getPkiExpressConfig());

    // Set PDF to be signed.
    $signer->setPdfToSign("app-data/$userfile");

    // Set the "Pierre de Fermat" certificate's thumbprint (SHA-1).
    $signer->setCertificateThumbprint('f6c24db85cb0187c73014cc3834e5a96b8c458bc');

    // Set a file reference for the stamp file. Note that this file can be referenced later by "fref://stamp" at the
    // "url" field on the visual representation (see content/vr.json file or getVisualRepresentation($case) method).
    $signer->addFileReference('stamp', 'content/stamp.png');

    // Set visual representation. We provide a PHP class that represents the visual representation model.
    $signer->setVisualRepresentation(getVisualRepresentation(1));
    // Alternatively, we can provide a javascript file that contains a json-encoded model (see content/vr.json).
    //$signer->setVisualRepresentationFromFile("content/vr.json");

    // Generate path for output file and add to signer object.
    createAppData(); // make sure the "app-data" folder exists (util.php)
    $outputFile = uniqid() . ".pdf";
    $signer->setOutputFile("app-data/{$outputFile}");

    // Perform the signature.
    $signer->sign();

} catch(Exception $e) {

    // Get exception message to be rendered on signature page
    $errorMessage = $e->getMessage();
}

?><!DOCTYPE html>
<html>
<head>
    <title>PAdES Signature</title>
    <?php include 'includes.php' // jQuery and other libs (used only to provide a better user experience, but NOT required to use the Web PKI component) ?>
</head>
<body>

<?php include 'menu.php' // The top menu, this can be removed entirely ?>


<div class="container">

    <?php if (!isset($errorMessage)) { ?>

        <?php // If no errors have occurred, this page is shown for the user, with the link to the signed file. ?>
        <h2>PAdES Signature with a server key</h2>

        <p>File signed successfully!</p>
        <p>
            <a href="app-data/<?= $outputFile ?>" class="btn btn-default">Download the signed file</a>
        </p>


    <?php } else { ?>

        <?php
        // If some error occurred, the error message is show with a "Try Again" button to return to the upload.php
        // page.
        ?>
        <div class="alert alert-danger" role="alert" style="margin-top: 2%;">
            <label for="errorMsg">Signature Failed</label><br/>
            <span id="errorMsg"><?= $errorMessage ?></span>
        </div>
        <a class="btn btn-default" href="upload.php?goto=pades-signature-server-key">Try Again</a>

    <?php } ?>

</div>

</body>
</html>