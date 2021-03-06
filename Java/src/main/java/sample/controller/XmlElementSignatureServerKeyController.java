package sample.controller;


import com.lacunasoftware.pkiexpress.XmlSignaturePolicies;
import com.lacunasoftware.pkiexpress.XmlSigner;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import sample.Application;
import sample.util.Util;

import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.UUID;


@Controller
public class XmlElementSignatureServerKeyController {

    /**
     * This action perform a local XML signature of an element of the XML in one step using PKI Express and renders a
     * link to the signed file.
     */
    @RequestMapping(value = "/xml-element-signature-server-key", method = {RequestMethod.GET})
    public String get(
            Model model,
            HttpServletResponse response
    ) throws IOException {

        try {

            // Get an instance of the XmlSigner class, responsible for receiving the signature elements and performing
            // the local signature.
            XmlSigner signer = new XmlSigner(Util.getPkiExpressConfig());

            // Set PKI default options (see Util.java)
            Util.setPkiDefaults(signer);

            // Set the XML to be signed, a sample Brazilian fiscal invoice pre-generated.
            signer.setXmlToSign(Util.getSampleNFePath());

            // Set the "Pierre de Fermat" certificate's thumbprint (SHA-1).
            signer.setCertificateThumbprint("f6c24db85cb0187c73014cc3834e5a96b8c458bc");

            // Set the signature policy.
            signer.setSignaturePolicy(XmlSignaturePolicies.NFe);

            // Set the ID of the element to be signed.
            signer.setToSignElementId("NFe35141214314050000662550010001084271182362300");

            // Generate path for output file and add to singer object.
            String filename = UUID.randomUUID() + ".xml";
            signer.setOutputFile(Application.getTempFolderPath().resolve(filename));

            // Perform the signature.
            signer.sign();

            // If you want to delete the temporary files created by this step, use the method dispose(). This method
            // MUST be called after the sign() method, because it deletes some files needed by the method.
            signer.dispose();

            // Render the link to download the signed file on signature page.
            model.addAttribute("outputFile", filename);

        } catch(Exception ex) {

            // Get exception message to be rendered on signature page.
            model.addAttribute("errorMessage", ex.getMessage());
        }

        return "xml-element-signature-server-key";
    }

}
