<?php

/*
* This file perform a local CAdES signature in one step using PKI Express.
*/

require __DIR__ . '/vendor/autoload.php';

use Lacuna\PkiExpress\CadesSigner;

// Retrieve the URL argument "userfile", this is filled after been redirected here by the file upload.php. We'll set the
// path of the file to be signed, which was saved in the "app-data" folder by upload.php.
$userfile = isset($_GET['userfile']) ? $_GET['userfile'] : null;

try {

    // Verify if the provided userfile exists.
    if (!file_exists("app-data/$userfile")) {
        throw new \Exception('File not found!');
    }

    // Get an instance of the CadesSigner class, responsible for receiving the signature elements and performing the
    // local signature.
    $signer = new CadesSigner(getPkiExpressConfig());

    // Set file to be signed. If the file is a CMS, the PKI Express will recognize that and will co-sign that file. But,
    // if the CMS was a "detached" signature, the original file must be provided with the setDataFile($path) method:
    //$signer->setDataFile($dataFile);
    $signer->setFileToSign("app-data/{$userfile}");

    // Set the "Pierre de Fermat" certificate's thumbprint (SHA-1).
    $signer->setCertificateThumbprint('f6c24db85cb0187c73014cc3834e5a96b8c458bc');

    // Set 'encapsulate content' option (default: true).
    $signer->encapsulateContent = true;

    // Generate path for output file and add to signer object.
    createAppData(); // make sure the "app-data" folder exists (util.php)'
    $outputFile = uniqid() . ".p7s";
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
    <title>CAdES Signature</title>
    <?php include 'includes.php' // jQuery and other libs (used only to provide a better user experience, but NOT required to use the Web PKI component) ?>
</head>
<body>

<?php include 'menu.php' // The top menu, this can be removed entirely ?>


<div class="container">

    <?php if (!isset($errorMessage)) { ?>

        <?php // If no errors have occurred, this page is shown for the user, with the link to the signed file. ?>
        <h2>CAdES Signature with a server key</h2>

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
        <a class="btn btn-default" href="upload.php?goto=cades-signature-server-key">Try Again</a>

    <?php } ?>

</div>

</body>
</html>