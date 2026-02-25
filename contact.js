// Gestion du formulaire de contact
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script contact.js chargé');
    
    const contactForm = document.querySelector('.contact-form');
    const confirmationMessage = document.getElementById('confirmationMessage');

    // Vérifications de sécurité
    if (!contactForm) {
        console.error('ERREUR : Formulaire .contact-form non trouvé dans le DOM');
        return;
    }
    console.log('Formulaire trouvé:', contactForm);

    if (!confirmationMessage) {
        console.error('ERREUR : Element #confirmationMessage non trouvé dans le DOM');
        console.log('Assurez-vous d\'avoir cette ligne dans votre HTML :');
        console.log('<div id="confirmationMessage" style="display:none; margin-top:10px;"></div>');
        return;
    }
    console.log('Element confirmationMessage trouvé:', confirmationMessage);

    // Gestion de la soumission du formulaire
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulaire soumis');

        // Récupération du bouton
        const submitButton = contactForm.querySelector('button[type="submit"]');
        if (!submitButton) {
            console.error('Bouton submit non trouvé');
            return;
        }

        // Désactivation du bouton
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Envoi en cours...';

        // Préparation des données
        const formData = new FormData(contactForm);
        
        console.log('Envoi des données à contact.php...');

        // Envoi via fetch
        fetch('contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Réponse reçue, status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Texte brut reçu:', text);
            
            // Tentative de parsing JSON
            let data;
            try {
                data = JSON.parse(text);
                console.log('JSON parsé avec succès:', data);
            } catch (e) {
                console.error('ERREUR de parsing JSON:', e);
                console.error('Texte reçu:', text);
                throw new Error('Le serveur a retourné une réponse invalide');
            }
            
            return data;
        })
        .then(data => {
            console.log('Traitement de la réponse:', data);
            
            if (data.success) {
                // Succès
                confirmationMessage.textContent = data.message;
                confirmationMessage.style.color = 'green';
                confirmationMessage.style.fontSize = '1.5rem';
                confirmationMessage.style.textAlign = 'center';
                confirmationMessage.style.display = 'block';
                
                // Réinitialisation du formulaire
                contactForm.reset();
                console.log('Formulaire réinitialisé');
                
                // Masquer après 5 secondes
                setTimeout(() => {
                    confirmationMessage.style.display = 'none';
                }, 10000);
            } else {
                // Erreur
                confirmationMessage.textContent = data.message;
                confirmationMessage.style.color = 'red';
                confirmationMessage.style.fontSize = '1.5rem';
                confirmationMessage.style.textAlign = 'center';
                confirmationMessage.style.display = 'block';
                console.error('Erreur du serveur:', data.message);
            }
        })
        .catch(error => {
            console.error('ERREUR lors de la requête:', error);
            confirmationMessage.textContent = 'Une erreur est survenue.';
            confirmationMessage.style.color = 'red';
            confirmationMessage.style.fontSize = '1.5rem';
            confirmationMessage.style.textAlign = 'center';
            confirmationMessage.style.display = 'block';
        })
        .finally(() => {
            // Réactivation du bouton
            submitButton.disabled = false;
            submitButton.textContent = originalText;
            console.log('Bouton réactivé');
        });
    });
    
    console.log('Écouteur d\'événements ajouté au formulaire');
});
