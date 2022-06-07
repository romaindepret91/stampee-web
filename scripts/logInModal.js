document.querySelectorAll('.btn-buy-content').forEach(e => e.onclick = displayModal);
/**
 * Affichage d'une fenêtre modale de connexion
 */
function displayModal() {
    let locationHref  = () => {location.href = this.dataset.href};
    let cancel        = () => {document.getElementById('logInModal').close()}; 
    document.querySelector('#logInModal .OK').onclick = locationHref;
    document.querySelector('#logInModal .KO').onclick = cancel;
    document.getElementById('logInModal').showModal();
    document.querySelector('#logInModal .focus').focus();
}