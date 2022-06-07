document.querySelectorAll('.btn-buy-content').forEach(e => e.onclick = displayModal);
/**
 * Affichage d'une fenêtre modale de mise
 */
function displayModal() {
    let locationHref  = () => {
        const bidAmount = document.querySelector('.bidding-form input').value;
        location.href = this.dataset.href + `&bidAmount=${ bidAmount }`;
        };
    let cancel        = () => {document.getElementById('biddingModal').close()}; 
    document.querySelector('#biddingModal .OK').onclick = locationHref;
    document.querySelector('#biddingModal .KO').onclick = cancel;
    document.getElementById('biddingModal').showModal();
    document.querySelector('#biddingModal .focus').focus();
}