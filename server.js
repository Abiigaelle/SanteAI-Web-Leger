const { exec } = require('child_process');

console.log('\x1b[36m%s\x1b[0m', '==================================================');
console.log('\x1b[36m%s\x1b[0m', '      SantéAI — Console de contrôle locale       ');
console.log('\x1b[36m%s\x1b[0m', '==================================================');
console.log('Démarrage de Apache et MySQL (XAMPP)...');

// Démarrer XAMPP
exec('C:\\xampp\\xampp_start.exe', (err) => {
    if (err) {
        console.error('Erreur lors du démarrage de XAMPP :', err);
        process.exit(1);
    }
    
    console.log('\x1b[32m%s\x1b[0m', '[OK] XAMPP a démarré avec succès.');
    console.log('Ouverture du site dans votre navigateur...');
    
    // Ouvrir le navigateur sous Windows
    exec('start http://localhost/santeai/');
    
    console.log('\x1b[35m%s\x1b[0m', '\n-> Le serveur tourne actuellement sur http://localhost/santeai/');
    console.log('\x1b[33m%s\x1b[0m', '-> Appuyez sur CTRL+C pour arrêter proprement XAMPP et quitter.\n');
});

// Intercepter le CTRL+C
process.on('SIGINT', () => {
    console.log('\nArrêt en cours des serveurs XAMPP (Apache & MySQL)...');
    exec('C:\\xampp\\xampp_stop.exe', (err) => {
        if (err) {
            console.error('Erreur lors de l\'arrêt de XAMPP :', err);
        } else {
            console.log('\x1b[31m%s\x1b[0m', '[OK] Serveurs XAMPP arrêtés proprement.');
        }
        process.exit(0);
    });
});

// Garder le processus Node.js actif en arrière-plan
setInterval(() => {}, 1000);
