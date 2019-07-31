
function showRequestsAndUsecases(elem) {

    let coverageUrl = '/coverage/file/' + elem.dataset.file + '/line/' + elem.dataset.line;

    let xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function() {
        if( xmlhttp.readyState === 4 && xmlhttp.status === 200 ) {
            let payload = JSON.parse(xmlhttp.responseText);

            console.log(payload);

            let elemCU = document.getElementById("coverageRequestsAndUsecases");

            let out = '<span style="font-weight: bold;">usecases for line ' + elem.dataset.line + ':</span>';
            if (payload.usecases.length === 0) {
                out += "<br>none<br>";
            } else {
                out += "<ul>";
                for (let id in payload.usecases) {
                    out += "<li>"
                        + id + ' ' + payload.usecases[id]
                        + "</li>";
                }
                out += "</ul>";
            }

            out += '<span style="font-weight: bold;">requests for line ' + elem.dataset.line + ':</span>';
            if (payload.requests.length === 0) {
                out += "<br>none";
            } else {
                out += "<ul>";
                for (let id in payload.requests) {
                    out += "<li>"
                        + id + ' ' + payload.requests[id]
                        + "</li>";
                }
                out += "</ul>";
            }
            elemCU.innerHTML = out;

            elemCU.style.top = (elem.getBoundingClientRect().top - 50) + "px";
        }
    };

    xmlhttp.open( "GET", coverageUrl, true );
    xmlhttp.send();
}
