
const   _elGridAuctions = new WeakMap(),
        _changeGridDisplaytoList = new WeakMap(),
        _changeGridDisplaytoGallery = new WeakMap();
/**
 * Classe qui gère le type d'affichage de la grille dans la page de catalogue (grille/liste)
 *
 */
export class GridDisplay {
    constructor(elGridAuctions) {
        if(!(elGridAuctions instanceof HTMLElement)) throw new Error('Must be an HTML element');
        if((!elGridAuctions.classList.contains('auctions-grid--list')) && (!elGridAuctions.classList.contains('auctions-grid--gallery'))) throw new Error('Must be an HTML element of class "auctions-grid--list" or "auctions-grid--gallery".');
        _elGridAuctions.set(this, elGridAuctions);
        _changeGridDisplaytoList.set(this, () => {
            const elGridItems = Array.from(this.elGridAuctions.querySelectorAll('.auctions-grid-item--gallery')).concat(Array.from(this.elGridAuctions.querySelectorAll('.auctions-grid-item--hidden')));
            this.elGridAuctions.classList.remove('auctions-grid--gallery');
            this.elGridAuctions.classList.add('auctions-grid--list');
            elGridItems.forEach((elGridItem) => {
                const gridElements = {
                    elTileImg: elGridItem.querySelector('.tile-img--gallery'),
                    elDividers: elGridItem.querySelectorAll('.divider--gallery'),
                    elTextContent: elGridItem.querySelector('.tile-text-content--gallery'),
                    elTextTop: elGridItem.querySelector('.text-content-top--gallery'),
                    elTextMiddle: elGridItem.querySelector('.text-content-middle--gallery'),
                    elPriceImg: elGridItem.querySelector('.tile-price-img--gallery'),
                    elTextBottom: elGridItem.querySelector('.text-content-bottom--gallery'),
                    elAuctionStatus : elGridItem.querySelector('.auctions-grid-item-status--gallery')
                };
                for(let gridEl in gridElements) {
                    if(gridEl !== 'elDividers') {
                        let classOfEl = gridElements[gridEl].className,
                            classToInject = classOfEl.replace('--gallery', '--list');

                        gridElements[gridEl].className = classToInject;
                    }
                    else {
                        gridElements[gridEl].forEach((elDivider) => {
                            elDivider.classList.remove('divider--gallery');
                            elDivider.classList.add('divider--list');  
                        });
                    }
                }
                elGridItem.classList.remove('auctions-grid-item--gallery');
                if(!elGridItem.classList.contains('auctions-grid-item--hidden'))elGridItem.classList.add('auctions-grid-item--list');
            });
        });
        _changeGridDisplaytoGallery.set(this, () => {
            const elGridItems = Array.from(this.elGridAuctions.querySelectorAll('.auctions-grid-item--list')).concat(Array.from(this.elGridAuctions.querySelectorAll('.auctions-grid-item--hidden')));
            this.elGridAuctions.classList.remove('auctions-grid--list');
            this.elGridAuctions.classList.add('auctions-grid--gallery');
            elGridItems.forEach((elGridItem) => {
                const gridElements = {
                    elTileImg: elGridItem.querySelector('.tile-img--list'),
                    elDividers: elGridItem.querySelectorAll('.divider--list'),
                    elTextContent: elGridItem.querySelector('.tile-text-content--list'),
                    elTextTop: elGridItem.querySelector('.text-content-top--list'),
                    elTextMiddle: elGridItem.querySelector('.text-content-middle--list'),
                    elPriceImg: elGridItem.querySelector('.tile-price-img--list'),
                    elTextBottom: elGridItem.querySelector('.text-content-bottom--list'),
                    elAuctionStatus : elGridItem.querySelector('.auctions-grid-item-status--list')
                };
                for(let gridEl in gridElements) {
                    if(gridEl !== 'elDividers') {
                        let classOfEl = gridElements[gridEl].className,
                            classToInject = classOfEl.replace('--list', '--gallery');
                        gridElements[gridEl].className = classToInject;
                    }
                    else {
                        gridElements[gridEl].forEach((elDivider) => {
                            elDivider.classList.remove('divider--list');
                            elDivider.classList.add('divider--gallery');  
                        });
                    }
                }
                elGridItem.classList.remove('auctions-grid-item--list');
                if(!elGridItem.classList.contains('auctions-grid-item--hidden')) elGridItem.classList.add('auctions-grid-item--gallery');
            });
        });
    }
    get elGridAuctions() {
        return _elGridAuctions.get(this);
    }
    get changeGridDisplaytoList() {
        return _changeGridDisplaytoList.get(this);
    }
    get changeGridDisplaytoGallery() {
        return _changeGridDisplaytoGallery.get(this);
    }
}