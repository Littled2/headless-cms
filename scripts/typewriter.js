window.addEventListener("load", () => {
    // Get the typewriter elements
    const els = document.querySelectorAll(".typewriter")

    els.forEach(el => typewriter(el))
})


function typewriter(textElement) {

    console.log(textElement)

    let text = textElement.innerText
    textElement.innerText = ''

    let space = false

    for (let i = 0; i < text.length; i++) {
        setTimeout(() => {

            if(!space) {
                textElement.innerText += text[i]
            } else {
                textElement.innerText += ' ' + text[i]
            }

            if(text[i] === ' ') {
                space = true
            } else {
                space = false
            }

        }, (i * 100))
    }
}