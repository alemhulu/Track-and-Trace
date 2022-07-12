function openTab(evt, tabno) {
    var i, x, tablinks;
    x = document.getElementsByClassName("tab");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("change");
    for (i = 0; i < x.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("horizontal-btn-active", "");
    }

    document.getElementById(tabno).style.display = "block";
    evt.currentTarget.className += " horizontal-btn-active";
}

// vertical tab links
function vTab(evt, vtabno) {
    var i, x, tablinks;
    x = document.getElementsByClassName("vtab");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("active");
    for (i = 0; i < x.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" bg-lime-200", "");
    }

    document.getElementById(vtabno).style.display = "block";
    evt.currentTarget.className += " bg-lime-200";
}