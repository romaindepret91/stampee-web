
const   _elHeader = new WeakMap(),
        _elBurgerIcon = new WeakMap,
        _elCrossIcon = new WeakMap(),
        _elMenuMobile = new WeakMap(),
        elBody = document.querySelector('body'),
        _displayMobileMenu = new WeakMap ();
/**
 * Classe qui gère l'affichage du menu burger en mode mobile/tablette
 *
 */
export class MenuDisplay {
    constructor(elHeader) {
        _elHeader.set(this, elHeader);
        _elBurgerIcon.set(this, this.elHeader.querySelector('.burger-icon'));
        _elCrossIcon.set(this, this.elHeader.querySelector('.cross-icon'));
        _elMenuMobile.set(this, this.elHeader.querySelector('.menu-mobile'));
        _displayMobileMenu.set(this, () => {
            this.elBurgerIcon.addEventListener('click', () => {

                this.elCrossIcon.style.setProperty('display', 'block');
                this.elCrossIcon.style.setProperty('z-index', '30');
                this.elBurgerIcon.style.setProperty('display', 'none');
                this.elMenuMobile.classList.add('active');
                this.elMenuMobile.classList.remove('disabled');
                elBody.style.setProperty('position', 'fixed');
            });
            this.elCrossIcon.addEventListener('click', () => {

                this.elBurgerIcon.style.setProperty('display', 'block');
                this.elCrossIcon.style.setProperty('display', 'none');
                this.elMenuMobile.classList.remove('active');
                this.elMenuMobile.classList.add('disabled');
                elBody.style.removeProperty('position');
            });
            window.addEventListener('resize', () =>{
                if(window.innerWidth >= 768) {
                    this.elMenuMobile.classList.remove('active');
                    this.elMenuMobile.classList.add('disabled');
                }
                else{
                    if(this.elCrossIcon.style.getPropertyValue('display') === 'block' && !this.elMenuMobile.classList.contains('active')) {
                        this.elMenuMobile.classList.add('active'); 
                        this.elMenuMobile.classList.remove('disabled');
                    }// Permet de réafficher le menu mobile lors d'une transition vers le mode mobile si il était préalablement ouvert
                }
            });
        });
        this.displayMobileMenu();
    }
    get elHeader() {
        return _elHeader.get(this);
    }
    get elBurgerIcon() {
        return _elBurgerIcon.get(this);
    }
    get elCrossIcon() {
        return _elCrossIcon.get(this);
    }
    get elMenuMobile() {
        return _elMenuMobile.get(this);
    }
    get displayMobileMenu() {
        return _displayMobileMenu.get(this);
    }
}