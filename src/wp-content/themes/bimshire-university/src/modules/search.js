import $ from 'jquery';

class Search {
    constructor() {
        this.isOverLayOpen = false;
        this.isSpinnerVisible = false;
        this.searchOverlay = $('.search-overlay');
        this.openButton = $('.js-search-trigger');
        this.closeButton = $('.search-overlay__close');
        this.searchField = $('#search-term');
        this.searchResults = $('.search-overlay__results');
        this.debounce;
        this.previousSearchText;
        this.events();
    }

    events() {
        this.openButton.on('click', this.openOverlay.bind(this));
        this.closeButton.on('click', this.closeOverlay.bind(this));
        $(document).on('keydown', this.keyPressDispatcher.bind(this));
        this.searchField.on('keyup', this.processSearchTerm.bind(this));
    }

    openOverlay() {
        this.searchOverlay.addClass('search-overlay--active');
        $('body').addClass('body-no-scroll');
        this.isOverLayOpen = true;
    }

    closeOverlay() {
        this.searchOverlay.removeClass('search-overlay--active');
        $('body').removeClass('body-no-scroll');
        this.isOverLayOpen = false;
    }

    keyPressDispatcher(e) {
        if (e.keyCode === 27 && this.isOverLayOpen) this.closeOverlay();
    }

    processSearchTerm(e) {
        if (this.searchField.val() !== this.previousSearchText) {
            clearTimeout(this.debounce);

            if (this.searchField.val().trim()) {
                if (!this.isSpinnerVisible) {
                    this.searchResults.html(
                        '<div class="spinner-loader"></div>'
                    );
                    this.isSpinnerVisible = true;
                }
                this.debounce = setTimeout(this.getResults.bind(this), 2000);
            } else {
                this.searchResults.html('');
                this.isSpinnerVisible = false;
            }
        }

        this.previousSearchText = this.searchField.val();
    }

    getResults() {
        this.searchResults.html('Imagine real search results');
        this.isSpinnerVisible = false;
    }
}

export default Search;
