
const   _elCatalogue        = new WeakMap(),
        _elAuctionsTiles    = new WeakMap(),
        _elSearchAuctions   = new WeakMap(),
        _elSearchCriterias  = new WeakMap(),
        _startSearch        = new WeakMap();
/**
 * Classe qui gère la recherche d'enchères de la page catalogue
 *
 */
export class SearchAuctions {
    constructor(elCatalogue) {
        if(!(elCatalogue instanceof HTMLElement)) throw new Error('Must be an HTML element');
        if(!elCatalogue.classList.contains('catalogue-main')) throw new Error('Must be an HTML element of class "catalogue-main".');
        _elCatalogue.set(this, elCatalogue);
        _elAuctionsTiles.set(this, elCatalogue.querySelectorAll('article'));
        _elSearchAuctions.set(this, elCatalogue.querySelector('.search-auctions'));
        _elSearchCriterias.set(this, this.elSearchAuctions.querySelectorAll('.search-auctions__criteria'));
        _startSearch.set(this, () => {
            let criterias = {
                region:     [],
                condition:  [],
                period:     [],
                price:      {min: null, max: null},
                status:     []
                },
                inputs = Array.from(this.elSearchAuctions.querySelectorAll('input')), 
                select = Array.from(this.elSearchAuctions.querySelectorAll('select')),
                searchBar = Array.from(document.querySelectorAll('#searchbar-auction'));
            inputs = inputs.concat(select, searchBar);
            inputs.forEach(input => {
                if(input.id === "searchbar-auction") {
                    const   elSubmit = input.parentNode.lastElementChild.previousElementSibling,
                            elCancel = input.parentNode.lastElementChild;
                    elSubmit.onclick = () => {
                        let elMResultMsg = elCatalogue.querySelector('.noResult-msg--show');
                        if(elMResultMsg) {
                            elMResultMsg.classList.remove('noResult-msg--show');
                            elMResultMsg.classList.add('noResult-msg--hidden');
                        }
                        let noAuction = true;
                        this.elAuctionsTiles.forEach(elAuctionTile => {
                            const   auction = {
                                title:          elAuctionTile.querySelector('.tile-title').firstElementChild.firstChild.nodeValue,
                                country:        elAuctionTile.querySelector('.tile-title').firstElementChild.nextElementSibling.firstChild.nodeValue,
                                region:         elAuctionTile.querySelector('.tile-stamp-origin').firstElementChild.firstElementChild.nextSibling.nodeValue,
                                condition:      elAuctionTile.querySelector('.tile-stamp-status').firstElementChild.firstElementChild.nextSibling.nodeValue,
                                period:           elAuctionTile.querySelector('.tile-stamp-year').firstElementChild.firstElementChild.nextSibling.nodeValue
                            }
                            auction.status = elAuctionTile.querySelector('.auctions-grid-item-status--gallery') || elAuctionTile.querySelector('.auctions-grid-item-status--list');
                            auction.status = auction.status.firstElementChild.innerText;
                            let auctionToDisplay = false,
                                searchValue = input.value.toLowerCase();
                            if(searchValue !== '') {
                                for(let auctionProp in auction) {
                                    let propValue = auction[auctionProp].toLowerCase();
                                    if(propValue.includes(searchValue)) auctionToDisplay = true;
                                }
                            }
                            else auctionToDisplay = true;
                            if(auctionToDisplay) {
                                if(elAuctionTile.classList.contains('auctions-grid-item--hidden') && elAuctionTile.firstElementChild.classList.contains('tile-img--list')) {
                                    elAuctionTile.classList.remove('auctions-grid-item--hidden');
                                    elAuctionTile.classList.add('auctions-grid-item--list');
                                }
                                else if(!elAuctionTile.classList.contains('auctions-grid-item--gallery') && !elAuctionTile.classList.contains('auctions-grid-item--list')) {
                                    elAuctionTile.classList.remove('auctions-grid-item--hidden');
                                    elAuctionTile.classList.add('auctions-grid-item--gallery');
                                }
                                noAuction= false;
                            }
                            else {
                                if(elAuctionTile.classList.contains('auctions-grid-item--gallery')) elAuctionTile.classList.remove('auctions-grid-item--gallery');
                                if(elAuctionTile.classList.contains('auctions-grid-item--list')) elAuctionTile.classList.remove('auctions-grid-item--list');
                                if(!elAuctionTile.classList.contains('auctions-grid-item--hidden')) elAuctionTile.classList.add('auctions-grid-item--hidden');
                            }
                        });
                        if(noAuction) {
                            elMResultMsg = elCatalogue.querySelector('.noResult-msg--hidden');
                            elMResultMsg.classList.remove('noResult-msg--hidden');
                            elMResultMsg.classList.add('noResult-msg--show');
                        }
                        input.value = '';
                    };
                    elCancel.onclick = () => {
                        this.elAuctionsTiles.forEach(elAuctionTile => {
                            if(elAuctionTile.classList.contains('auctions-grid-item--hidden') && elAuctionTile.firstElementChild.classList.contains('tile-img--list')) {
                                elAuctionTile.classList.remove('auctions-grid-item--hidden');
                                elAuctionTile.classList.add('auctions-grid-item--list');
                            }
                            else if(!elAuctionTile.classList.contains('auctions-grid-item--gallery') && !elAuctionTile.classList.contains('auctions-grid-item--list')) {
                                elAuctionTile.classList.remove('auctions-grid-item--hidden');
                                elAuctionTile.classList.add('auctions-grid-item--gallery');
                            }
                        });
                        input.value = '';
                        inputs.forEach(input => {
                            switch(input.type) {
                                case 'checkbox':
                                    if(input.checked) input.checked = false;
                                    break;
                                case 'range':
                                    input.nextElementSibling.value = 0;
                                    break;
                                case 'select-one':
                                    const options = input.querySelectorAll('option');
                                    options.forEach(option => {
                                        if(option.selected) option.selected = false;
                                    });
                                    break;
                            }
                        });
                    };
                }
                else {
                    input.onchange = () => {
                        let elMResultMsg = elCatalogue.querySelector('.noResult-msg--show');
                        if(elMResultMsg) {
                            elMResultMsg.classList.remove('noResult-msg--show');
                            elMResultMsg.classList.add('noResult-msg--hidden');
                        }
                        this.elSearchCriterias.forEach(elSearchCriteria => {
                            let elInputs;
                            switch(elSearchCriteria.dataset.criteriaType) {
                                case 'region':
                                    elInputs = elSearchCriteria.querySelectorAll('input');
                                    elInputs.forEach(elInput => {
                                        if(elInput.checked && !criterias['region'].includes(elInput.nextElementSibling.innerText)) criterias['region'].push(elInput.nextElementSibling.innerText);
                                        else if(!elInput.checked && criterias['region'].includes(elInput.nextElementSibling.innerText)) {
                                            criterias['region'] = criterias['region'].filter((value)=>{
                                                return value !== elInput.nextElementSibling.innerText;
                                            })
                                        }
                                    });
                                    break;
                                case 'condition':
                                    elInputs = elSearchCriteria.querySelectorAll('input');
                                    elInputs.forEach(elInput => {
                                        if(elInput.checked && !criterias['condition'].includes(elInput.nextElementSibling.innerText)) criterias['condition'].push(elInput.nextElementSibling.innerText);
                                        else if(!elInput.checked && criterias['condition'].includes(elInput.nextElementSibling.innerText)) {
                                            criterias['condition'] = criterias['condition'].filter((value)=>{
                                                return value !== elInput.nextElementSibling.innerText;
                                            })
                                        }
                                    });
                                    break;
                                case 'period':
                                    elInputs = elSearchCriteria.querySelectorAll('option');
                                    elInputs.forEach(elInput => {
                                        if(elInput.selected && !criterias['period'].includes(elInput.value)) criterias['period'].push(elInput.value);
                                        else if(!elInput.selected && criterias['period'].includes(elInput.value)) {
                                            criterias['period'] = criterias['period'].filter((value)=>{
                                                return value !== elInput.value;
                                            })
                                        }
                                    });
                                    break;
                                case 'price':
                                    elInputs = elSearchCriteria.querySelectorAll('input');
                                    elInputs.forEach(elInput => {
                                        if(elInput.name === 'price-min') {
                                            criterias['price']['min'] = elInput.value;
                                        }
                                        if(elInput.name === 'price-max') {
                                            criterias['price']['max'] = elInput.value;
                                        }
                                    });
                                    break;
                                case 'status':
                                    elInputs = elSearchCriteria.querySelectorAll('input');
                                    elInputs.forEach(elInput => {
                                        if(elInput.checked && !criterias['status'].includes(elInput.nextElementSibling.innerText)) criterias['status'].push(elInput.nextElementSibling.innerText);
                                        else if(!elInput.checked && criterias['status'].includes(elInput.nextElementSibling.innerText)) {
                                            criterias['status'] = criterias['status'].filter((value)=>{
                                                return value !== elInput.nextElementSibling.innerText;
                                            })
                                        }
                                    });
                                    break;
                            }
                        });
                        let noAuction = true;
                        this.elAuctionsTiles.forEach(elAuctionTile => {
                            const   auction = {
                                region:         elAuctionTile.querySelector('.tile-stamp-origin').firstElementChild.firstElementChild.nextSibling.nodeValue,
                                condition:      elAuctionTile.querySelector('.tile-stamp-status').firstElementChild.firstElementChild.nextSibling.nodeValue,
                                period:           elAuctionTile.querySelector('.tile-stamp-year').firstElementChild.firstElementChild.nextSibling.nodeValue,
                                price:          elAuctionTile.querySelector('.tile-bid').firstElementChild.innerText.substring(1),
                            }
                            auction.status = elAuctionTile.querySelector('.auctions-grid-item-status--gallery') || elAuctionTile.querySelector('.auctions-grid-item-status--list');
                            auction.status = auction.status.firstElementChild.innerText;
                            let auctionToDisplay = true;
                            for(const auctionProp in auction) {
                                for(const criteria in criterias) {
                                    switch(criteria) {
                                        case 'region':
                                            if(criteria === auctionProp) {
                                                if(criterias[criteria].length !== 0 && !criterias[criteria].includes(auction[auctionProp])) {
                                                    auctionToDisplay = false;
                                                }
                                            }
                                            break;
                                        case 'condition':
                                            if(criteria === auctionProp) {
                                                if(criterias[criteria].length !== 0 && !criterias[criteria].includes(auction[auctionProp])) {
                                                    auctionToDisplay = false;
                                                }
                                            }
                                            break;
                                        case 'period':
                                            if(criteria === auctionProp) {
                                                if(criterias[criteria].length !== 0) {
                                                    criterias[criteria].forEach(period => {
                                                        period = period.split('-');
                                                        if(auction[auctionProp] < period[0] || auction[auctionProp] > period[1]) {
                                                            auctionToDisplay = false;
                                                        }
                                                    });
                                                }
                                            }
                                            break;
                                        case 'price':
                                            if(criteria === auctionProp) {
                                                if(parseInt(criterias[criteria]['min']) !== 0 && parseInt(auction[auctionProp]) < parseInt(criterias[criteria]['min'])) {
                                                    auctionToDisplay = false;
                                                } 
                                                if(parseInt(criterias[criteria]['max']) !== 0 &&  parseInt(auction[auctionProp]) > parseInt(criterias[criteria]['max']) ) {
                                                    auctionToDisplay = false;
                                                }
                                            }
                                            break;
                                        case 'status':
                                            if(criteria === auctionProp) {
                                                let criteriasToCheck = [];
                                                criterias[criteria].forEach(criteria => {
                                                    console.log(criteria);
                                                    if(criteria === 'Enchères à venir') criteriasToCheck.push('À venir');
                                                    if(criteria === 'Enchères en cours') criteriasToCheck.push('En cours');
                                                    if(criteria === 'Enchères clôturées') criteriasToCheck.push('Vente clôturée');
                                                });
                                                if(criterias[criteria].length !== 0 && !criteriasToCheck.includes(auction[auctionProp])) {
                                                    auctionToDisplay = false;
                                                }
                                            }
                                            break;
                                    }
                                }
                            }
                            if(!auctionToDisplay) {
                                if(elAuctionTile.classList.contains('auctions-grid-item--gallery')) elAuctionTile.classList.remove('auctions-grid-item--gallery');
                                if(elAuctionTile.classList.contains('auctions-grid-item--list')) elAuctionTile.classList.remove('auctions-grid-item--list');
                                if(!elAuctionTile.classList.contains('auctions-grid-item--hidden')) elAuctionTile.classList.add('auctions-grid-item--hidden');
                            }
                            else {
                                if(elAuctionTile.classList.contains('auctions-grid-item--hidden') && elAuctionTile.firstElementChild.classList.contains('tile-img--list')) {
                                    elAuctionTile.classList.remove('auctions-grid-item--hidden');
                                    elAuctionTile.classList.add('auctions-grid-item--list');
                                }
                                else if(!elAuctionTile.classList.contains('auctions-grid-item--gallery') && !elAuctionTile.classList.contains('auctions-grid-item--list')) {
                                    elAuctionTile.classList.remove('auctions-grid-item--hidden');
                                    elAuctionTile.classList.add('auctions-grid-item--gallery');
                                }
                                noAuction= false;
                            }
                        });
                        if(noAuction) {
                            elMResultMsg = elCatalogue.querySelector('.noResult-msg--hidden');
                            elMResultMsg.classList.remove('noResult-msg--hidden');
                            elMResultMsg.classList.add('noResult-msg--show');
                        }
                    };
                }
            });
        });
        this.startSearch();
    }
    get elCatalogue() {
        return _elCatalogue.get(this);
    }
    get elAuctionsTiles() {
        return _elAuctionsTiles.get(this);
    }
    get elSearchAuctions() {
        return _elSearchAuctions.get(this);
    }
    get elSearchCriterias() {
        return _elSearchCriterias.get(this);
    }
    get startSearch() {
        return _startSearch.get(this);
    }
}