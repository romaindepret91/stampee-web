
const   _elMemberLogo = new WeakMap(),
        _displayMemberMenu = new WeakMap();
/**
 * Classe qui gère l'affichage du menu déroulant du membre
 *
 */
export class MemberMenu {
    constructor(elHeader) {
        _elMemberLogo.set(this, elHeader.querySelector('.member-logo--c'));
        _displayMemberMenu.set(this, () => {
            let elMenuDropdown = null;
            if(this.elMemberLogo) elMenuDropdown = this.elMemberLogo.nextElementSibling;
            if(elMenuDropdown) {
                this.elMemberLogo.addEventListener('click', () => {
                    if(elMenuDropdown.classList.contains('nav-top__dropdown-content')) {
                        elMenuDropdown.classList.remove('nav-top__dropdown-content');
                        elMenuDropdown.classList.add('nav-top__dropdown-content--hidden');
                    }
                    else if(elMenuDropdown.classList.contains('nav-top__dropdown-content--hidden')) {
                        elMenuDropdown.classList.remove('nav-top__dropdown-content--hidden');
                        elMenuDropdown.classList.add('nav-top__dropdown-content');
                    }
                });
            }
        });
        this.displayMemberMenu();
    }
    get elMemberLogo() {
        return _elMemberLogo.get(this);
    }
    get displayMemberMenu() {
        return _displayMemberMenu.get(this);
    }
}