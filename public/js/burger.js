document.addEventListener('DOMContentLoaded', function() {
    const burgerBtn = document.querySelector('.burger-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    burgerBtn?.addEventListener('click', function() {
        this.classList.toggle('burger-active');
        mobileMenu.classList.toggle('mobile-menu-active');
        
        console.log('Burger classes:', this.classList);
        console.log('Menu classes:', mobileMenu.classList);
    });
});