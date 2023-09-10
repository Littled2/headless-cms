window.addEventListener("load", () => {
    document.querySelector(".hero").style.height = `calc(100vh - ${window.getComputedStyle(document.querySelector("header")).height})`
    setTimeout(() => {
        document.querySelector("#scroll-down-icon").style.opacity = "1"
    }, 1500)
})