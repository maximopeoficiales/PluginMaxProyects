<?php

function mfShowWsdl()
{
     header("Content-Type: application/xml");
     require __DIR__ . "/../../../../webservices/example.wsdl";
}


/* http://maxco.punkuhr.test/wp-json/webservice/wsdl */
/* http://maxco.punkuhr.test/webservices/example.wsdl */
add_action("rest_api_init", function () {
     register_rest_route("/webservice", "/wsdl", array(
          "methods" => "GET",
          "callback" => "mfShowWsdl",
          'args'            => array(),
     ));
});
