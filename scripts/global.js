// ----------------------------------------------------------
// INFO:
// This JS file is loaded across ALL webpages.
//
// -----------------------------------------------------------


window.document.onscroll = e => {
    const header = document.querySelector("header")
    if(e.target.scrollingElement.scrollTop > 0) {
        header.classList.remove("not-scrolling")
    } else {
        header.classList.add("not-scrolling")
    }
}