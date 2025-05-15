jQuery(function () {
    $.ajax({
        url: "../Controllers/Servlet.php",
        type: "GET",
        data: {
          action: "session",
        },
        dataType: "json",
        success: function (response) {
            if (response.exits) {
                loadData();
            } else {
                window.location.href = '/403';
            }
        },
        error: function (error) {
            Swal.fire('Error', 'Hubo un problema al cargar los datos', 'error');
        },
    });
});


function loadData () {
    $.ajax({
        url: "../Controllers/Servlet.php",
        type: "GET",
        data: {
          action: "data",
        },
        dataType: "json",
        success: function (response) {
            if (!response.error) {
                let cardsContainer = $("#cardsData");
                cardsContainer.empty();
                cardsContainer.html(response.html);
                loadScript("../Controllers/cardLogic-min.js");
                //showData();
            } else {
                console.log(response.msg)
            }
        },
        error: function (error) {
            Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
        },
    });
}

function loadScript(url) {
    let script = document.createElement("script");
    script.src = url;
    script.type = "text/javascript";
    script.async = false;
    document.body.appendChild(script);
}


function showData () {
    $.ajax ({
        url: "../Controllers/Servlet.php",
        type: "GET",
        data: {
          action: "show",
        },
        dataType: "json", // Expecting JSON response
        success: function (response) {
            console.log("Show data de la clase");
            console.log(response.msg);
        },
        error: function (error) {
            Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
        },
    });
}