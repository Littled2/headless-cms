

class Moving_Points {
    constructor(canvas, points_count) {

        this.points_count = points_count
        this.canvas = canvas
        this.points = []
        this.interval = null

        this.ctx = this.canvas.getContext("2d")
        this.ctx.lineWidth = 0.5

        window.addEventListener("resize", this.initialise)

        this.initialise()
    }

    initialise() {

        //set canvas width and height properly
        this.canvas.height = +getComputedStyle(this.canvas).getPropertyValue("height").slice(0, -2) * window.devicePixelRatio
        this.canvas.width = +getComputedStyle(this.canvas).getPropertyValue("width").slice(0, -2) * window.devicePixelRatio

        if(this.interval) clearInterval(this.interval)

        this.points = []

        for (let i = 0; i < this.points_count; i++) {
            this.points.push(new Point(this.canvas))
        }


        this.MIN_CONNECT_DISTANCE = this.canvas.width / 4

        this.interval = setInterval(() => this.refresh(), 35)

        this.refresh()

    }

    refresh() {

        // Clear the canvas ready for the next frame
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Draw the new frame
        this.draw_points()
    }

    draw_points() {
        for (let i = 0; i < this.points.length; i++) {

            let points_within_reach = []

            for (let j = 0; j < this.points.length; j++) {

                const distance = this.dist_between_points(this.points[i], this.points[j])

                if (distance < this.MIN_CONNECT_DISTANCE){

                    points_within_reach.push([
                        this.points[j].x,
                        this.points[j].y,
                        (distance / this.MIN_CONNECT_DISTANCE).toFixed(2)
                    ])

                }
            }

            if(points_within_reach.length > 2) {
                
                let start_x = this.points[i].x
                let start_y = this.points[i].y

                for (let j = 0; j < points_within_reach.length; j++) {

                    // Draw the line between the two points
                    // Colour the line depending upon the distance between the points

                    this.ctx.beginPath()
                    this.ctx.moveTo(start_x, start_y)
                    this.ctx.lineTo(points_within_reach[j][0], points_within_reach[j][1])
                    this.ctx.strokeStyle = `rgba(80, 80, 80, ${1 - points_within_reach[j][2]})`
                    this.ctx.stroke()

                }
            }
        }

        // Update the positions for all of the points
        for (let i = 0; i < this.points.length; i++) {
            this.points[i].update_position()
        }
    }

    /**
     * Returns the distance between two given points
     */
    dist_between_points(p1, p2) {
        return Math.round(
            Math.sqrt(
                    Math.pow((p1.x - p2.x), 2) + Math.pow((p1.y - p2.y), 2)
                )
            )
    }
}


class Point{
    constructor(canvas){

        this.canvas = canvas

        // this.x = this.getRandomNumber(0, this.canvas.width)
        // this.y = this.getRandomNumber(0, this.canvas.height)
        
        // Start all points in the center
        this.x = this.canvas.width / 2
        this.y = this.canvas.height / 2

        this.speed = this.getRandomNumber(1, 10)

        let angle = this.getRandomNumber(0, 360)
        
        this.direction = {
            x: +(Math.cos(angle)).toFixed(3),
            y: +(Math.sin(angle)).toFixed(3)
        }
    }

    getRandomNumber(min, max){
        return Math.floor(Math.random() * (max - min + 1) + min)
    }

    update_position(){

        // Move the point by the correct amount on the x axis and y axis
        this.x += this.direction.x * this.speed
        this.y += this.direction.y * this.speed

        // Handle if the point has exited the canvas area
        if(this.x > this.canvas.width || this.x < 0){
            this.direction.x = -1 * this.direction.x
        }else if(this.y > this.canvas.height || this.y < 0){
            this.direction.y = -1 * this.direction.y
        }
    }
}




const points_control = new Moving_Points(document.querySelector("#hero-canvas"), 25)

