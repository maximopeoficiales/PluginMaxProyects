try {
  jQuery(function (g) {
    /* bloque de codigo para calcular el envio */
    if ("undefined" == typeof wc_checkout_params) return !1;
    var v = {
      updateTimer: !(g.blockUI.defaults.overlayCSS.cursor = "default"),
      dirtyInput: !1,
      selectedPaymentMethod: !1,
      xhr: !1,
      $order_review: g("#order_review"),
      $checkout_form: g("form.checkout"),

      queue_update_checkout: function (e) {
        if (9 === (e.keyCode || e.which || 0)) return !0;
        (v.dirtyInput = this) /* obtengo el elemento */, //no hace casi nada igual funciona
          /*  v.reset_update_checkout_timer(),  */ (v.updateTimer = setTimeout(
            v.maybe_update_checkout,
            "1000"
          ));
      },
      trigger_update_checkout: function () {
        /*   v.reset_update_checkout_timer(), */
        (v.dirtyInput = !1), g(document.body).trigger("update_checkout");
      },
      maybe_update_checkout: function () {
        var e = !0;
        if (g(v.dirtyInput).length) {
          var t = g(v.dirtyInput)
            .closest("div")
            .find(".address-field.validate-required");
          t.length &&
            t.each(function () {
              "" === g(this).find("input.input-text").val() && (e = !1);
            });
        }
        e && v.trigger_update_checkout();
      },
    };
    /* fin de bloque para colcular el envio */

    /* funcion para calcular el envio */
    function calcularEnvio() {
      let postcode = document.querySelector("#billing_postcode.input-text");
      v.queue_update_checkout(postcode);
    } /* div de direccion donde se pondra el mapa */
/*     let divDireccion = document.querySelector("#billing_address_1_field");
    let mapa =
      '<div style="width: 100%; height: 480px" id="map-canvas"></div>';
    let botones =
      '<div class="btn-group text-center" style="text-align:center; margin-bottom:10px;  margin-top:10px;display: flex;justify-content:center  !important;align-items:center !important; role="group"><button type="button" class="btn btn-secondary mr-2" id="miubicacion"><i class="fas fa-map-marker-alt fa-fw"></i>Mi Ubicacion</button><button type="button" class="btn btn-secondary ml-2" id="buscar"><i class="fas fa-search-location fa-fw"></i>Buscar</button></div>';
    divDireccion.insertAdjacentHTML("beforeend", botones);
    divDireccion.insertAdjacentHTML("beforeend", "<p></p>");
    divDireccion.insertAdjacentHTML("beforeend", mapa);
    divDireccion.insertAdjacentHTML("beforeend", "<p></p>"); */
    /* fin de insercion de mapas */
    const input_direccion = "#billing_address_1.input-text";
    /* vacio y oculto algunos campos */
    document.querySelector(input_direccion).value = "";
    document.querySelector("#ce_distanciakm_field").style.display = "none";
    document.querySelector("#ce_latitud_field").style.display = "none";
    document.querySelector("#ce_longitud_field").style.display = "none";
    document.querySelector("#billing_postcode_field").style.display = "none";
   

    let rendererOptions = {
      draggable: true /* activamos el pin movible */,
    };
    let directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
    let map;
    let markers = [];
    /*  Si no pone las coordenadas en header and scripts se pondran las coordenadas de Lima*/
    let Iniciolat, Iniciolon;
    if (!data_coodenadas.latitud_tienda) {
      Iniciolat = -12.055347200000002;
      Iniciolon = -77.03101439999999;
    } else {
      Iniciolat = data_coodenadas.latitud_tienda;
      Iniciolon = data_coodenadas.longitud_tienda;
    }
    /* instancio coodernadas de la tienda*/
    let ubicacionTienda = new google.maps.LatLng(Iniciolat, Iniciolon);
    /* campos necesarios para guardar datos importantes*/
    const campoKM = document.querySelector("#ce_distanciakm.input-text");
    const campoLatitud = document.querySelector("#ce_latitud.input-text");
    const campoLongitud = document.querySelector("#ce_longitud.input-text");
    const campoCodigoPostal = document.querySelector(
      "#billing_postcode.input-text"
    );
    /* funcion para obtener el mapa */
    function getMap() {
      return map;
    }
    /* funcion inicializado del mapa */
    function initialize() {
      let mapOptions = {
        zoom: 13,
        center: ubicacionTienda,
        disableDefaultUI: true,
        zoomControl: true,
        scaleControl: true,
      };

      map = new google.maps.Map(
        document.getElementById("map-canvas"),
        mapOptions
      );
      directionsDisplay.setMap(map);
      centrarMapa(Iniciolat, Iniciolon, getMap(), input_direccion);
    }

    /* funcion para centrar el mapa  */
    function centrarMapa(lat, lng, map, campo_rellenar = "") {
      clearMarkers();
      map.setCenter(new google.maps.LatLng(lat, lng), 13);
      let coordenadas = new google.maps.LatLng(lat, lng);
      if (campo_rellenar != "") {
        addMarker(coordenadas, campo_rellenar);
      } else {
        addMarker(coordenadas);
      }
    }
    /* funcion para agregar un marcador al mapa */
    function addMarker(location, campo_rellenar = "") {
      let marker = new google.maps.Marker({
        position: location,
        draggable: true,
        animation: google.maps.Animation.DROP,
        map: map,
      });
      markers.push(marker);
      /* el evento dragend es al soltar el marcador */
      google.maps.event.addListener(
        markers[markers.length - 1],
        "dragend",
        function (event) {
          console.log(event.latLng.lat());
          console.log(event.latLng.lng());
          if (campo_rellenar != "") {
            getDireccionPorCoordenadas(
              event.latLng.lat(),
              event.latLng.lng(),
              input_direccion
            );
          }
        }
      );
    }

    // Pone todos los marcadores en un array
    function setMapOnAll(map) {
      for (let i = 0; i < markers.length; i++) {
        markers[i].setMap(map);
      }
    }

    // Eliminar los marcadores
    function clearMarkers() {
      setMapOnAll(null);
    }
    /* Funcion Busca por Direccion */
    function buscarPorDireccion(direccion, campoDireccion) {
      let geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: direccion }, function (results, status) {
        if (status === "OK") {
          let resultados = results[0].geometry.location,
            resultados_lat = resultados.lat(),
            resultados_long = resultados.lng();
          /* guardo las coordenadas */
          campoLatitud.value = resultados_lat;
          campoLongitud.value = resultados_long;
          document.querySelector(campoDireccion).value =
            results[0].formatted_address;
          /* obtener el postal code */
          results[0].address_components.forEach((element) => {
            if (element.types[0] === "postal_code") {
              /* obtengo el postal_code */
              campoCodigoPostal.value = element.short_name;
            }
          });
          /* fin de postal code */
          centrarMapa(
            resultados_lat,
            resultados_long,
            getMap(),
            campoDireccion
          );
          obtenerkmporCoordenadas(
            Iniciolat,
            Iniciolon,
            resultados_lat,
            resultados_long
          );
        }
      });
    }
    function obtenerUbicacion(campoRellenar) {
      if (!!navigator.geolocation) {
        //Pedimos los datos de geolocalizacion al navegador
        navigator.geolocation.getCurrentPosition(
          //Si el navegador entrega los datos de geolocalizacion los imprimimos
          function (position) {
            console.log(position);
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;

            centrarMapa(lat, lng, getMap(), campoRellenar);
            getDireccionPorCoordenadas(lat, lng, campoRellenar);
            obtenerkmporCoordenadas(Iniciolat, Iniciolon, lat, lng);
          },
          function () {
            window.alert("Ubicacion no permitida");
          }
        );
      }
    }
    /* funcion para obtener la distancia en km por coordenadas */
    function obtenerkmporCoordenadas(startlat, startlon, endlat, endlon) {
      let directionsService = new google.maps.DirectionsService();
      let start = `"${startlat},${startlon}"`;
      let end = `"${endlat},${endlon}"`;
      directionsService.route(
        {
          origin: start,
          destination: end,
          travelMode: "DRIVING",
        },
        function (response, status) {
          if (status === "OK") {
            let kms = parseFloat(
              response.routes[0].legs[0].distance.text.replace("km", "")
            );
            campoKM.value = kms;
          } else {
            window.alert(
              "La peticion de la direccion a calculcar a fallado:" + status
            );
          }
        }
      );
      calcularEnvio();
    }
    /* funcion para obtener las direccion por coordenadas */
    function getDireccionPorCoordenadas(lat, lng, campoRellenar) {
      let geocoder = new google.maps.Geocoder();
      /* guardo las coordenadas */
      campoLatitud.value = lat;
      campoLongitud.value = lng;
      geocoder.geocode(
        {
          location: { lat, lng },
        },
        function (results, status) {
          // si la solicitud fue exitosa
          if (status === google.maps.GeocoderStatus.OK) {
            // si encontr�� alg��n resultado.
            if (results[1]) {
              /*  console.log(results[1]); */
              results[1].address_components.forEach((element) => {
                if (element.types[0] === "postal_code") {
                  /* obtengo el postal_code */
                  campoCodigoPostal.value = element.short_name;
                }
              });
              /* obtengo y guardo los km en localstorage */
              obtenerkmporCoordenadas(Iniciolat, Iniciolon, lat, lng);
              /* pongo la direccion en el campo a rellenar */
              document.querySelector(campoRellenar).value =
                results[1].formatted_address;
            }
          }
        }
      );
    }
    /* event listeners de el boton Mi Ubicacion y Buscar */
    document
      .querySelector("#miubicacion")
      .addEventListener("click", function () {
        obtenerUbicacion(input_direccion);
      });
    document.querySelector("#buscar").addEventListener("click", function () {
      let direccion = document.querySelector(input_direccion).value;
      buscarPorDireccion(direccion, input_direccion);
    });

    /* inicializa el mapa al cargar la pagina */
    initialize();
    // google.maps.event.addDomListener(window, "load", initialize);
  });
} catch (error) {
  console.warn("Ocurrio en error actualize el plugin :D");
}
