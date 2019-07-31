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
                    out += "<li><a href='/coverage?usecaseId=" + id + "'>"
                        + id + ' ' + payload.usecases[id]
                        + "</a></li>";
                }
                out += "</ul>";
            }

            out += '<span style="font-weight: bold;">requests for line ' + elem.dataset.line + ':</span>';
            if (payload.requests.length === 0) {
                out += "<br>none";
            } else {
                out += "<ul>";
                for (let id in payload.requests) {
                    out += "<li><a href='/coverage?requestId=" + id + "'>"
                        + id + ' ' + payload.requests[id]
                        + "</a></li>";
                }
                out += "</ul>";
            }
            elemCU.innerHTML = out;

            elemCU.style.top = (elem.getBoundingClientRect().top - 30) + "px";
        }
    };

    xmlhttp.open("GET", coverageUrl, true);
    xmlhttp.send();
}
