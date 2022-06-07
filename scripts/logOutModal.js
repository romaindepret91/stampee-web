document.querySelectorAll('.toConfirmLogOut').forEach(e => e.onclick = displayModal);
/**
 * Affichage d'une fenêtre modale de déconnexion
 */
function displayModal() {
  let locationHref  = () => {location.href = this.dataset.href};
  let cancel        = () => {document.getElementById('logOutModal').close()}; 
  document.querySelector('#logOutModal .OK').onclick = locationHref;
  document.querySelector('#logOutModal .KO').onclick = cancel;
  document.getElementById('logOutModal').showModal();
  document.querySelector('#logOutModal .focus').focus();
}