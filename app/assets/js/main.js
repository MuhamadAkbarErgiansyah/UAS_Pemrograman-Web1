/*=========================================
  Explore Bandung - Enhanced JavaScript
  Modern Animations & Interactions
=========================================*/

// ===== SMOOTH SCROLL REVEAL =====
(function(){
  const items = document.querySelectorAll('[data-animate]');
  const io = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
      if(e.isIntersecting) {
        e.target.classList.add('fade-in');
      }
    });
  }, {threshold: 0.12});
  items.forEach(i=>io.observe(i));
})();

// ===== SCROLL TO TOP BUTTON =====
document.addEventListener('DOMContentLoaded', function() {
  const scrollBtn = document.createElement('div');
  scrollBtn.className = 'scroll-to-top';
  scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
  document.body.appendChild(scrollBtn);
  
  window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
      scrollBtn.classList.add('visible');
    } else {
      scrollBtn.classList.remove('visible');
    }
  });
  
  scrollBtn.addEventListener('click', function() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
});

// ===== NAVBAR SCROLL EFFECT =====
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
  const currentScroll = window.pageYOffset;
  
  if (currentScroll > 100) {
    navbar.style.boxShadow = '0 8px 30px rgba(0,0,0,0.12)';
  } else {
    navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.08)';
  }
  
  lastScroll = currentScroll;
});

// ===== SMOOTH YOUTUBE MODAL HANDLING =====
document.addEventListener('click', function(e){
  const target = e.target.closest('[data-video-src]');
  if(!target) return;
  const src = target.getAttribute('data-video-src');
  const modalId = target.getAttribute('data-bs-target') || '#modalVideo';
  const iframe = document.querySelector(modalId + ' iframe');
  if(iframe) iframe.src = src + '?autoplay=1';
});

document.addEventListener('DOMContentLoaded', function(){
  const modals = document.querySelectorAll('.modal');
  modals.forEach(m=>{
    m.addEventListener('hidden.bs.modal', function(){
      const iframe = m.querySelector('iframe');
      if(iframe) iframe.src = '';
    });
  });
});

// ===== CARD HOVER EFFECTS =====
document.querySelectorAll('.card').forEach(card => {
  card.addEventListener('mouseenter', function() {
    this.style.transition = '0.3s ease';
  });
});

// ===== ALERT AUTO DISMISS =====
document.querySelectorAll('.alert').forEach(alert => {
  if (!alert.classList.contains('alert-permanent')) {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-20px)';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  }
});

// ===== SMOOTH PAGE TRANSITIONS =====
document.addEventListener('DOMContentLoaded', function() {
  document.body.style.opacity = '0';
  setTimeout(() => {
    document.body.style.transition = 'opacity 0.5s ease';
    document.body.style.opacity = '1';
  }, 100);
});

console.log('✨ Explore Bandung - Enhanced JavaScript Loaded!');
