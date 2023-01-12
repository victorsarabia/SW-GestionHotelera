/**
 * Asegura la ejecución cuando el documento haya sido cargado completamente en el navegador
 */
$( document ).ready(function() {

    console.log( "ready!" );

	/**
	 * Evento click del botón
	 * Realiza petición de médicos al servidor y recibe JSON estructurado
	 */
	$( "#enviar" ).click(function() {
	    console.log("Inicio Ajax");
	    /*
	    // Petición AJAX con JQuery
	    var valor = $("#valor").val();
		var request = $.ajax({
			url: "sw_medico.php",
			method: "POST",
			data: {
				filters :  {nombre: valor}, //{nombre: valor, sip: "11"}
				action: "get"
			},
			dataType: "json"
		});
		request.done(function (response) {
			console.log("Respuesta Ajax")
			$("#title").html(response.data);
			if (response.success) {
				$("table tbody").empty();
				for(i=0; i<response.data.length; i++) {
					$("table tbody").append("<tr><td>" + response.data[i].sip + "</td><td>" + response.data[i].nombre + "</td></tr>");
				}
				response[i]["sip"]

			}
		});
		request.fail(function (jqXHR, textStatus) {
			alert("Request failed: " + textStatus);
		});
	     */


		// Petición AJAX con Javascrip sin Framework
		var valor = document.getElementById("valor").value;
		var data = {
			filters :  {nombre: valor}, //{nombre: valor, sip: "11"}
			action: "get"
		};
		fetch("sw_medico.php", {
			headers: {
				"Content-Type": "application/json; charset=utf-8",
				//'Content-Type': 'application/x-www-form-urlencoded',
				'Accept': 'application/json'
			},
			method: 'POST',
			body: JSON.stringify(data)
		})
			.then(res => res.json()) // parse response as JSON (can be res.text() for plain response)
			.then(response => {
				// here you do what you want with response
				if (response.success) {
					$("table tbody").empty();
					for(i=0; i<response.data.length; i++) {
						$("table tbody").append("<tr><td>" + response.data[i].sip + "</td><td>" + response.data[i].nombre + "</td></tr>");
					}
				}
			})
			.catch(err => {
				console.log(err)
				alert("sorry, there are no results for your search")
			});

		console.log("Fin Ajax")
	});


	/**
	 * Hace una petición al servidor para inicializar la tabla en la primera carga
	 */
	$.ajax({
		method: "POST",
		url: "sw_medico.php",
		data: {
		    action: "get"
		},
		dataType: "json"
	})
	// en caso de éxito
	.done(function( response ) {
		console.log(response);

		if (response.success) {
			for(i=0; i<response.data.length; i++) {
				$("table tbody").append("<tr><td>" + response.data[i].sip + "</td><td>" + response.data[i].nombre + "</td></tr>");
			}
		}
	})
	// En caso de fallo
	.fail(function( jqXHR, textStatus, errorThrown ) {
		alert( "Error: " + textStatus );
	});


});
