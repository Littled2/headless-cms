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



window.addEventListener("load", () => {

    if(window.location.pathname !== "/") return

    document.querySelector(".hero").style.height = `calc(100vh - ${window.getComputedStyle(document.querySelector("header")).height})`

    setTimeout(() => {
        document.querySelector("#scroll-down-icon").style.opacity = "1"
    }, 1500)
})