document.querySelectorAll('.toConfirmDelete').forEach(e => e.onclick = displayModal);
/**
 * Affichage d'une fenêtre modale de suppression
 */
function displayModal() {
  let locationHref  = () => {location.href = this.dataset.href};
  let cancel        = () => {document.getElementById('deletionModal').close()}; 
  document.querySelector('#deletionModal .OK').onclick = locationHref;
  document.querySelector('#deletionModal .KO').onclick = cancel;
  document.getElementById('deletionModal').showModal();
  document.querySelector('#deletionModal .focus').focus();
}