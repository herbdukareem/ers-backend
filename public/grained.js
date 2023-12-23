window.addEventListener('DOMContentLoaded', (event) => {
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    // Set canvas dimensions to cover the viewport
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    // Particle class
    class Particle {
      constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.radius = Math.random() * 2 + 1;
        this.color = 'rgba(200, 25, 255, 0.6)';
        this.speedX = Math.random() * 1 - 0.5;
        this.speedY = Math.random() * 1 - 0.5;
      }

      // Method to draw the particle
      draw() {
        ctx.fillStyle = this.color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fill();
      }

      // Method to update particle position
      update() {
        this.x += this.speedX;
        this.y += this.speedY;

        // Wrap particles around canvas edges
        if (this.x > canvas.width + this.radius) {
          this.x = -this.radius;
        } else if (this.x < -this.radius) {
          this.x = canvas.width + this.radius;
        }
        if (this.y > canvas.height + this.radius) {
          this.y = -this.radius;
        } else if (this.y < -this.radius) {
          this.y = canvas.height + this.radius;
        }

        this.draw();
      }
    }

    // Create particles
    const particles = [];
    const numParticles = 100;

    function init() {
      for (let i = 0; i < numParticles; i++) {
        particles.push(new Particle());
      }
    }

    // Animation loop
    function animate() {
    
      requestAnimationFrame(animate);
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      for (const particle of particles) {
        particle.update();
      }
    }

    // Initialize and start animation
    init();
    
    animate();
  });
