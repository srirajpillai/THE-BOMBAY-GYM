const canvas = document.getElementById("gymLogo");
const ctx = canvas.getContext("2d");

// Clear canvas
ctx.clearRect(0, 0, canvas.width, canvas.height);

// Draw Circle (Background)
ctx.fillStyle = "#ffcc00";  // Yellow background
ctx.beginPath();
ctx.arc(150, 150, 140, 0, Math.PI * 2);
ctx.fill();

// Draw Text (Gym)
ctx.fillStyle = "black";
ctx.font = "bold 40px Arial";
ctx.fillText("GYM", 100, 160);

// Draw Dumbbell Shape
ctx.fillStyle = "black";

// Left Handle
ctx.fillRect(50, 130, 20, 40);
ctx.fillRect(70, 140, 20, 20);

// Right Handle
ctx.fillRect(230, 130, 20, 40);
ctx.fillRect(210, 140, 20, 20);

// Middle Bar
ctx.fillRect(90, 145, 120, 10);
