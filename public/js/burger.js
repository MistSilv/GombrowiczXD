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


// document.addEventListener("DOMContentLoaded", function () {
//     async function checkLocalAccess() {
//         try {
//             const controller = new AbortController();
//             const timeoutId = setTimeout(() => controller.abort(), 1500); 

//             await fetch("http://192.168.210.219/index_mobile.php?ean=1234567890", {
//                 method: 'GET',
//                 mode: 'no-cors',
//                 signal: controller.signal
//             });

//             clearTimeout(timeoutId);

//             console.log("🟢 Połączenie z lokalnym serwerem (WiFi OK)");

//             await fetch("/api/report-network", {
//                 method: "POST",
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//                 },
//                 body: JSON.stringify({ hasLocalAccess: true })
//             });

//         } catch (error) {
//             console.warn("🔴 Brak dostępu do lokalnego serwera (LTE?)");

//             await fetch("/api/report-network", {
//                 method: "POST",
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//                 },
//                 body: JSON.stringify({ hasLocalAccess: false })
//             });
//         }
//     }

//     checkLocalAccess();
// });