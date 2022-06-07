'use scrict';
import { GridDisplay }      from "./classes/GridDisplay.js";
import { MenuDisplay }      from "./classes/MenuDisplay.js";
import { MemberMenu }       from "./classes/MemberMenu.js";
import { SearchAuctions }   from "./classes/SearchAuctions.js";
const   galleryStyleBtn = document.querySelector('.gallery-style-btn'),
        listStyleBtn = document.querySelector('.list-style-btn'),
        elGridAuctions = document.querySelector('.auctions-grid--gallery') || document.querySelector('.carrousel-other-auctions'),
        elInfoSave = document.querySelector('.info-tile-save'),
        elHeader = document.querySelector('.header'),
        elCatalogue = document.querySelector('.catalogue-main');

// Gestion de l'affichage des tuiles d'enchères en fonction du mode choisi (grille/liste)
let     elTilesAuctions;
if(elGridAuctions) {
    elGridAuctions.classList.contains('carrousel-other-auctions') ? elTilesAuctions = elGridAuctions.querySelectorAll('.auctions-grid-item--small') : elTilesAuctions = elGridAuctions.querySelectorAll('.auctions-grid-item--gallery');
}
if(galleryStyleBtn) {
    galleryStyleBtn.addEventListener('click', () => {
        new GridDisplay(elGridAuctions).changeGridDisplaytoGallery();
    });
}
if(listStyleBtn) {
    listStyleBtn.addEventListener('click', () => {
        new GridDisplay(elGridAuctions).changeGridDisplaytoList();
    });
}
// Gestion du changement de couleur de l'icône de favori au clic dans les tuiles
if(elTilesAuctions) {
    elTilesAuctions.forEach((elTileAuction) => {
        const elFavorite = elTileAuction.querySelector('.tile-favorite') || elTileAuction.querySelector('.tile-favorite--small');
        if(elFavorite) {
            elFavorite.addEventListener('click', () => {
                elFavorite.classList.toggle('isFavorite');
            });
        }
    });

}
// Gestion du changement de couleur de l'icône de favori au clic dans la page d'enchère
if(elInfoSave) {
    elInfoSave.addEventListener('click', () => {
        elInfoSave.classList.toggle('isFavorite');
    });
}
// Gestion de l'affichage du menu burger 
new MenuDisplay(elHeader);
// Gestion de l'affichage du menu utilisateur/membre
new MemberMenu(elHeader);
// Gestion de la recherche d'enchères
if(elCatalogue) {
    new SearchAuctions(elCatalogue);
}




