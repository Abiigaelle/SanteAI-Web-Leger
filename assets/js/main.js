// ============================================================
// SantéAI — Script JavaScript principal
//
// Ce fichier est chargé dans le footer.php (après le HTML),
// ce qui garantit que les éléments du DOM existent quand
// JavaScript essaie de les manipuler.
//
// Contenu :
//  1. Initialisation du graphique des symptômes (Chart.js)
//  2. Initialisation du graphique TSH (Chart.js)
//  3. Utilitaires divers
// ============================================================

// Attendre que le DOM soit entièrement chargé avant d'exécuter le code
document.addEventListener('DOMContentLoaded', function () {

    // === GRAPHIQUE 1 : Évolution Fatigue & Humeur ===
    // Ce graphique n'est présent que sur le dashboard
    const canvasSymptomes = document.getElementById('chartSymptomes');
    if (canvasSymptomes) {

        // donneesSymptomes est définie dans views/dashboard/index.php
        // via json_encode() PHP — c'est la méthode pour passer des données PHP à JS
        const labels    = donneesSymptomes.map(d => formatDate(d.date_saisie));
        const fatigue   = donneesSymptomes.map(d => d.niveau_fatigue);
        const humeur    = donneesSymptomes.map(d => d.niveau_humeur);

        new Chart(canvasSymptomes, {
            type: 'line', // Graphique en courbes
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Fatigue',
                        data: fatigue,
                        borderColor: '#E53E3E',       // Rouge pour la fatigue
                        backgroundColor: 'rgba(229, 62, 62, 0.1)',
                        tension: 0.4,                 // Courbe lissée
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Humeur',
                        data: humeur,
                        borderColor: '#48BB78',       // Vert pour l'humeur
                        backgroundColor: 'rgba(72, 187, 120, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        // Personnalisation des infobulles au survol
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ' : ' + context.raw + '/5';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 1,
                        max: 5,
                        ticks: { stepSize: 1 },
                        title: { display: true, text: 'Niveau (1–5)' }
                    }
                }
            }
        });
    }

    // === GRAPHIQUE 2 : Évolution TSH ===
    const canvasTSH = document.getElementById('chartTSH');
    if (canvasTSH) {

        // donneesBilans est définie dans views/dashboard/index.php
        const labelsB = donneesBilans.map(d => formatDate(d.date_bilan));
        const tsh     = donneesBilans.map(d => d.tsh);

        new Chart(canvasTSH, {
            type: 'line',
            data: {
                labels: labelsB,
                datasets: [
                    {
                        label: 'TSH (mUI/L)',
                        data: tsh,
                        borderColor: '#5B9BD5',
                        backgroundColor: 'rgba(91, 155, 213, 0.15)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: tsh.map(v => {
                            // Coloration des points selon la norme
                            if (v === null) return '#ccc';
                            if (v > 4.0)   return '#E53E3E'; // rouge si hors norme haute
                            if (v < 0.4)   return '#F6AD55'; // orange si hors norme basse
                            return '#48BB78';                 // vert si dans les normes
                        }),
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const v = context.raw;
                                let statut = '';
                                if (v > 4.0) statut = ' ⚠️ Élevée';
                                else if (v < 0.4) statut = ' ⚠️ Basse';
                                else statut = ' ✓ Normale';
                                return 'TSH : ' + v + ' mUI/L' + statut;
                            }
                        }
                    },
                    annotation: {
                        // Zone de normalité TSH (0.4–4.0)
                        // Nécessiterait le plugin chartjs-plugin-annotation
                        // TODO : ajouter le plugin annotation pour visualiser la zone normale
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        title: { display: true, text: 'TSH (mUI/L)' }
                    }
                }
            }
        });
    }

}); // fin DOMContentLoaded

// === UTILITAIRE : Formater une date YYYY-MM-DD en DD/MM ===
function formatDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length < 3) return dateStr;
    return parts[2] + '/' + parts[1]; // Affiche JJ/MM
}
